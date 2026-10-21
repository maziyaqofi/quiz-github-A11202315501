<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use Illuminate\Support\Facades\Auth;

class RiwayatPendaftaranController extends Controller
{
    public function index()
    {
        // Retrieve registrations belonging to the logged-in patient
        $riwayatPendaftaran = DaftarPoli::with([
            'jadwalPeriksa.dokter.poli',
            'periksas.pembayaran'
        ])
            ->where('id_pasien', Auth::id())
            ->latest('id')
            ->get();

        return view(
            'pasien.riwayat-pendaftaran.index',
            compact('riwayatPendaftaran')
        );
    }

    public function show($id)
    {
        // Retrieve only the logged-in patient's registration
        $pendaftaran = DaftarPoli::with([
            'jadwalPeriksa.dokter.poli',
            'periksas.detailPeriksas.obat',
            'periksas.pembayaran'
        ])
            ->where('id_pasien', Auth::id())
            ->findOrFail($id);

        return view(
            'pasien.riwayat-pendaftaran.show',
            compact('pendaftaran')
        );
    }
}