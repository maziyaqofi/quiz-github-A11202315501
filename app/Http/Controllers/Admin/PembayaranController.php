<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\User;
use App\Notifications\PembayaranLunas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    public function index()
    {
        // Retrieve payments with related patient and doctor information
        $pembayarans = Pembayaran::with([
            'periksa.daftarPoli.pasien',
            'periksa.daftarPoli.jadwalPeriksa.dokter',
        ])
            ->latest()
            ->paginate(10);

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    public function show($id)
    {
        // Retrieve payment details
        $pembayaran = Pembayaran::with([
            'periksa.daftarPoli.pasien',
            'periksa.daftarPoli.jadwalPeriksa.dokter',
        ])->findOrFail($id);

        return view('admin.pembayaran.show', compact('pembayaran'));
    }

    public function verify(Request $request, $id)
    {
        // Validate the verification decision
        $validated = $request->validate([
            'status' => ['required', 'in:lunas,ditolak'],
        ]);

        DB::transaction(function () use ($id, $validated) {
            // Lock the payment to prevent concurrent verification
            $pembayaran = Pembayaran::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pembayaran->status !== 'menunggu'
                || !$pembayaran->tanggal_upload
                || !$pembayaran->bukti_pembayaran) {
                throw ValidationException::withMessages([
                    'status' => 'Pembayaran ini tidak dapat diverifikasi.',
                ]);
            }

            $pembayaran->status = $validated['status'];

            if ($validated['status'] === 'lunas') {
                $pembayaran->tanggal_verifikasi = now();
            } else {
                $pembayaran->tanggal_verifikasi = null;
            }

            $pembayaran->save();

            // Notify the patient only after a successful payment verification
            if ($validated['status'] === 'lunas') {
                $patientId = $pembayaran->periksa->daftarPoli->id_pasien;
                $paymentId = $pembayaran->id;

                DB::afterCommit(function () use ($patientId, $paymentId) {
                    $patient = User::where('role', 'pasien')
                        ->find($patientId);

                    $payment = Pembayaran::find($paymentId);

                    if ($patient && $payment) {
                        $patient->notify(new PembayaranLunas($payment));
                    }
                });
            }
        }, 3);

        return redirect()
            ->route('admin.pembayaran.index')
            ->with('success', 'Status pembayaran berhasil diperbarui.');
    }
}