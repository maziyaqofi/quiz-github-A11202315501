<?php

namespace App\Exports;

use App\Models\Periksa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RiwayatPasienExport implements FromCollection, WithHeadings
{
    public function __construct(
        private readonly int $dokterId
    ) {}

    public function collection(): Collection
    {
        // Retrieve examination histories belonging to the authenticated doctor
        return Periksa::whereHas('daftarPoli.jadwalPeriksa', function ($query) {
            $query->where('id_dokter', $this->dokterId);
        })
            ->with([
                'daftarPoli.pasien',
                'detailPeriksas.obat',
            ])
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($periksa) {
                return [
                    'nama_pasien' => $periksa->daftarPoli?->pasien?->nama,
                    'no_rm' => $periksa->daftarPoli?->pasien?->no_rm,
                    'tanggal_periksa' => $periksa->tgl_periksa,
                    'keluhan' => $periksa->daftarPoli?->keluhan,
                    'catatan' => $periksa->catatan,
                    'obat' => $periksa->detailPeriksas
                        ->pluck('obat.nama_obat')
                        ->filter()
                        ->implode(', '),
                    'biaya_periksa' => $periksa->biaya_periksa,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Pasien',
            'No. Rekam Medis',
            'Tanggal Periksa',
            'Keluhan',
            'Catatan Dokter',
            'Obat',
            'Biaya Periksa',
        ];
    }
}