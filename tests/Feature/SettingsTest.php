<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Regular user
        $this->user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $this->user->modules()->create(['module' => 'meeting_room']);
    }

    /**
     * Test admin can access settings index.
     */
    public function test_admin_can_access_settings_pages(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.cost-centers.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Cost Center');

        $responseSmtp = $this->actingAs($this->admin)->get(route('admin.settings.smtp.show'));
        $responseSmtp->assertStatus(200);
        $responseSmtp->assertSee('Pengaturan SMTP');
    }

    /**
     * Test non-admin cannot access settings pages.
     */
    public function test_non_admin_cannot_access_settings_pages(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.settings.cost-centers.index'));
        $response->assertStatus(403);

        $responseSmtp = $this->actingAs($this->user)->get(route('admin.settings.smtp.show'));
        $responseSmtp->assertStatus(403);
    }

    /**
     * Test Cost Center CRUD.
     */
    public function test_cost_center_crud(): void
    {
        // 1. Create
        $response = $this->actingAs($this->admin)->post(route('admin.settings.cost-centers.store'), [
            'cost_center_code' => '10501',
            'description' => 'SBA PME Limestone Mine Linkage',
            'person_responsible' => 'ADI HANDARBENI',
            'company_code' => 'ID50',
        ]);

        $response->assertRedirect(route('admin.settings.cost-centers.index'));
        $this->assertDatabaseHas('cost_centers', [
            'cost_center_code' => '10501',
            'description' => 'SBA PME Limestone Mine Linkage',
            'person_responsible' => 'ADI HANDARBENI',
            'company_code' => 'ID50',
        ]);

        $cc = CostCenter::first();

        // 2. Edit View
        $responseEdit = $this->actingAs($this->admin)->get(route('admin.settings.cost-centers.edit', $cc));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Edit Cost Center');

        // 3. Update
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.settings.cost-centers.update', $cc), [
            'cost_center_code' => '10501',
            'description' => 'Updated Description',
            'person_responsible' => 'ADI HANDARBENI',
            'company_code' => 'ID50',
        ]);

        $responseUpdate->assertRedirect(route('admin.settings.cost-centers.index'));
        $this->assertDatabaseHas('cost_centers', [
            'id' => $cc->id,
            'description' => 'Updated Description',
        ]);

        // 4. Delete
        $responseDelete = $this->actingAs($this->admin)->delete(route('admin.settings.cost-centers.destroy', $cc));
        $responseDelete->assertRedirect(route('admin.settings.cost-centers.index'));
        $this->assertDatabaseMissing('cost_centers', ['id' => $cc->id]);
    }

    /**
     * Test SMTP Settings Form & Database Encryption.
     */
    public function test_smtp_settings_form_and_encryption(): void
    {
        // 1. Save SMTP settings
        $response = $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'testuser',
            'password' => 'secret_password_123',
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);

        $setting = SmtpSetting::first();
        $this->assertNotNull($setting);
        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $setting->id]));
        
        // Assert password is NOT stored as plain text
        $this->assertNotEquals('secret_password_123', $setting->password);
        
        // Assert decrypt string matches the original password
        $decrypted = Crypt::decryptString($setting->password);
        $this->assertEquals('secret_password_123', $decrypted);

        // 2. Update without changing password
        $response2 = $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'id' => $setting->id,
            'host' => 'smtp.mailtrap.io',
            'port' => 587, // Changed port
            'username' => 'testuser',
            'password' => '', // Empty password
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);

        $response2->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $setting->id]));

        $updatedSetting = SmtpSetting::first();
        $this->assertEquals(587, $updatedSetting->port);
        // Password should remain intact and valid
        $this->assertEquals('secret_password_123', Crypt::decryptString($updatedSetting->password));
    }

    /**
     * Test SMTP test email successfully sent.
     */
    public function test_smtp_test_mail_success(): void
    {
        // 1. Seed SMTP settings
        $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'testuser@example.com',
            'password' => 'secret_password_123',
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);
        
        $setting = SmtpSetting::first();

        // 2. Fake mailer
        \Illuminate\Support\Facades\Mail::fake();

        // 3. Post to test route
        $response = $this->actingAs($this->admin)->post(route('admin.settings.smtp.test'), [
            'smtp_setting_id' => $setting->id,
            'recipient' => 'testuser@example.com',
        ]);

        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $setting->id]));
        $response->assertSessionHas('success', 'Email test berhasil dikirim. Silakan periksa inbox email.');

        // Verify email was sent to username (since it is a valid email)
        \Illuminate\Support\Facades\Mail::assertSent(function (\Illuminate\Mail\Mailable $mail) {
            return $mail->hasTo('testuser@example.com');
        });
    }

    /**
     * Test SMTP test email error handling and credential redaction.
     */
    public function test_smtp_test_mail_failure_redacts_credentials(): void
    {
        // 1. Seed SMTP settings
        $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'testuser@example.com',
            'password' => 'secret_password_123',
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);

        $setting = SmtpSetting::first();

        // 2. Mock Mail facade to throw exception containing credentials
        \Illuminate\Support\Facades\Mail::shouldReceive('forgetMailers')->zeroOrMoreTimes();
        
        $pendingMailMock = \Mockery::mock();
        $pendingMailMock->shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('Connection failed with user testuser@example.com and password secret_password_123'));

        \Illuminate\Support\Facades\Mail::shouldReceive('to')
            ->once()
            ->with('testuser@example.com')
            ->andReturn($pendingMailMock);

        // 3. Post to test route
        $response = $this->actingAs($this->admin)->post(route('admin.settings.smtp.test'), [
            'smtp_setting_id' => $setting->id,
            'recipient' => 'testuser@example.com',
        ]);

        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $setting->id]));
        
        // Assert error exists in session and does not contain plain text credentials
        $response->assertSessionHas('error');
        $error = session('error');
        $this->assertStringNotContainsString('secret_password_123', $error);
        $this->assertStringNotContainsString('testuser@example.com', $error);
        $this->assertStringContainsString('[REDACTED]', $error);
    }

    /**
     * Test non-admin user cannot access SMTP settings tabs or actions.
     */
    public function test_non_admin_cannot_access_smtp_tabs_or_actions(): void
    {
        // Try GET history tab
        $responseHistory = $this->actingAs($this->user)->get(route('admin.settings.smtp.show', ['tab' => 'history']));
        $responseHistory->assertStatus(403);

        // Try POST notifications update
        $responseNotifications = $this->actingAs($this->user)->post(route('admin.settings.smtp.notifications.update'), [
            'notifications' => [],
        ]);
        $responseNotifications->assertStatus(403);

        // Try PUT template update
        $template = \App\Models\EmailTemplate::first();
        $this->assertNotNull($template);
        
        $responseTemplate = $this->actingAs($this->user)->put(route('admin.settings.smtp.templates.update', $template), [
            'subject' => 'Hacker Subject',
            'body' => 'Hacker Body',
            'is_active' => true,
        ]);
        $responseTemplate->assertStatus(403);
    }

    /**
     * Test SMTP update logs history only when actual changes are made.
     */
    public function test_smtp_update_logs_history_only_on_changes(): void
    {
        // 1. First save (creates a history entry)
        $response1 = $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'testuser@example.com',
            'password' => 'secret_password_123',
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);
        
        $setting = SmtpSetting::first();
        $response1->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $setting->id]));
        $this->assertEquals(1, \App\Models\SmtpHistory::count());

        // 2. Second save with EXACT SAME DATA (should NOT create a new history entry)
        $response2 = $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'id' => $setting->id,
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'testuser@example.com',
            'password' => '', // blank password keeps existing password
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);
        
        $response2->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $setting->id]));
        $this->assertEquals(1, \App\Models\SmtpHistory::count()); // History count must remain 1!

        // 3. Third save with CHANGES (should create history entry)
        $response3 = $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'id' => $setting->id,
            'host' => 'smtp.newhost.com', // changed host
            'port' => 2525,
            'username' => 'testuser@example.com',
            'password' => 'secret_password_123',
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);
        
        $response3->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $setting->id]));
        $this->assertEquals(2, \App\Models\SmtpHistory::count());

        // Verify password is NOT saved in SmtpHistory
        $histories = \App\Models\SmtpHistory::all();
        foreach ($histories as $hist) {
            $this->assertFalse(isset($hist->password));
        }
    }

    /**
     * Test SMTP test email updates status and last success/failure timestamps.
     */
    public function test_smtp_test_mail_updates_connection_status(): void
    {
        // 1. Seed SMTP settings
        $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'testuser@example.com',
            'password' => 'secret_password_123',
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);

        $settings = SmtpSetting::first();

        // Mock successful email sending
        \Illuminate\Support\Facades\Mail::fake();

        $responseSuccess = $this->actingAs($this->admin)->post(route('admin.settings.smtp.test'), [
            'smtp_setting_id' => $settings->id,
            'recipient' => 'testuser@example.com',
        ]);
        $responseSuccess->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $settings->id]));

        // Assert SmtpSetting updated connection status to connected
        $settings = $settings->fresh();
        $this->assertEquals('connected', $settings->status);
        $this->assertNotNull($settings->last_success_at);
        $this->assertNull($settings->last_failure_at);
        $this->assertNull($settings->last_error_message);

        // Assert EmailLog has status = sent
        $this->assertDatabaseHas('email_logs', [
            'recipient' => 'testuser@example.com',
            'email_type' => 'test_email',
            'status' => 'sent',
        ]);

        // Mock failed email sending
        \Illuminate\Support\Facades\Mail::shouldReceive('forgetMailers')->zeroOrMoreTimes();
        
        $pendingMailMock = \Mockery::mock();
        $pendingMailMock->shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('Connection to smtp.mailtrap.io failed using username testuser@example.com and password secret_password_123'));

        \Illuminate\Support\Facades\Mail::shouldReceive('to')
            ->once()
            ->with('testuser@example.com')
            ->andReturn($pendingMailMock);

        $responseFailure = $this->actingAs($this->admin)->post(route('admin.settings.smtp.test'), [
            'smtp_setting_id' => $settings->id,
            'recipient' => 'testuser@example.com',
        ]);
        $responseFailure->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $settings->id]));

        // Assert SmtpSetting updated connection status to failed and redacted password
        $settings = $settings->fresh();
        $this->assertEquals('failed', $settings->status);
        $this->assertNotNull($settings->last_failure_at);
        $this->assertStringNotContainsString('secret_password_123', $settings->last_error_message);
        $this->assertStringNotContainsString('testuser@example.com', $settings->last_error_message);
        $this->assertStringContainsString('[REDACTED]', $settings->last_error_message);

        // Assert EmailLog has status = failed and is redacted
        $failedLog = \App\Models\EmailLog::where('status', 'failed')->first();
        $this->assertNotNull($failedLog);
        $this->assertStringNotContainsString('secret_password_123', $failedLog->error_detail);
        $this->assertStringNotContainsString('testuser@example.com', $failedLog->error_detail);
        $this->assertStringContainsString('[REDACTED]', $failedLog->error_detail);
    }

    /**
     * Test admin can update email templates.
     */
    public function test_admin_can_update_email_template(): void
    {
        $template = \App\Models\EmailTemplate::first();
        $this->assertNotNull($template);

        $response = $this->actingAs($this->admin)->put(route('admin.settings.smtp.templates.update', $template), [
            'subject' => 'Updated Subject Test',
            'body' => 'Updated body content for {user_name}.',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'templates']));
        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'subject' => 'Updated Subject Test',
            'body' => 'Updated body content for {user_name}.',
        ]);
    }

    /**
     * Test EmailService variables replacement works and is used during mail dispatch.
     */
    public function test_email_service_variables_replacement_and_notification_toggles(): void
    {
        // 1. Seed SMTP settings
        $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'testuser@example.com',
            'password' => 'secret_password_123',
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);

        // Create Room & Booking
        $room = \App\Models\Room::create([
            'name' => 'Rose Room',
            'capacity' => 10,
            'location' => 'Lantai 1',
            'status' => 'active',
        ]);
        $booking = \App\Models\Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $room->id,
            'title' => 'Important Meeting',
            'booking_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'approved',
            'meeting_type' => 'offline',
        ]);

        // Fake Mail
        \Illuminate\Support\Facades\Mail::fake();

        // 2. Test template mail sending (ON setting)
        $emailService = app(\App\Services\EmailService::class);
        $result = $emailService->sendReservationCreated($booking);

        $this->assertTrue($result);
        \Illuminate\Support\Facades\Mail::assertSent(function (\Illuminate\Mail\Mailable $mail) use ($booking) {
            $this->assertTrue($mail->hasTo($this->user->email));
            
            // Render mailable body to check variable replacement
            $html = $mail->render();
            $this->assertStringContainsString($this->user->name, $html);
            $this->assertStringContainsString('Rose Room', $html);
            
            return true;
        });

        // Verify EmailLog has been written
        $this->assertDatabaseHas('email_logs', [
            'recipient' => $this->user->email,
            'email_type' => 'reservasi_baru',
            'status' => 'sent',
        ]);

        // 3. Test template mail sending with OFF setting
        \App\Models\EmailNotificationSetting::where('event_key', 'reservasi_baru')->update([
            'is_active' => false,
        ]);

        \Illuminate\Support\Facades\Mail::fake(); // reset fakes
        $result2 = $emailService->sendReservationCreated($booking);
        $this->assertFalse($result2); // should skip sending
        \Illuminate\Support\Facades\Mail::assertNothingSent();
    }

    /**
     * Test meeting reminder scheduler sends reminders on time.
     */
    public function test_meeting_reminder_scheduler_sends_reminders_on_time(): void
    {
        // 1. Seed SMTP settings
        $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'testuser@example.com',
            'password' => 'secret_password_123',
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);

        // Enable meeting reminder notification with 30 minutes duration
        \App\Models\EmailNotificationSetting::updateOrCreate(
            ['event_key' => 'pengingat_meeting'],
            ['event_name' => 'Pengingat Meeting', 'is_active' => true, 'reminder_time' => 30]
        );
        \App\Models\EmailTemplate::updateOrCreate(
            ['template_key' => 'pengingat_meeting'],
            ['name' => 'Pengingat Meeting', 'subject' => 'Pengingat Meeting #{id}', 'body' => 'Pengingat meeting', 'is_active' => true]
        );

        // Create Room & Booking starting exactly in 30 minutes
        $room = \App\Models\Room::create([
            'name' => 'Tulip Room',
            'capacity' => 15,
            'location' => 'Lantai 1',
            'status' => 'active',
        ]);
        $targetStartTime = \Carbon\Carbon::now('Asia/Jakarta')->addMinutes(30);
        $booking = \App\Models\Booking::create([
            'user_id' => $this->user->id,
            'room_id' => $room->id,
            'title' => 'Reminder Test Meeting',
            'booking_date' => $targetStartTime->format('Y-m-d'),
            'start_time' => $targetStartTime->format('H:i:s'),
            'end_time' => $targetStartTime->copy()->addHour()->format('H:i:s'),
            'status' => 'approved',
            'meeting_type' => 'offline',
        ]);

        // Fake Mail
        \Illuminate\Support\Facades\Mail::fake();

        // Retrieve and execute the scheduled tasks defined in console.php
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
        $events = collect($schedule->events());
        $this->assertNotEmpty($events);

        foreach ($events as $event) {
            $event->run(app());
        }

        // Verify the meeting reminder email was sent
        \Illuminate\Support\Facades\Mail::assertSent(function (\Illuminate\Mail\Mailable $mail) {
            return $mail->hasTo($this->user->email);
        });

        // Verify log was recorded
        $this->assertDatabaseHas('email_logs', [
            'recipient' => $this->user->email,
            'email_type' => 'pengingat_meeting',
            'status' => 'sent',
        ]);
    }

    /**
     * Test creating a new SMTP configuration with empty password fails.
     */
    public function test_create_smtp_with_empty_password_fails(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp.mailtrap.io',
            'port' => 2525,
            'username' => 'newuser@example.com',
            'password' => '', // Empty password when creating
            'encryption' => 'tls',
            'from_address' => 'noreply@sba.co.id',
            'from_name' => 'Admin SBA',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals(0, SmtpSetting::count());
    }

    /**
     * Test multiple SMTP configurations list and selection.
     */
    public function test_multiple_smtp_configurations_list_and_selection(): void
    {
        // 1. Create first SMTP
        $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp1.example.com',
            'port' => 587,
            'username' => 'smtp1@example.com',
            'password' => 'pass123',
            'encryption' => 'tls',
            'from_address' => 'smtp1@example.com',
            'from_name' => 'SMTP One',
        ]);

        // 2. Create second SMTP
        $this->actingAs($this->admin)->post(route('admin.settings.smtp.update'), [
            'host' => 'smtp2.example.com',
            'port' => 465,
            'username' => 'smtp2@example.com',
            'password' => 'pass456',
            'encryption' => 'ssl',
            'from_address' => 'smtp2@example.com',
            'from_name' => 'SMTP Two',
        ]);

        $this->assertEquals(2, SmtpSetting::count());

        $response = $this->actingAs($this->admin)->get(route('admin.settings.smtp.show'));
        $response->assertStatus(200);
        $response->assertSee('smtp1@example.com');
        $response->assertSee('smtp2@example.com');
    }

    /**
     * Test setting a configuration as active/default.
     */
    public function test_set_active_smtp_config(): void
    {
        $smtp1 = SmtpSetting::create([
            'host' => 'smtp1.example.com',
            'port' => 587,
            'username' => 'smtp1@example.com',
            'password' => Crypt::encryptString('pass123'),
            'encryption' => 'tls',
            'from_address' => 'smtp1@example.com',
            'from_name' => 'SMTP One',
            'is_active' => true,
        ]);

        $smtp2 = SmtpSetting::create([
            'host' => 'smtp2.example.com',
            'port' => 465,
            'username' => 'smtp2@example.com',
            'password' => Crypt::encryptString('pass456'),
            'encryption' => 'ssl',
            'from_address' => 'smtp2@example.com',
            'from_name' => 'SMTP Two',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.settings.smtp.activate', $smtp2->id));
        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $smtp2->id]));

        $this->assertTrue($smtp2->fresh()->is_active);
        $this->assertFalse($smtp1->fresh()->is_active);
    }

    /**
     * Test deleting an SMTP configuration.
     */
    public function test_delete_smtp_configuration(): void
    {
        $smtp = SmtpSetting::create([
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'smtp@example.com',
            'password' => Crypt::encryptString('pass123'),
            'encryption' => 'tls',
            'from_address' => 'smtp@example.com',
            'from_name' => 'SMTP Test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.settings.smtp.delete', $smtp->id));
        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config']));

        $this->assertDatabaseMissing('smtp_settings', ['id' => $smtp->id]);
    }

    /**
     * Test deleting active SMTP configuration promotes next remaining configuration to active.
     */
    public function test_delete_active_smtp_config_promotes_next_config(): void
    {
        $smtp1 = SmtpSetting::create([
            'host' => 'smtp1.example.com',
            'port' => 587,
            'username' => 'smtp1@example.com',
            'password' => Crypt::encryptString('pass123'),
            'encryption' => 'tls',
            'from_address' => 'smtp1@example.com',
            'from_name' => 'SMTP One',
            'is_active' => true,
        ]);

        $smtp2 = SmtpSetting::create([
            'host' => 'smtp2.example.com',
            'port' => 465,
            'username' => 'smtp2@example.com',
            'password' => Crypt::encryptString('pass456'),
            'encryption' => 'ssl',
            'from_address' => 'smtp2@example.com',
            'from_name' => 'SMTP Two',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.settings.smtp.delete', $smtp1->id));
        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config']));

        $this->assertDatabaseMissing('smtp_settings', ['id' => $smtp1->id]);
        $this->assertTrue($smtp2->fresh()->is_active);
    }

    /**
     * Test delete button is rendered in the UI even when config is active.
     */
    public function test_delete_button_rendered_for_active_smtp_config(): void
    {
        $smtp = SmtpSetting::create([
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'smtp@example.com',
            'password' => Crypt::encryptString('pass123'),
            'encryption' => 'tls',
            'from_address' => 'smtp@example.com',
            'from_name' => 'SMTP Test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.settings.smtp.show', ['tab' => 'config']));
        $response->assertStatus(200);
        $response->assertSee('triggerDelete(' . $smtp->id . ', true)', false);
    }

    /**
     * Test connection action triggers verification.
     */
    public function test_test_connection_action(): void
    {
        $smtp = SmtpSetting::create([
            'host' => 'localhost',
            'port' => 1025,
            'username' => 'smtp@example.com',
            'password' => Crypt::encryptString('pass123'),
            'encryption' => 'none',
            'from_address' => 'smtp@example.com',
            'from_name' => 'SMTP Test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.settings.smtp.test-connection'), [
            'smtp_setting_id' => $smtp->id,
        ]);

        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $smtp->id]));
    }

    /**
     * Test SMTP connection test success.
     */
    public function test_smtp_test_connection_success(): void
    {
        $smtp = SmtpSetting::create([
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'testuser@example.com',
            'password' => Crypt::encryptString('secret_password_123'),
            'encryption' => 'tls',
            'from_address' => 'smtp@example.com',
            'from_name' => 'SMTP Test',
            'is_active' => true,
        ]);

        // Mock Mail manager and transport
        \Illuminate\Support\Facades\Mail::shouldReceive('forgetMailers')->zeroOrMoreTimes();
        
        $transportMock = \Mockery::mock(\Symfony\Component\Mailer\Transport\TransportInterface::class);
        $transportMock->shouldReceive('start')->once();
        $transportMock->shouldReceive('stop')->once();

        $mailerMock = \Mockery::mock(\Illuminate\Mail\Mailer::class);
        $mailerMock->shouldReceive('getSymfonyTransport')->once()->andReturn($transportMock);

        \Illuminate\Support\Facades\Mail::shouldReceive('mailer')
            ->with('smtp')
            ->andReturn($mailerMock);

        $response = $this->actingAs($this->admin)->post(route('admin.settings.smtp.test-connection'), [
            'smtp_setting_id' => $smtp->id,
        ]);

        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $smtp->id]));
        $response->assertSessionHas('success', 'SMTP connection successful.');

        // Assert SmtpSetting updated connection status to connected
        $smtp = $smtp->fresh();
        $this->assertEquals('connected', $smtp->status);
        $this->assertNotNull($smtp->last_success_at);
        $this->assertNull($smtp->last_failure_at);
    }

    /**
     * Test SMTP connection test failure redacts credentials.
     */
    public function test_smtp_test_connection_failure_redacts_credentials(): void
    {
        $smtp = SmtpSetting::create([
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'testuser@example.com',
            'password' => Crypt::encryptString('secret_password_123'),
            'encryption' => 'tls',
            'from_address' => 'smtp@example.com',
            'from_name' => 'SMTP Test',
            'is_active' => true,
        ]);

        // Mock Mail manager and transport
        \Illuminate\Support\Facades\Mail::shouldReceive('forgetMailers')->zeroOrMoreTimes();
        
        $transportMock = \Mockery::mock(\Symfony\Component\Mailer\Transport\TransportInterface::class);
        $transportMock->shouldReceive('start')
            ->once()
            ->andThrow(new \Exception('Authentication failed for user testuser@example.com and password secret_password_123 in DSN smtp://testuser%40example.com:secret_password_123@smtp.example.com'));

        $mailerMock = \Mockery::mock(\Illuminate\Mail\Mailer::class);
        $mailerMock->shouldReceive('getSymfonyTransport')->once()->andReturn($transportMock);

        \Illuminate\Support\Facades\Mail::shouldReceive('mailer')
            ->with('smtp')
            ->andReturn($mailerMock);

        $response = $this->actingAs($this->admin)->post(route('admin.settings.smtp.test-connection'), [
            'smtp_setting_id' => $smtp->id,
        ]);

        $response->assertRedirect(route('admin.settings.smtp.show', ['tab' => 'config', 'id' => $smtp->id]));
        $response->assertSessionHas('error');

        $error = session('error');
        $this->assertStringNotContainsString('secret_password_123', $error);
        $this->assertStringNotContainsString('testuser@example.com', $error);
        $this->assertStringContainsString('[REDACTED]', $error);

        // Assert fresh settings in DB
        $smtp = $smtp->fresh();
        $this->assertEquals('failed', $smtp->status);
        $this->assertNotNull($smtp->last_failure_at);
        $this->assertStringNotContainsString('secret_password_123', $smtp->last_error_message);
        $this->assertStringNotContainsString('testuser@example.com', $smtp->last_error_message);
        $this->assertStringContainsString('[REDACTED]', $smtp->last_error_message);
    }
}

