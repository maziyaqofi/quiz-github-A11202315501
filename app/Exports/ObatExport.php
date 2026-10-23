<?php

namespace App\Exports;

use App\Models\Obat;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ObatExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        // Retrieve all medicines, including prices and stock
        return Obat::orderBy('nama_obat')
            ->get()
            ->map(function ($obat) {
                return [
                    'nama_obat' => $obat->nama_obat,
                    'kemasan' => $obat->kemasan ?? '-',
                    'harga' => $obat->harga,
                    'stok' => $obat->stok,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Obat',
            'Kemasan',
            'Harga (Rp)',
            'Stok',
        ];
    }
}