<?php

namespace App\Notifications;

use App\Models\Pembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BuktiPembayaranUploaded extends Notification
{
    use Queueable;

    public function __construct(
        public Pembayaran $pembayaran
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $periksa = $this->pembayaran->periksa;
        $pendaftaran = $periksa->daftarPoli;

        return [
            'type' => 'bukti_pembayaran_uploaded',
            'title' => 'Bukti Pembayaran Baru',
            'message' => 'Pasien telah mengunggah bukti pembayaran untuk pemeriksaan #' . $periksa->id . '.',
            'id_pembayaran' => $this->pembayaran->id,
            'id_periksa' => $periksa->id,
            'id_daftar_poli' => $pendaftaran->id,
        ];
    }
}