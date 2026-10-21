<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_periksa')
                ->unique()
                ->constrained('periksa')
                ->cascadeOnDelete();

            $table->string('bukti_pembayaran')->nullable();

            $table->string('status')->default('menunggu');

            $table->timestamp('tanggal_upload')->nullable();

            $table->timestamp('tanggal_verifikasi')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};