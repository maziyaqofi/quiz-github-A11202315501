<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        // Create two users with different roles
        $doctor = User::factory()->create([
            'role' => 'dokter',
        ]);

        $patient = User::factory()->create([
            'role' => 'pasien',
        ]);

        // Create a notification belonging to the doctor
        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'test_notification',
            'notifiable_type' => User::class,
            'notifiable_id' => $doctor->id,
            'data' => json_encode([
                'title' => 'Doctor Notification',
                'message' => 'Private notification for doctor.',
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Patient attempts to mark doctor's notification as read
        $response = $this->actingAs($patient)
            ->patch(route('notifications.read', $notificationId));

        // Access must be denied
        $response->assertNotFound();

        // Doctor's notification must remain unread
        $this->assertDatabaseHas('notifications', [
            'id' => $notificationId,
            'notifiable_id' => $doctor->id,
            'read_at' => null,
        ]);
    }
}