<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Get the patient's active registration
        $antrianAktif = DaftarPoli::with([
            'jadwalPeriksa.dokter.poli',
        ])
            ->where('id_pasien', Auth::id())
            ->whereIn('status_antrian', ['menunggu', 'dilayani'])
            ->latest('id')
            ->first();

        // Get all schedules and their currently served queue
        $jadwals = JadwalPeriksa::with([
            'dokter.poli',
            'daftarPolis' => function ($query) {
                $query->whereDate('tanggal_kunjungan', today())
                    ->where('status_antrian', 'dilayani');
            },
        ])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Get the currently served number for the patient's schedule
        $nomorSedangDilayani = null;

        if ($antrianAktif) {
            $nomorSedangDilayani = DaftarPoli::where(
                'id_jadwal',
                $antrianAktif->id_jadwal
            )
                ->whereDate(
                    'tanggal_kunjungan',
                    $antrianAktif->tanggal_kunjungan
                )
                ->where('status_antrian', 'dilayani')
                ->value('no_antrian');
        }

        return view('pasien.dashboard', compact(
            'antrianAktif',
            'jadwals',
            'nomorSedangDilayani'
        ));
    }
}
