<?php

namespace App\Notifications;

use App\Models\Pembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PembayaranLunas extends Notification
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

        return [
            'type' => 'pembayaran_lunas',
            'title' => 'Pembayaran Berhasil Diverifikasi',
            'message' => 'Pembayaran pemeriksaan #' . $periksa->id .
                ' sebesar Rp ' .
                number_format($periksa->biaya_periksa, 0, ',', '.') .
                ' telah diverifikasi dan dinyatakan lunas.',
            'id_pembayaran' => $this->pembayaran->id,
            'id_periksa' => $periksa->id,
            'id_daftar_poli' => $periksa->id_daftar_poli,
        ];
    }
}