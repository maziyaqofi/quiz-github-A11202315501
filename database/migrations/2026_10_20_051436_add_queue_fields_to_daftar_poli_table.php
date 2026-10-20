
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
            $table->date('tanggal_kunjungan')->nullable();
            $table->string('status_antrian', 20)->default('menunggu');
        });

        // Initialize existing registrations based on examination records
        DB::table('daftar_poli')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('periksa')
                    ->whereColumn('periksa.id_daftar_poli', 'daftar_poli.id');
            })
            ->update(['status_antrian' => 'selesai']);

        // Historical visit dates cannot be inferred reliably from registration time
        // Existing records will keep tanggal_kunjungan as NULL
    }

    public function down(): void
    {
        Schema::table('daftar_poli', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_kunjungan',
                'status_antrian',
            ]);
        });
    }
};
