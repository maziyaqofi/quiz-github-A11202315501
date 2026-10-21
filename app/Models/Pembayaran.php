<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'id_periksa',
        'bukti_pembayaran',
        'status',
        'tanggal_upload',
        'tanggal_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_upload' => 'datetime',
            'tanggal_verifikasi' => 'datetime',
        ];
    }

    public function periksa(): BelongsTo
    {
        return $this->belongsTo(Periksa::class, 'id_periksa');
    }
}