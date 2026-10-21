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

    public function queueStatus()
    {
        // Get the patient's active registration
        $antrianAktif = DaftarPoli::where('id_pasien', Auth::id())
            ->whereIn('status_antrian', ['menunggu', 'dilayani'])
            ->latest('id')
            ->first();

        // Get currently served queue numbers for today's schedules
        $antrianDilayani = DaftarPoli::query()
            ->whereDate('tanggal_kunjungan', today())
            ->where('status_antrian', 'dilayani')
            ->get(['id_jadwal', 'no_antrian'])
            ->mapWithKeys(function ($antrian) {
                return [
                    $antrian->id_jadwal => $antrian->no_antrian,
                ];
            });

        // Get the currently served number for the patient's visit date
        $nomorSedangDilayani = null;

        if ($antrianAktif && $antrianAktif->tanggal_kunjungan !== null) {
            $nomorSedangDilayani = DaftarPoli::where(
                'id_jadwal',
                $antrianAktif->id_jadwal
            )
                ->whereDate(
                    'tanggal_kunjungan',
                    $antrianAktif->tanggal_kunjungan->toDateString()
                )
                ->where('status_antrian', 'dilayani')
                ->value('no_antrian');
        }

        return response()->json([
            'antrian_aktif' => $antrianAktif ? [
                'id' => $antrianAktif->id,
                'status' => $antrianAktif->status_antrian,
                'no_antrian' => $antrianAktif->no_antrian,
                'id_jadwal' => $antrianAktif->id_jadwal,
            ] : null,
            'nomor_sedang_dilayani' => $nomorSedangDilayani,
            'antrian_dilayani' => $antrianDilayani,
        ]);
    }

}
