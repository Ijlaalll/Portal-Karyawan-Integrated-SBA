<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Exception;

class BookingService
{
    public function __construct(
        protected AvailabilityService $availabilityService
    ) {}

    /**
     * Create a new booking request within a database transaction.
     */
    public function createBooking(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {
            $meetingType = $data['meeting_type'] ?? 'offline';

            $room = null;
            if ($meetingType === 'offline' || $meetingType === 'hybrid') {
                if (empty($data['room_id'])) {
                    throw new Exception('Ruangan harus dipilih untuk pertemuan offline/hybrid.');
                }
                
                // Lock room row for concurrency protection
                $room = Room::where('id', $data['room_id'])->lockForUpdate()->firstOrFail();

                if (!$room->isActive()) {
                    throw new Exception('Ruangan yang dipilih tidak aktif dan tidak dapat dipesan.');
                }

                // Check capacity
                $participantCount = (int) ($data['participant_count'] ?? 1);
                if ($room->capacity < $participantCount) {
                    throw new Exception("Kapasitas ruangan ({$room->capacity}) tidak mencukupi untuk jumlah peserta ({$participantCount}).");
                }
            }

            // Business rule: Start time must be before end time
            if ($data['start_time'] >= $data['end_time']) {
                throw new Exception('Waktu mulai harus sebelum waktu selesai.');
            }

            // Business rule: Booking date/time cannot be in the past
            $today = date('Y-m-d');
            if ($data['booking_date'] < $today) {
                throw new Exception('Tanggal pemesanan tidak boleh di masa lalu.');
            }
            if ($data['booking_date'] === $today) {
                $currentTime = date('H:i:s');
                $startTimeFormatted = date('H:i:s', strtotime($data['start_time']));
                if ($startTimeFormatted < $currentTime) {
                    throw new Exception('Waktu mulai pemesanan tidak boleh di masa lalu.');
                }
            }

            // Business rule: Operating hours = 08:00 - 17:00
            $startTimeOnly = date('H:i', strtotime($data['start_time']));
            $endTimeOnly = date('H:i', strtotime($data['end_time']));
            if ($startTimeOnly < '08:00' || $endTimeOnly > '17:00') {
                throw new Exception('Pemesanan harus dalam jam operasional (08:00–17:00).');
            }

            // Business rule: Minimum duration = 30 minutes (1800 seconds)
            $start = strtotime($data['start_time']);
            $end = strtotime($data['end_time']);
            if (($end - $start) < 1800) {
                throw new Exception('Durasi pemesanan minimal adalah 30 menit.');
            }

            if ($meetingType === 'offline' || $meetingType === 'hybrid') {
                // Stage 3: Final availability check
                $isAvailable = $this->availabilityService->isRoomAvailable(
                    $room->id,
                    $data['booking_date'],
                    $data['start_time'],
                    $data['end_time']
                );

                if (!$isAvailable) {
                    throw new Exception('Ruangan tidak lagi tersedia untuk slot waktu yang dipilih.');
                }
            }

            // Build additional requirements
            $requirements = [];
            if (($meetingType === 'offline' || $meetingType === 'hybrid') && isset($data['additional_requirements'])) {
                $requirements = $data['additional_requirements'];
                if (in_array('Lainnya', $requirements) && !empty($data['additional_requirements_other'])) {
                    $requirements[] = 'Lainnya: ' . $data['additional_requirements_other'];
                }
            }

            $booking = Booking::create([
                'user_id' => $user->id,
                'room_id' => $room ? $room->id : null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'status' => 'approved', // Bypasses Admin approval, approved immediately
                'meeting_type' => $meetingType,
                'participant_count' => $data['participant_count'] ?? 1,
                'additional_requirements' => !empty($requirements) ? $requirements : null,
                'online_platform' => $meetingType === 'online' ? 'Microsoft Teams' : null,
            ]);

            $this->triggerTaskCreation($booking);

            // Record Cost Center transaction if final/approved
            app(\App\Services\CostCenterTransactionService::class)->recordRoomBooking($booking);

            // Send email notifications
            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendReservationCreated($booking);
            $emailService->sendReservationApproved($booking);

            return $booking;
        });
    }

    /**
     * Automatically trigger task creation and notifications.
     */
    public function triggerTaskCreation(Booking $booking): void
    {
        if ($booking->meeting_type === 'offline') {
            $booking->update([
                'ga_status' => 'pending',
            ]);
            $gaUsers = User::where('role', 'ga')->get();
            foreach ($gaUsers as $ga) {
                Notification::send(
                    $ga->id,
                    'Tugas Persiapan Ruangan Baru',
                    "Pemesanan offline '{$booking->title}' siap disiapkan. Silakan siapkan ruangan {$booking->room->name}.",
                    route('ga.preparations')
                );
            }
        } elseif ($booking->meeting_type === 'hybrid') {
            $booking->update([
                'ga_status' => 'pending',
                'it_status' => 'pending',
            ]);
            
            // Notify GA
            $gaUsers = User::where('role', 'ga')->get();
            foreach ($gaUsers as $ga) {
                Notification::send(
                    $ga->id,
                    'Tugas Persiapan Ruangan Baru',
                    "Pemesanan hybrid '{$booking->title}' siap disiapkan. Silakan siapkan ruangan {$booking->room->name}.",
                    route('ga.preparations')
                );
            }
            // Notify IT
            $itUsers = User::where('role', 'it')->get();
            foreach ($itUsers as $it) {
                Notification::send(
                    $it->id,
                    'Tugas Persiapan Meeting Hybrid Baru',
                    "Pemesanan hybrid '{$booking->title}' membutuhkan persiapan teknis. Silakan siapkan peralatan hybrid.",
                    route('it.preparations')
                );
            }
        }
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking, User $user): Booking
    {
        if ($booking->user_id !== $user->id) {
            throw new Exception('Anda tidak berwenang untuk membatalkan pemesanan ini.');
        }

        if (!in_array($booking->status, ['pending', 'approved'])) {
            throw new Exception('Pemesanan ini tidak dapat dibatalkan dalam statusnya saat ini.');
        }

        $startDateTime = \Carbon\Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time, 'Asia/Jakarta');
        $now = \Carbon\Carbon::now('Asia/Jakarta');

        if ($now->greaterThanOrEqualTo($startDateTime)) {
            throw new Exception('Tidak dapat membatalkan pemesanan yang sudah dimulai.');
        }

        // Preparation lock — backend enforcement (defence-in-depth, mirrors BookingPolicy::cancel)
        if ($booking->status === 'approved') {
            if ($booking->meeting_type === 'offline') {
                if ($booking->ga_status !== null && $booking->ga_status !== 'pending') {
                    throw new Exception('Pemesanan tidak dapat dibatalkan karena Tim GA sudah memulai persiapan.');
                }
                if ($booking->ga_status === null && $booking->preparation_status !== null
                    && $booking->preparation_status !== 'Menunggu Persiapan') {
                    throw new Exception('Pemesanan tidak dapat dibatalkan karena persiapan sudah dimulai.');
                }
            } elseif ($booking->meeting_type === 'online') {
                throw new Exception('Pemesanan online yang sudah disetujui tidak dapat dibatalkan oleh user.');
            } elseif ($booking->meeting_type === 'hybrid') {
                if ($booking->ga_status !== null && $booking->ga_status !== 'pending') {
                    throw new Exception('Pemesanan tidak dapat dibatalkan karena Tim GA sudah memulai persiapan.');
                }
                if ($booking->it_status !== null && $booking->it_status !== 'pending') {
                    throw new Exception('Pemesanan tidak dapat dibatalkan karena Tim IT sudah memulai persiapan.');
                }
            }
        }

        // User self-cancel: popup not needed (user already knows), acknowledged=true
        $booking->update([
            'status'                          => 'cancelled',
            'cancellation_reason'             => 'USER_CANCEL',
            'cancellation_note'               => 'Dibatalkan oleh pengguna.',
            'cancelled_by'                    => $user->id,
            'cancelled_at'                    => \Carbon\Carbon::now('Asia/Jakarta'),
            'cancellation_popup_acknowledged' => true,
        ]);

        // Reverse Cost Center transaction if applicable
        app(\App\Services\CostCenterTransactionService::class)->reverseRoomBooking($booking);

        // Send email notification for cancellation
        $emailService = app(\App\Services\EmailService::class);
        $emailService->sendReservationCancelled($booking);

        return $booking;
    }
}

