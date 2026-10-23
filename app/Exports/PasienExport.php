<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PasienExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        // Retrieve all registered patients
        return User::where('role', 'pasien')
            ->orderBy('nama')
            ->get()
            ->map(function ($pasien) {
                return [
                    'nama' => $pasien->nama,
                    'no_rm' => $pasien->no_rm ?? '-',
                    'no_ktp' => $pasien->no_ktp ?? '-',
                    'no_hp' => $pasien->no_hp ?? '-',
                    'alamat' => $pasien->alamat ?? '-',
                    'email' => $pasien->email,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama Pasien',
            'Nomor Rekam Medis',
            'Nomor KTP',
            'Nomor HP',
            'Alamat',
            'Email',
        ];
    }
}