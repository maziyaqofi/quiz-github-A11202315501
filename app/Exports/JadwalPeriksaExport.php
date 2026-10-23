<?php

namespace App\Exports;

use App\Models\JadwalPeriksa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JadwalPeriksaExport implements FromCollection, WithHeadings
{
    public function __construct(
        private readonly int $dokterId
    ) {}

    public function collection(): Collection
    {
        // Retrieve schedules belonging to the authenticated doctor
        return JadwalPeriksa::where('id_dokter', $this->dokterId)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->map(function ($jadwal) {
                return [
                    'hari' => $jadwal->hari,
                    'jam_mulai' => $jadwal->jam_mulai,
                    'jam_selesai' => $jadwal->jam_selesai,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Hari',
            'Jam Mulai',
            'Jam Selesai',
        ];
    }
}