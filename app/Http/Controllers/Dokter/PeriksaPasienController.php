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
use App\Events\QueueUpdated;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        // Retrieve only today's patient queue for the authenticated doctor
        $daftarPasien = DaftarPoli::with([
            'pasien',
            'jadwalPeriksa',
            'periksas',
        ])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->whereDate('tanggal_kunjungan', today())
            ->orderBy('id_jadwal')
            ->orderBy('urutan_panggil')
            ->orderBy('id')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }


    public function create($id)
    {
        // Find the patient registration
        $daftarPoli = DaftarPoli::with('jadwalPeriksa')
            ->findOrFail($id);

        // Verify that the registration belongs to the logged-in doctor
        abort_unless(
            (int) $daftarPoli->jadwalPeriksa->id_dokter === (int) Auth::id(),
            403
        );

        // Only allow examinations for patients currently being served
        if ($daftarPoli->status_antrian !== 'dilayani') {
            throw ValidationException::withMessages([
                'id_daftar_poli' => 'Pasien harus berstatus sedang dilayani sebelum pemeriksaan.',
            ]);
        }

        // Prevent duplicate examinations
        if ($daftarPoli->periksas()->exists()) {
            throw ValidationException::withMessages([
                'id_daftar_poli' => 'Pasien sudah diperiksa.',
            ]);
        }

        // Retrieve available medicines
        $obats = Obat::all();

        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    public function mulaiLayani($id)
    {
        $antrian = DB::transaction(function () use ($id) {

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

            // Validate queue status
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

            // Ensure the patient has a valid call order
            if ($pendaftaran->urutan_panggil === null) {
                throw ValidationException::withMessages([
                    'antrian' => 'Urutan pemanggilan pasien belum tersedia.',
                ]);
            }

            // Prevent serving two patients on the same schedule and date
            $sedangDilayani = DaftarPoli::where('id_jadwal', $pendaftaran->id_jadwal)
                ->whereDate(
                    'tanggal_kunjungan',
                    $pendaftaran->tanggal_kunjungan->toDateString()
                )
                ->where('status_antrian', 'dilayani')
                ->exists();

            if ($sedangDilayani) {
                throw ValidationException::withMessages([
                    'antrian' => 'Masih ada pasien yang sedang dilayani pada jadwal ini.',
                ]);
            }

            // Prevent serving patients out of the current call order
            $antrianSebelumnya = DaftarPoli::where(
                'id_jadwal',
                $pendaftaran->id_jadwal
            )
                ->whereDate(
                    'tanggal_kunjungan',
                    $pendaftaran->tanggal_kunjungan->toDateString()
                )
                ->where('status_antrian', 'menunggu')
                ->where(function ($query) use ($pendaftaran) {
                    $query->where(
                        'urutan_panggil',
                        '<',
                        $pendaftaran->urutan_panggil
                    )
                    ->orWhere(function ($query) use ($pendaftaran) {
                        $query->where(
                            'urutan_panggil',
                            $pendaftaran->urutan_panggil
                        )
                        ->where('id', '<', $pendaftaran->id);
                    });
                })
                ->exists();

            if ($antrianSebelumnya) {
                throw ValidationException::withMessages([
                    'antrian' => 'Masih ada pasien dengan urutan pemanggilan lebih awal yang harus dilayani.',
                ]);
            }

            // Update queue status
            $pendaftaran->update([
                'status_antrian' => 'dilayani',
            ]);

            // Return registration data after updating
            return $pendaftaran;

        }, 3);

        // Broadcast after the transaction is committed
        QueueUpdated::dispatch(
            (int) $antrian->id_jadwal,
            $antrian->tanggal_kunjungan->toDateString()
        );

        return redirect()
            ->route('periksa-pasien.index')
            ->with('message', 'Pasien mulai dilayani.')
            ->with('type', 'success');
    }

    public function lewati($id)
    {
        $antrian = DB::transaction(function () use ($id) {

            // Find registration and verify doctor ownership
            $pendaftaran = DaftarPoli::with('jadwalPeriksa')
                ->findOrFail($id);

            abort_unless(
                (int) $pendaftaran->jadwalPeriksa->id_dokter === (int) Auth::id(),
                403
            );

            // Lock the schedule to synchronize queue operations
            JadwalPeriksa::whereKey($pendaftaran->id_jadwal)
                ->lockForUpdate()
                ->firstOrFail();

            // Lock and refresh registration data
            $pendaftaran = DaftarPoli::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            // Only allow skipping today's queue
            if (
                $pendaftaran->tanggal_kunjungan === null ||
                !$pendaftaran->tanggal_kunjungan->isToday()
            ) {
                throw ValidationException::withMessages([
                    'antrian' => 'Hanya antrean hari ini yang dapat dilewati.',
                ]);
            }

            // Only waiting patients can be skipped
            if ($pendaftaran->status_antrian !== 'menunggu') {
                throw ValidationException::withMessages([
                    'antrian' => 'Hanya pasien yang sedang menunggu yang dapat dilewati.',
                ]);
            }

            // Ensure the patient has a valid call order
            if ($pendaftaran->urutan_panggil === null) {
                throw ValidationException::withMessages([
                    'antrian' => 'Urutan pemanggilan pasien belum tersedia.',
                ]);
            }

            // Prevent skipping a patient while another is being served
            $sedangDilayani = DaftarPoli::where('id_jadwal', $pendaftaran->id_jadwal)
                ->whereDate(
                    'tanggal_kunjungan',
                    $pendaftaran->tanggal_kunjungan->toDateString()
                )
                ->where('status_antrian', 'dilayani')
                ->exists();

            if ($sedangDilayani) {
                throw ValidationException::withMessages([
                    'antrian' => 'Masih ada pasien yang sedang dilayani pada jadwal ini.',
                ]);
            }

            // Check for waiting patients with an earlier call order
            $antrianSebelumnya = DaftarPoli::where('id_jadwal', $pendaftaran->id_jadwal)
                ->whereDate(
                    'tanggal_kunjungan',
                    $pendaftaran->tanggal_kunjungan->toDateString()
                )
                ->where('status_antrian', 'menunggu')
                ->where(function ($query) use ($pendaftaran) {
                    $query->where('urutan_panggil', '<', $pendaftaran->urutan_panggil)
                        ->orWhere(function ($query) use ($pendaftaran) {
                            $query->where('urutan_panggil', $pendaftaran->urutan_panggil)
                                ->where('id', '<', $pendaftaran->id);
                        });
                })
                ->exists();

            if ($antrianSebelumnya) {
                throw ValidationException::withMessages([
                    'antrian' => 'Pasien ini belum mendapat giliran untuk dilewati.',
                ]);
            }

            // Update queue status without changing the original queue number
            $pendaftaran->update([
                'status_antrian' => 'dilewati',
            ]);

            // Return updated registration data
            return $pendaftaran;

            }, 3);

        // Broadcast queue changes after the transaction is committed
        QueueUpdated::dispatch(
            (int) $antrian->id_jadwal,
            $antrian->tanggal_kunjungan->toDateString()
        );

        return redirect()
            ->route('periksa-pasien.index')
            ->with('message', 'Antrean pasien berhasil dilewati.')
            ->with('type', 'success');
    }

    public function aktifkanKembali($id)
    {
        $antrian = DB::transaction(function () use ($id) {

            // Find registration and verify doctor ownership
            $pendaftaran = DaftarPoli::with('jadwalPeriksa')
                ->findOrFail($id);

            abort_unless(
                (int) $pendaftaran->jadwalPeriksa->id_dokter === (int) Auth::id(),
                403
            );

            // Lock the schedule to synchronize queue operations
            JadwalPeriksa::whereKey($pendaftaran->id_jadwal)
                ->lockForUpdate()
                ->firstOrFail();

            // Lock and refresh registration data
            $pendaftaran = DaftarPoli::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            // Only allow reactivation on the scheduled visit date
            if (
                $pendaftaran->tanggal_kunjungan === null ||
                !$pendaftaran->tanggal_kunjungan->isToday()
            ) {
                throw ValidationException::withMessages([
                    'antrian' => 'Hanya antrean hari ini yang dapat diaktifkan kembali.',
                ]);
            }

            // Only skipped patients can be reactivated
            if ($pendaftaran->status_antrian !== 'dilewati') {
                throw ValidationException::withMessages([
                    'antrian' => 'Hanya antrean yang dilewati yang dapat diaktifkan kembali.',
                ]);
            }

            // Get the highest call order for this schedule and date
            $urutanTerakhir = DaftarPoli::where(
                'id_jadwal',
                $pendaftaran->id_jadwal
            )
                ->whereDate(
                    'tanggal_kunjungan',
                    $pendaftaran->tanggal_kunjungan->toDateString()
                )
                ->max('urutan_panggil') ?? 0;

            // Move the patient to the end of the call order
            $pendaftaran->update([
                'status_antrian' => 'menunggu',
                'urutan_panggil' => $urutanTerakhir + 1,
            ]);

            return $pendaftaran;

        }, 3);

        // Notify queue listeners after the transaction
        QueueUpdated::dispatch(
            (int) $antrian->id_jadwal,
            $antrian->tanggal_kunjungan->toDateString()
        );

        return redirect()
            ->route('periksa-pasien.index')
            ->with('message', 'Pasien berhasil diaktifkan kembali ke antrean.')
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

        $antrian = DB::transaction(function () use ($validated, $obatIds) {

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

            // Only allow examinations for patients currently being served
            if ($daftarPoli->status_antrian !== 'dilayani') {
                throw ValidationException::withMessages([
                    'id_daftar_poli' => 'Pasien harus berstatus sedang dilayani sebelum pemeriksaan disimpan.',
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

            return $daftarPoli;

        });

        // Send the event after the database transaction succeeds
        if ($antrian->tanggal_kunjungan !== null) {
            QueueUpdated::dispatch(
                (int) $antrian->id_jadwal,
                $antrian->tanggal_kunjungan->toDateString()
            );
        }

        return redirect()
            ->route('periksa-pasien.index')
            ->with('message', 'Data pemeriksaan berhasil disimpan.')
            ->with('type', 'success');
    }

}