<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\EmailNotificationSetting;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Models\Booking;
use App\Mail\TestSmtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected SmtpSettingsService $smtpSettingsService;

    public function __construct(SmtpSettingsService $smtpSettingsService)
    {
        $this->smtpSettingsService = $smtpSettingsService;
    }

    /**
     * Re-apply SMTP configuration dynamically from database.
     */
    public function applySmtpConfig(?SmtpSetting $smtp = null): void
    {
        if (!$smtp) {
            $smtp = $this->smtpSettingsService->getSettings();
        }
        
        if ($smtp) {
            config([
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => $this->smtpSettingsService->decryptPassword($smtp->password),
                'mail.mailers.smtp.encryption' => $smtp->encryption,
                'mail.from.address' => $smtp->from_address,
                'mail.from.name' => $smtp->from_name,
                'mail.default' => 'smtp',
            ]);

            // Force Laravel's MailManager to resolve config again on next send
            if (app()->resolved('mail.manager')) {
                app('mail.manager')->forgetMailers();
            }
        }
    }

    /**
     * Send a template-based email with event notification checks.
     */
    public function sendTemplateEmail(string $recipient, string $eventKey, array $variables = []): bool
    {
        // 1. Check event notification settings
        $notifSetting = EmailNotificationSetting::where('event_key', $eventKey)->first();
        if ($notifSetting && !$notifSetting->is_active) {
            // Notification is turned OFF by admin, do not send
            return false;
        }

        // 2. Load active email template
        $template = EmailTemplate::where('template_key', $eventKey)->first();
        if (!$template || !$template->is_active) {
            // If template not found or inactive, skip or fallback
            return false;
        }

        // Add default reminder time if it's meeting_reminder
        if ($eventKey === 'pengingat_meeting' && !isset($variables['reminder_time']) && $notifSetting) {
            $variables['reminder_time'] = $notifSetting->reminder_time;
        }

        // 3. Replace template variables
        $subject = $template->subject;
        $body = $template->body;
        foreach ($variables as $key => $value) {
            $subject = str_replace('{' . $key . '}', $value ?? '', $subject);
            $body = str_replace('{' . $key . '}', $value ?? '', $body);
        }

        // 4. Apply dynamic mail config
        $this->applySmtpConfig();

        try {
            // 5. Send email using dynamic TemplateMail
            Mail::to($recipient)->send(
                new \App\Mail\TemplateMail($subject, $body)
            );

            // 6. Log success
            EmailLog::create([
                'recipient' => $recipient,
                'subject' => $subject,
                'email_type' => $eventKey,
                'status' => 'sent',
            ]);

            return true;
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();

            // Sanitize SMTP credentials from error message
            $smtp = $this->smtpSettingsService->getSettings();
            if ($smtp) {
                if (!empty($smtp->password)) {
                    $decrypted = $this->smtpSettingsService->decryptPassword($smtp->password);
                    if ($decrypted) {
                        $errorMessage = str_replace($decrypted, '[REDACTED]', $errorMessage);
                    }
                }
                if (!empty($smtp->username)) {
                    $errorMessage = str_replace($smtp->username, '[REDACTED]', $errorMessage);
                }
            }

            // Log the redacted error to database
            EmailLog::create([
                'recipient' => $recipient,
                'subject' => $subject,
                'email_type' => $eventKey,
                'status' => 'failed',
                'error_detail' => 'SMTP Error: ' . $errorMessage,
            ]);

            // Log exception safely
            Log::error("Failed to send template email [{$eventKey}]: " . $errorMessage);

            return false;
        }
    }

    /**
     * Send new reservation email.
     */
    public function sendReservationCreated(Booking $booking): bool
    {
        return $this->sendTemplateEmail($booking->user->email, 'reservasi_baru', $this->getBookingVariables($booking));
    }

    /**
     * Send approved reservation email.
     */
    public function sendReservationApproved(Booking $booking): bool
    {
        return $this->sendTemplateEmail($booking->user->email, 'reservasi_disetujui', $this->getBookingVariables($booking));
    }

    /**
     * Send rejected reservation email.
     */
    public function sendReservationRejected(Booking $booking): bool
    {
        return $this->sendTemplateEmail($booking->user->email, 'reservasi_ditolak', $this->getBookingVariables($booking));
    }

    /**
     * Send cancelled reservation email.
     */
    public function sendReservationCancelled(Booking $booking): bool
    {
        return $this->sendTemplateEmail($booking->user->email, 'reservasi_dibatalkan', $this->getBookingVariables($booking));
    }

    /**
     * Send meeting reminder email.
     */
    public function sendMeetingReminder(Booking $booking): bool
    {
        return $this->sendTemplateEmail($booking->user->email, 'pengingat_meeting', $this->getBookingVariables($booking));
    }

    /**
     * Send account created email.
     */
    public function sendAccountCreated(User $user, string $plainPassword): bool
    {
        return $this->sendTemplateEmail($user->email, 'akun_dibuat', [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'role' => strtoupper($user->role),
            'password' => $plainPassword,
        ]);
    }

    /**
     * Send password reset email.
     */
    public function sendPasswordReset(User $user, string $resetLink): bool
    {
        return $this->sendTemplateEmail($user->email, 'reset_password', [
            'user_name' => $user->name,
            'reset_link' => $resetLink,
        ]);
    }

    /**
     * Helper to format booking variables.
     */
    protected function getBookingVariables(Booking $booking): array
    {
        return [
            'user_name' => $booking->user->name,
            'user_email' => $booking->user->email,
            'room_name' => $booking->room->name,
            'date' => $booking->booking_date ? $booking->booking_date->format('d M Y') : '',
            'start_time' => $booking->start_time ? substr($booking->start_time, 0, 5) : '',
            'end_time' => $booking->end_time ? substr($booking->end_time, 0, 5) : '',
            'reservation_id' => $booking->id,
            'status' => strtoupper($booking->status),
            'cancellation_note' => $booking->cancellation_note,
        ];
    }
}
