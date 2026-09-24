<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Booking;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Booking::syncCompletedBookings();
    app(\App\Services\VehicleRequestService::class)->autoCompleteExpiredRequests();

    // Send meeting reminders
    $notifSetting = \App\Models\EmailNotificationSetting::where('event_key', 'pengingat_meeting')->first();
    if ($notifSetting && $notifSetting->is_active) {
        $reminderMinutes = $notifSetting->reminder_time;
        
        // Find bookings starting in exactly $reminderMinutes (using a minute window)
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $targetTimeStart = $now->copy()->addMinutes($reminderMinutes)->startOfMinute();
        $targetTimeEnd = $now->copy()->addMinutes($reminderMinutes)->endOfMinute();

        $bookings = Booking::where('status', 'approved')
            ->whereDate('booking_date', $targetTimeStart->format('Y-m-d'))
            ->whereBetween('start_time', [
                $targetTimeStart->format('H:i:s'),
                $targetTimeEnd->format('H:i:s')
            ])
            ->get();

        $emailService = app(\App\Services\EmailService::class);
        foreach ($bookings as $booking) {
            // Check if reminder was already sent
            $alreadySent = \App\Models\EmailLog::where('email_type', 'pengingat_meeting')
                ->where('recipient', $booking->user->email)
                ->where('subject', 'like', "%#{$booking->id}%")
                ->exists();

            if (!$alreadySent) {
                $emailService->sendMeetingReminder($booking);
            }
        }
    }
})->everyMinute();

Artisan::command('vehicles:complete-expired', function () {
    $count = app(\App\Services\VehicleRequestService::class)->autoCompleteExpiredRequests();
    $this->info("Completed {$count} expired vehicle requests.");
})->purpose('Auto-complete approved vehicle requests whose end_datetime has elapsed');

