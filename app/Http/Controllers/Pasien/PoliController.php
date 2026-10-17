<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoliController extends Controller
{
    public function get()
    {
        $user = Auth::user();
        $polis = Poli::all();
        $jadwal = JadwalPeriksa::with('dokter', 'dokter.poli')->get();

        return view('pasien.daftar', [
            'user' => $user,
            'polis' => $polis,
            'jadwals' => $jadwal,
        ]);
    }

    
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'id_poli' => 'required|exists:poli,id',
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan' => 'nullable|string',
        ]);

        $jadwal = JadwalPeriksa::with('dokter')->findOrFail(
            $validated['id_jadwal']
        );

        // Ensure the selected schedule belongs to the selected poli
        if (
            !$jadwal->dokter ||
            (int) $jadwal->dokter->id_poli !== (int) $validated['id_poli']
        ) {
            throw ValidationException::withMessages([
                'id_jadwal' => 'Jadwal dokter tidak sesuai dengan poli yang dipilih.',
            ]);
        }

        DB::transaction(function () use ($validated, $jadwal) {

            // Lock the schedule row to serialize queue number allocation
            JadwalPeriksa::whereKey($jadwal->id)
                ->lockForUpdate()
                ->firstOrFail();

            $nomorTerakhir = DaftarPoli::where('id_jadwal', $jadwal->id)
                ->max('no_antrian') ?? 0;

            DaftarPoli::create([
                'id_pasien' => Auth::id(),
                'id_jadwal' => $jadwal->id,
                'keluhan' => $validated['keluhan'] ?? null,
                'no_antrian' => $nomorTerakhir + 1,
            ]);
        });

        return redirect()
            ->route('pasien.daftar')
            ->with('message', 'Berhasil mendaftar ke poli.')
            ->with('type', 'success');
    }

}