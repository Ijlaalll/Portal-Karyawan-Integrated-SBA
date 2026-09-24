<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ga_user_without_meeting_room_module_can_mark_all_notifications_as_read(): void
    {
        $gaUser = User::factory()->create([
            'role'   => 'ga',
            'status' => 'active',
        ]);
        // Only give ATK access, NO meeting_room module
        $gaUser->modules()->create(['module' => 'atk']);

        Notification::create([
            'user_id' => $gaUser->id,
            'title'   => 'Permintaan ATK Baru',
            'message' => 'Ada permohonan ATK baru yang perlu diproses.',
            'is_read' => false,
        ]);

        $this->actingAs($gaUser);

        $response = $this->post(route('notifications.mark-all-read'));

        $response->assertRedirect();
        $this->assertEquals(0, Notification::where('user_id', $gaUser->id)->where('is_read', false)->count());
    }

    public function test_get_request_to_read_all_notifications_works_properly(): void
    {
        $user = User::factory()->create([
            'role'   => 'user',
            'status' => 'active',
        ]);
        $user->modules()->create(['module' => 'toilet_inspection']);

        Notification::create([
            'user_id' => $user->id,
            'title'   => 'Notifikasi Toilet',
            'message' => 'Inspeksi telah selesai.',
            'is_read' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('notifications.mark-all-read'));

        $response->assertRedirect();
        $this->assertEquals(0, Notification::where('user_id', $user->id)->where('is_read', false)->count());
    }

    public function test_user_can_read_single_notification_and_redirect(): void
    {
        $user = User::factory()->create([
            'role'   => 'user',
            'status' => 'active',
        ]);

        $notification = Notification::create([
            'user_id' => $user->id,
            'title'   => 'Pengingat Meeting',
            'message' => 'Meeting dimulai 15 menit lagi.',
            'link'    => '/login',
            'is_read' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('notifications.read', $notification));

        $response->assertRedirect('/login');
        $this->assertTrue($notification->fresh()->is_read);
    }
}
