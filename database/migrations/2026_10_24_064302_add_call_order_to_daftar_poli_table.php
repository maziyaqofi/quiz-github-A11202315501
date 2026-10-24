<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daftar_poli', function (Blueprint $table) {
            $table->unsignedInteger('urutan_panggil')->nullable();
        });

        // Initialize call order from existing queue numbers
        DB::table('daftar_poli')
            ->whereNotNull('no_antrian')
            ->update([
                'urutan_panggil' => DB::raw('no_antrian'),
            ]);
    }

    public function down(): void
    {
        Schema::table('daftar_poli', function (Blueprint $table) {
            $table->dropColumn('urutan_panggil');
        });
    }
};