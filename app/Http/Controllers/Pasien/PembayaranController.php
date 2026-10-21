<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Notifications\BuktiPembayaranUploaded;

class PembayaranController extends Controller
{
    public function upload(Request $request, $id)
    {
        // Validate payment proof
        $validated = $request->validate([
            'bukti_pembayaran' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],
        ]);

        // Ensure the examination belongs to the logged-in patient
        $periksa = Periksa::whereHas('daftarPoli', function ($query) {
            $query->where('id_pasien', Auth::id())
                  ->where('status_antrian', 'selesai');
        })->findOrFail($id);

        // Store the uploaded file
        $path = $validated['bukti_pembayaran']->store(
            'bukti-pembayaran',
            'public'
        );

        try {
            DB::transaction(function () use ($periksa, $path) {
                // Lock examination to prevent concurrent uploads
                Periksa::whereKey($periksa->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $pembayaran = Pembayaran::where(
                    'id_periksa',
                    $periksa->id
                )->lockForUpdate()->first();

                if ($pembayaran &&
                    $pembayaran->tanggal_upload &&
                    $pembayaran->status !== 'ditolak') {
                    throw ValidationException::withMessages([
                        'bukti_pembayaran' =>
                            'Pembayaran sedang diverifikasi atau sudah lunas.',
                    ]);
                }

                if (!$pembayaran) {
                    $pembayaran = new Pembayaran();
                    $pembayaran->id_periksa = $periksa->id;
                }

                $oldPath = $pembayaran->bukti_pembayaran;

                $pembayaran->bukti_pembayaran = $path;
                $pembayaran->status = 'menunggu';
                $pembayaran->tanggal_upload = now();
                $pembayaran->tanggal_verifikasi = null;
                $pembayaran->save();

                // Notify the assigned doctor after the payment is committed
                $doctorId = $periksa->daftarPoli->jadwalPeriksa->id_dokter;
                $paymentId = $pembayaran->id;

                DB::afterCommit(function () use ($doctorId, $paymentId) {
                    $doctor = User::where('role', 'dokter')->find($doctorId);

                    $payment = Pembayaran::find($paymentId);

                    if ($doctor && $payment) {
                        $doctor->notify(new BuktiPembayaranUploaded($payment));
                    }
                });

                // Remove previously rejected proof after replacement
                if ($oldPath && $oldPath !== $path) {
                    DB::afterCommit(function () use ($oldPath) {
                        Storage::disk('public')->delete($oldPath);
                    });
                }
            });
        } catch (\Throwable $exception) {
            // Remove the new file if database update fails
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        return back()->with(
            'success',
            'Bukti pembayaran berhasil diunggah dan menunggu verifikasi.'
        );
    }
}