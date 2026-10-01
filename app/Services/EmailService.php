<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\EmailNotificationSetting;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Models\Booking;
use App\Models\AtkRequest;
use App\Models\ExitPermit;
use App\Models\VehicleRequest;
use App\Models\Driver;
use App\Models\Inspection;
use App\Mail\TestSmtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
            $encryption = strtolower($smtp->encryption ?? '');
            $isExplicitTls = in_array($encryption, ['tls', 'ssl']);

            config([
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => $this->smtpSettingsService->decryptPassword($smtp->password),
                'mail.mailers.smtp.encryption' => $isExplicitTls ? $smtp->encryption : null,
                'mail.mailers.smtp.auto_tls' => $isExplicitTls,
                'mail.mailers.smtp.verify_peer' => false,
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
            $valStr = is_array($value) ? implode(', ', $value) : (string) ($value ?? '');
            $subject = str_replace('{' . $key . '}', $valStr, $subject);
            $body = str_replace('{' . $key . '}', $valStr, $body);
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

    // ==========================================
    // ROOM MEETING & HYBRID TASKS
    // ==========================================
    public function sendHybridTaskToGa(Booking $booking, string $recipientEmail): bool
    {
        $vars = $this->getBookingVariables($booking);
        $vars['title'] = $booking->title;
        $vars['user_department'] = $booking->user->department ?? '-';
        $vars['additional_requirements'] = $booking->additional_requirements ?? '-';
        return $this->sendTemplateEmail($recipientEmail, 'meeting_hybrid_persiapan_ga', $vars);
    }

    public function sendHybridTaskToIt(Booking $booking, string $recipientEmail): bool
    {
        $vars = $this->getBookingVariables($booking);
        $vars['title'] = $booking->title;
        $vars['user_department'] = $booking->user->department ?? '-';
        $vars['additional_requirements'] = $booking->additional_requirements ?? '-';
        return $this->sendTemplateEmail($recipientEmail, 'meeting_hybrid_persiapan_it', $vars);
    }

    public function sendReservationCreatedAdmin(Booking $booking, string $adminEmail): bool
    {
        $vars = $this->getBookingVariables($booking);
        $vars['title'] = $booking->title;
        $vars['user_department'] = $booking->user->department ?? '-';
        $vars['meeting_type'] = strtoupper($booking->meeting_type);
        $vars['participant_count'] = $booking->participant_count ?? 1;
        return $this->sendTemplateEmail($adminEmail, 'reservasi_baru_admin', $vars);
    }

    // ==========================================
    // ATK (ALAT TULIS KANTOR)
    // ==========================================
    public function sendAtkRequestSubmittedToRequester(AtkRequest $request): bool
    {
        $request->loadMissing(['user', 'items.variant.item']);
        return $this->sendTemplateEmail($request->user->email, 'atk_request_submitted_requester', [
            'user_name' => $request->user->name,
            'user_email' => $request->user->email,
            'request_number' => $request->request_number,
            'date' => $request->created_at ? $request->created_at->format('d M Y H:i') : '',
            'note' => $request->note ?? '-',
            'item_count' => $request->items->count(),
        ]);
    }

    public function sendAtkRequestSubmittedToGa(AtkRequest $request, string $gaEmail): bool
    {
        $request->loadMissing(['user', 'items.variant.item']);
        $itemsDetail = $request->items->map(function ($i) {
            $itemName = $i->variant && $i->variant->item ? $i->variant->item->name . ' (' . $i->variant->name . ')' : 'Barang';
            $unit = $i->variant->unit ?? 'pcs';
            return "- {$itemName}: {$i->quantity} {$unit}";
        })->implode("\n");

        return $this->sendTemplateEmail($gaEmail, 'atk_request_submitted_ga', [
            'user_name' => $request->user->name,
            'user_department' => $request->user->department ?? '-',
            'request_number' => $request->request_number,
            'date' => $request->created_at ? $request->created_at->format('d M Y H:i') : '',
            'note' => $request->note ?? '-',
            'items_detail' => $itemsDetail ?: '- Tidak ada rincian -',
        ]);
    }

    public function sendAtkRequestApproved(AtkRequest $request): bool
    {
        $request->loadMissing('user');
        return $this->sendTemplateEmail($request->user->email, 'atk_request_approved', [
            'user_name' => $request->user->name,
            'request_number' => $request->request_number,
        ]);
    }

    public function sendAtkRequestRejected(AtkRequest $request, string $reason): bool
    {
        $request->loadMissing('user');
        return $this->sendTemplateEmail($request->user->email, 'atk_request_rejected', [
            'user_name' => $request->user->name,
            'request_number' => $request->request_number,
            'rejection_reason' => $reason,
        ]);
    }

    public function sendAtkRequestReady(AtkRequest $request): bool
    {
        $request->loadMissing('user');
        return $this->sendTemplateEmail($request->user->email, 'atk_request_ready', [
            'user_name' => $request->user->name,
            'request_number' => $request->request_number,
        ]);
    }

    // ==========================================
    // SIK (SURAT IZIN KELUAR)
    // ==========================================
    public function sendSikSubmittedToRequester(ExitPermit $sik): bool
    {
        $sik->loadMissing(['user', 'approver']);
        return $this->sendTemplateEmail($sik->user->email, 'sik_submitted_requester', [
            'user_name' => $sik->user->name,
            'sik_number' => $sik->sik_number,
            'permit_date' => $sik->permit_date ? Carbon::parse($sik->permit_date)->format('d M Y') : '',
            'departure_time' => $sik->expected_departure_time,
            'return_time' => $sik->expected_return_time,
            'purpose_type' => strtoupper($sik->purpose_type),
            'purpose' => $sik->purpose,
            'approver_name' => $sik->approver ? $sik->approver->name : 'Atasan',
        ]);
    }

    public function sendSikSubmittedToApprover(ExitPermit $sik, User $approver): bool
    {
        $sik->loadMissing('user');
        $vehicleInfo = $sik->vehicle_type === 'company_vehicle' ? 'Mobil Perusahaan' : ($sik->vehicle_type === 'private_vehicle' ? 'Kendaraan Pribadi' : 'Tanpa Kendaraan');
        if (!empty($sik->vehicle_details)) {
            $vehicleInfo .= " ({$sik->vehicle_details})";
        }

        return $this->sendTemplateEmail($approver->email, 'sik_submitted_approver', [
            'approver_name' => $approver->name,
            'user_name' => $sik->user->name,
            'user_department' => $sik->department ?? $sik->user->department ?? '-',
            'user_position' => $sik->position ?? $sik->user->position ?? '-',
            'sik_number' => $sik->sik_number,
            'permit_date' => $sik->permit_date ? Carbon::parse($sik->permit_date)->format('d M Y') : '',
            'departure_time' => $sik->expected_departure_time,
            'return_time' => $sik->expected_return_time,
            'purpose_type' => strtoupper($sik->purpose_type),
            'purpose' => $sik->purpose,
            'purpose_detail' => $sik->purpose_detail ?? '-',
            'vehicle_info' => $vehicleInfo,
        ]);
    }

    public function sendSikApproved(ExitPermit $sik): bool
    {
        $sik->loadMissing(['user', 'approver']);
        return $this->sendTemplateEmail($sik->user->email, 'sik_approved', [
            'user_name' => $sik->user->name,
            'sik_number' => $sik->sik_number,
            'permit_date' => $sik->permit_date ? Carbon::parse($sik->permit_date)->format('d M Y') : '',
            'departure_time' => $sik->expected_departure_time,
            'return_time' => $sik->expected_return_time,
            'purpose' => $sik->purpose,
            'approver_name' => $sik->approver ? $sik->approver->name : 'Atasan',
            'approval_note' => $sik->approval_note ?? '-',
        ]);
    }

    public function sendSikApprovedToSecurity(ExitPermit $sik, string $securityEmail): bool
    {
        $sik->loadMissing(['user', 'approver']);
        $vehicleInfo = $sik->vehicle_type === 'company_vehicle' ? 'Mobil Perusahaan' : ($sik->vehicle_type === 'private_vehicle' ? 'Kendaraan Pribadi' : 'Tanpa Kendaraan');
        if (!empty($sik->vehicle_details)) {
            $vehicleInfo .= " ({$sik->vehicle_details})";
        }

        return $this->sendTemplateEmail($securityEmail, 'sik_approved_security', [
            'user_name' => $sik->user->name,
            'user_department' => $sik->department ?? $sik->user->department ?? '-',
            'sik_number' => $sik->sik_number,
            'permit_date' => $sik->permit_date ? Carbon::parse($sik->permit_date)->format('d M Y') : '',
            'departure_time' => $sik->expected_departure_time,
            'return_time' => $sik->expected_return_time,
            'purpose_type' => strtoupper($sik->purpose_type),
            'vehicle_info' => $vehicleInfo,
            'approver_name' => $sik->approver ? $sik->approver->name : 'Atasan',
        ]);
    }

    public function sendSikRejected(ExitPermit $sik, string $reason): bool
    {
        $sik->loadMissing(['user', 'approver']);
        return $this->sendTemplateEmail($sik->user->email, 'sik_rejected', [
            'user_name' => $sik->user->name,
            'sik_number' => $sik->sik_number,
            'permit_date' => $sik->permit_date ? Carbon::parse($sik->permit_date)->format('d M Y') : '',
            'purpose' => $sik->purpose,
            'approver_name' => $sik->approver ? $sik->approver->name : 'Atasan',
            'rejection_reason' => $reason,
        ]);
    }

    // ==========================================
    // KENDARAAN OPERASIONAL (VEHICLE)
    // ==========================================
    public function sendVehicleRequestSubmittedToRequester(VehicleRequest $request): bool
    {
        $request->loadMissing(['user', 'vehicle']);
        return $this->sendTemplateEmail($request->user->email, 'vehicle_request_submitted_requester', [
            'user_name' => $request->user->name,
            'request_number' => $request->request_number,
            'destination' => $request->destination,
            'purpose' => $request->purpose,
            'start_datetime' => $request->start_datetime ? Carbon::parse($request->start_datetime)->format('d M Y H:i') : '',
            'end_datetime' => $request->end_datetime ? Carbon::parse($request->end_datetime)->format('d M Y H:i') : '',
            'preferred_vehicle' => $request->vehicle ? $request->vehicle->name : 'Sesuai Ketersediaan Pool',
        ]);
    }

    public function sendVehicleRequestSubmittedToGa(VehicleRequest $request, string $gaEmail): bool
    {
        $request->loadMissing('user');
        return $this->sendTemplateEmail($gaEmail, 'vehicle_request_submitted_ga', [
            'user_name' => $request->user->name,
            'user_department' => $request->user->department ?? '-',
            'request_number' => $request->request_number,
            'destination' => $request->destination,
            'purpose' => $request->purpose,
            'start_datetime' => $request->start_datetime ? Carbon::parse($request->start_datetime)->format('d M Y H:i') : '',
            'end_datetime' => $request->end_datetime ? Carbon::parse($request->end_datetime)->format('d M Y H:i') : '',
            'notes' => $request->notes ?? '-',
        ]);
    }

    public function sendVehicleRequestApprovedToRequester(VehicleRequest $request): bool
    {
        $request->loadMissing(['user', 'vehicle', 'driver']);
        return $this->sendTemplateEmail($request->user->email, 'vehicle_request_approved_requester', [
            'user_name' => $request->user->name,
            'request_number' => $request->request_number,
            'vehicle_name' => $request->vehicle ? $request->vehicle->name : 'Armada SBA',
            'plate_number' => $request->vehicle ? $request->vehicle->plate_number : '-',
            'driver_name' => $request->driver ? $request->driver->name : 'Lepas Kunci (Tanpa Driver)',
            'driver_phone' => $request->driver ? ($request->driver->phone_number ?? '-') : '-',
            'destination' => $request->destination,
            'start_datetime' => $request->start_datetime ? Carbon::parse($request->start_datetime)->format('d M Y H:i') : '',
            'end_datetime' => $request->end_datetime ? Carbon::parse($request->end_datetime)->format('d M Y H:i') : '',
        ]);
    }

    public function sendVehicleRequestApprovedToDriver(VehicleRequest $request, Driver $driver): bool
    {
        $driverEmail = $driver->user ? $driver->user->email : null;
        if (!$driverEmail) {
            return false;
        }

        $request->loadMissing(['user', 'vehicle']);
        return $this->sendTemplateEmail($driverEmail, 'vehicle_request_approved_driver', [
            'driver_name' => $driver->name,
            'request_number' => $request->request_number,
            'user_name' => $request->user->name,
            'user_department' => $request->user->department ?? '-',
            'user_phone' => '-',
            'destination' => $request->destination,
            'purpose' => $request->purpose,
            'start_datetime' => $request->start_datetime ? Carbon::parse($request->start_datetime)->format('d M Y H:i') : '',
            'end_datetime' => $request->end_datetime ? Carbon::parse($request->end_datetime)->format('d M Y H:i') : '',
            'vehicle_name' => $request->vehicle ? $request->vehicle->name : 'Armada SBA',
            'plate_number' => $request->vehicle ? $request->vehicle->plate_number : '-',
        ]);
    }

    public function sendVehicleRequestRejectedToRequester(VehicleRequest $request, string $reason): bool
    {
        $request->loadMissing('user');
        return $this->sendTemplateEmail($request->user->email, 'vehicle_request_rejected', [
            'user_name' => $request->user->name,
            'request_number' => $request->request_number,
            'rejection_reason' => $reason,
        ]);
    }

    // ==========================================
    // INSPEKSI TOILET
    // ==========================================
    public function sendToiletInspectionSubmittedToInspector(Inspection $inspection): bool
    {
        $inspection->loadMissing(['inspector', 'location', 'results']);
        $totalItems = $inspection->results->count();
        $okItems = $inspection->results->where('is_ok', true)->count();
        $defectItems = $totalItems - $okItems;

        $summary = "{$okItems} item bersih/sesuai";
        if ($defectItems > 0) {
            $summary .= ", {$defectItems} item kotor/rusak";
        }

        return $this->sendTemplateEmail($inspection->inspector->email, 'toilet_inspection_submitted_inspector', [
            'inspector_name' => $inspection->inspector->name,
            'location_name' => $inspection->location ? $inspection->location->name : 'Toilet SBA',
            'building' => $inspection->location->building ?? '-',
            'floor' => $inspection->location->floor ?? '-',
            'inspected_at' => $inspection->inspected_at ? Carbon::parse($inspection->inspected_at)->format('d M Y H:i') : '',
            'status' => strtoupper($inspection->status),
            'summary' => $summary,
            'notes' => $inspection->notes ?? '-',
        ]);
    }

    public function sendToiletInspectionIssueReported(Inspection $inspection, string $recipientEmail, array $issues): bool
    {
        $inspection->loadMissing(['inspector', 'location']);
        $issuesList = !empty($issues) ? implode("\n", array_map(fn($item) => "- " . $item, $issues)) : '- Tidak ada rincian spesifik -';

        return $this->sendTemplateEmail($recipientEmail, 'toilet_inspection_issue_reported', [
            'location_name' => $inspection->location ? $inspection->location->name : 'Toilet SBA',
            'building' => $inspection->location->building ?? '-',
            'floor' => $inspection->location->floor ?? '-',
            'inspector_name' => $inspection->inspector->name,
            'inspected_at' => $inspection->inspected_at ? Carbon::parse($inspection->inspected_at)->format('d M Y H:i') : '',
            'issues_list' => $issuesList,
            'notes' => $inspection->notes ?? '-',
        ]);
    }

    public function sendToiletInspectionStatusUpdated(Inspection $inspection, string $status, ?string $spvNotes, string $spvName): bool
    {
        $inspection->loadMissing(['inspector', 'location']);
        return $this->sendTemplateEmail($inspection->inspector->email, 'toilet_inspection_status_updated', [
            'inspector_name' => $inspection->inspector->name,
            'location_name' => $inspection->location ? $inspection->location->name : 'Toilet SBA',
            'status' => strtoupper($status),
            'spv_name' => $spvName,
            'spv_notes' => $spvNotes ?? '-',
            'actioned_at' => Carbon::now('Asia/Jakarta')->format('d M Y H:i'),
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
