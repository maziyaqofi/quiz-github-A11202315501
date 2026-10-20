<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\JadwalPeriksa;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        $obats = Obat::all();
        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    public function mulaiLayani($id)
    {
        DB::transaction(function () use ($id) {

            // Find the registration and verify doctor ownership
            $pendaftaran = DaftarPoli::with('jadwalPeriksa')
                ->findOrFail($id);

            abort_unless(
                (int) $pendaftaran->jadwalPeriksa->id_dokter === (int) Auth::id(),
                403
            );

            // Lock the schedule to prevent concurrent queue starts
            JadwalPeriksa::whereKey($pendaftaran->id_jadwal)
                ->lockForUpdate()
                ->firstOrFail();

            // Refresh registration data inside the transaction
            $pendaftaran = DaftarPoli::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pendaftaran->status_antrian !== 'menunggu') {
                throw ValidationException::withMessages([
                    'antrian' => 'Antrian ini tidak dalam status menunggu.',
                ]);
            }

            // Only allow service on the scheduled visit date
            if (
                $pendaftaran->tanggal_kunjungan === null ||
                !$pendaftaran->tanggal_kunjungan->isToday()
            ) {
                throw ValidationException::withMessages([
                    'antrian' => 'Pasien hanya dapat dilayani pada tanggal kunjungannya.',
                ]);
            }

            // Prevent serving two patients on the same schedule and date
            $sedangDilayani = DaftarPoli::where('id_jadwal', $pendaftaran->id_jadwal)
                ->where('tanggal_kunjungan', $pendaftaran->tanggal_kunjungan)
                ->where('status_antrian', 'dilayani')
                ->exists();

            if ($sedangDilayani) {
                throw ValidationException::withMessages([
                    'antrian' => 'Masih ada pasien yang sedang dilayani pada jadwal ini.',
                ]);
            }

            // Update queue status
            $pendaftaran->update([
                'status_antrian' => 'dilayani',
            ]);

        }, 3);

        return redirect()
            ->route('periksa-pasien.index')
            ->with('message', 'Pasien mulai dilayani.')
            ->with('type', 'success');
    }
    
    public function store(Request $request)
    {
        // Validate examination data
        $validated = $request->validate([
            'id_daftar_poli' => 'required|integer|exists:daftar_poli,id',
            'catatan' => 'nullable|string',
            'obat_json' => 'required|json',
        ]);

        // Decode selected medicine IDs
        $obatIds = json_decode($validated['obat_json'], true);

        // Validate medicine ID format
        if (
            !is_array($obatIds) ||
            !array_is_list($obatIds) ||
            count($obatIds) !== count(array_unique($obatIds)) ||
            collect($obatIds)->contains(
                fn ($id) => !is_int($id) || $id < 1
            )
        ) {
            throw ValidationException::withMessages([
                'obat_json' => 'Pilihan obat tidak valid.',
            ]);
        }

        DB::transaction(function () use ($validated, $obatIds) {

            // Lock the registration during examination
            $daftarPoli = DaftarPoli::whereKey(
                $validated['id_daftar_poli']
            )
                ->lockForUpdate()
                ->firstOrFail();

            // Verify the registration belongs to the logged-in doctor
            $isAssigned = $daftarPoli->jadwalPeriksa()
                ->where('id_dokter', Auth::id())
                ->exists();

            abort_unless($isAssigned, 403);

            // Prevent duplicate examinations
            if ($daftarPoli->periksas()->exists()) {
                throw ValidationException::withMessages([
                    'id_daftar_poli' => 'Pasien sudah diperiksa.',
                ]);
            }

            // Retrieve selected medicines from the database
            $obats = Obat::whereIn('id', $obatIds)->get();

            if ($obats->count() !== count($obatIds)) {
                throw ValidationException::withMessages([
                    'obat_json' => 'Terdapat obat yang tidak valid.',
                ]);
            }

            // Calculate examination cost
            $biayaDasar = 150000;
            $totalObat = $obats->sum('harga');
            $totalBiaya = $biayaDasar + $totalObat;

            // Save examination
            $periksa = Periksa::create([
                'id_daftar_poli' => $daftarPoli->id,
                'tgl_periksa' => now(),
                'catatan' => $validated['catatan'] ?? null,
                'biaya_periksa' => $totalBiaya,
            ]);

            // Save examination medicines
            foreach ($obats as $obat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $obat->id,
                ]);
            }

            // Mark the patient's queue as completed
            $daftarPoli->update([
                'status_antrian' => 'selesai',
            ]);

        });

        return redirect()
            ->route('periksa-pasien.index')
            ->with('message', 'Data pemeriksaan berhasil disimpan.')
            ->with('type', 'success');
    }

}