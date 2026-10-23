<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DokterExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        // Retrieve all doctors with their assigned polyclinic
        return User::with('poli')
            ->where('role', 'dokter')
            ->orderBy('nama')
            ->get()
            ->map(function ($dokter) {
                return [
                    'nama' => $dokter->nama,
                    'email' => $dokter->email,
                    'no_ktp' => $dokter->no_ktp,
                    'no_hp' => $dokter->no_hp,
                    'alamat' => $dokter->alamat,
                    'poli' => $dokter->poli?->nama_poli ?? '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Dokter',
            'Email',
            'Nomor KTP',
            'Nomor HP',
            'Alamat',
            'Poli',
        ];
    }
}