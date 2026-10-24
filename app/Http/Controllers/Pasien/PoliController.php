<?php

namespace App\Http\Controllers\Pasien;

use Carbon\Carbon;
use App\Models\User;
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
            'keluhan' => 'required|string',
        ]);

        $jadwal = JadwalPeriksa::with('dokter')->findOrFail(
            $validated['id_jadwal']
        );

        // Verify that the schedule belongs to the selected poli
        if (
            !$jadwal->dokter ||
            (int) $jadwal->dokter->id_poli !== (int) $validated['id_poli']
        ) {
            throw ValidationException::withMessages([
                'id_jadwal' => 'Jadwal dokter tidak sesuai dengan poli yang dipilih.',
            ]);
        }

        // Map Indonesian day names to ISO weekday numbers
        $hariMap = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 7,
        ];

        $now = Carbon::now();
        $hariJadwal = $hariMap[$jadwal->hari];

        $selisihHari = ($hariJadwal - $now->dayOfWeekIso + 7) % 7;

        $tanggalKunjungan = $now->copy()
            ->startOfDay()
            ->addDays($selisihHari);

        // If today's schedule has ended, use next week's schedule
        if ($selisihHari === 0) {
            $jamSelesai = Carbon::parse(
                $tanggalKunjungan->toDateString() . ' ' . $jadwal->jam_selesai
            );

            if ($now->greaterThanOrEqualTo($jamSelesai)) {
                $tanggalKunjungan->addWeek();
            }
        }

        DB::transaction(function () use ($validated, $jadwal, $tanggalKunjungan) {

            // Lock the patient row to prevent simultaneous registrations
            User::whereKey(Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            // Check for active registrations based on their scheduled end time
            $pendaftaranAktif = DaftarPoli::with('jadwalPeriksa')
                ->where('id_pasien', Auth::id())
                ->whereIn('status_antrian', ['menunggu', 'dilayani', 'dilewati'])
                ->get()
                ->contains(function ($registration) {
                    // An ongoing examination remains active
                    if ($registration->status_antrian === 'dilayani') {
                        return true;
                    }

                    // Treat incomplete schedule information as active
                    if (
                        $registration->tanggal_kunjungan === null ||
                        $registration->jadwalPeriksa === null
                    ) {
                        return true;
                    }

                    // Determine when the scheduled examination ends
                    $scheduledEnd = Carbon::parse(
                        $registration->tanggal_kunjungan->toDateString()
                        . ' '
                        . $registration->jadwalPeriksa->jam_selesai
                    );

                    return now()->lessThan($scheduledEnd);
                });

                // Prevent multiple active registrations
                if ($pendaftaranAktif) {
                    throw ValidationException::withMessages([
                        'id_jadwal' => 'Anda masih memiliki pendaftaran aktif. Selesaikan pemeriksaan sebelumnya sebelum mendaftar kembali.',
                    ]);
                }

            // Lock schedule row before allocating the queue number
            JadwalPeriksa::whereKey($jadwal->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Get the last queue number for this schedule and visit date
            $nomorTerakhir = DaftarPoli::where('id_jadwal', $jadwal->id)
                ->whereDate('tanggal_kunjungan', $tanggalKunjungan->toDateString())
                ->max('no_antrian') ?? 0;

            // Get the last call order, including reactivated patients
            $urutanTerakhir = DaftarPoli::where('id_jadwal', $jadwal->id)
                ->whereDate('tanggal_kunjungan', $tanggalKunjungan->toDateString())
                ->max('urutan_panggil') ?? 0;

            // Assign a new queue number and call order
            DaftarPoli::create([
                'id_pasien' => Auth::id(),
                'id_jadwal' => $jadwal->id,
                'keluhan' => $validated['keluhan'],
                'tanggal_kunjungan' => $tanggalKunjungan->toDateString(),
                'status_antrian' => 'menunggu',
                'no_antrian' => $nomorTerakhir + 1,
                'urutan_panggil' => $urutanTerakhir + 1,
            ]);

        }, 3);

        return redirect()
            ->route('pasien.riwayat-pendaftaran.index')
            ->with('message', 'Berhasil mendaftar ke poli.')
            ->with('type', 'success');
    }

}