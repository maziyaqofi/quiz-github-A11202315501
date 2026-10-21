<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_verification_does_not_create_duplicate_notifications(): void
    {
        // Create users for each role
        $admin = User::factory()->create(['role' => 'admin']);
        $doctor = User::factory()->create(['role' => 'dokter']);
        $patient = User::factory()->create(['role' => 'pasien']);

        // Create a doctor's schedule
        $scheduleId = DB::table('jadwal_periksa')->insertGetId([
            'id_dokter' => $doctor->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '12:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Register the patient
        $registrationId = DB::table('daftar_poli')->insertGetId([
            'id_pasien' => $patient->id,
            'id_jadwal' => $scheduleId,
            'keluhan' => 'Demam',
            'no_antrian' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create an examination
        $examinationId = DB::table('periksa')->insertGetId([
            'id_daftar_poli' => $registrationId,
            'tgl_periksa' => now(),
            'biaya_periksa' => 150000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a payment awaiting verification
        $paymentId = DB::table('pembayaran')->insertGetId([
            'id_periksa' => $examinationId,
            'bukti_pembayaran' => 'test/payment-proof.jpg',
            'status' => 'menunggu',
            'tanggal_upload' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // First verification should succeed
        $firstResponse = $this->actingAs($admin)->patch(
            route('admin.pembayaran.verify', $paymentId),
            ['status' => 'lunas']
        );

        $firstResponse->assertRedirect(
            route('admin.pembayaran.index')
        );

        $this->assertDatabaseHas('pembayaran', [
            'id' => $paymentId,
            'status' => 'lunas',
        ]);

        // Exactly one notification should exist
        $this->assertSame(
            1,
            $patient->notifications()->count()
        );

        // Second verification should be rejected
        $secondResponse = $this->actingAs($admin)->patch(
            route('admin.pembayaran.verify', $paymentId),
            ['status' => 'lunas']
        );

        $secondResponse->assertSessionHasErrors('status');

        // Payment remains completed
        $this->assertDatabaseHas('pembayaran', [
            'id' => $paymentId,
            'status' => 'lunas',
        ]);

        // No additional notification should be created
        $this->assertSame(
            1,
            $patient->notifications()->count()
        );
    }
}
