<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'approved_by',
        'title',
        'description',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'approved_at',
        'rejection_reason',
        'rating',
        'feedback',
        'meeting_type',
        'participant_count',
        'additional_requirements',
        'online_platform',
        'online_meeting_link',
        'preparation_status',
        'ready_popup_acknowledged',
        'active_popup_acknowledged',
        'ga_status',
        'it_status',
        'prepared_by_ga',
        'prepared_by_it',
        'ga_ready_popup_acknowledged',
        'it_ready_popup_acknowledged',
        'cancellation_reason',
        'cancellation_note',
        'cancelled_by',
        'cancelled_at',
        'cancellation_popup_acknowledged',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date:Y-m-d',
            'approved_at' => 'datetime',
            'additional_requirements' => 'array',
            'ready_popup_acknowledged' => 'boolean',
            'active_popup_acknowledged' => 'boolean',
            'ga_ready_popup_acknowledged' => 'boolean',
            'it_ready_popup_acknowledged' => 'boolean',
            'cancellation_popup_acknowledged' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    public function setBookingDateAttribute($value): void
    {
        if ($value instanceof \DateTimeInterface) {
            $this->attributes['booking_date'] = $value->format('Y-m-d');
        } else {
            $this->attributes['booking_date'] = $value;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function preparedByGa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_ga');
    }

    public function preparedByIt(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_it');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getOverallStatus(): string
    {
        if ($this->status === 'cancelled') {
            return 'Dibatalkan';
        }
        if ($this->status === 'rejected') {
            return 'Ditolak';
        }
        if ($this->status === 'completed') {
            return 'Selesai';
        }
        if ($this->status === 'pending') {
            return 'Menunggu';
        }

        if ($this->meeting_type === 'offline') {
            switch ($this->ga_status) {
                case 'in_progress':
                    return 'GA Sedang Menyiapkan Ruangan';
                case 'ready':
                    return 'Ruangan Siap';
                case 'not_ready':
                    return 'GA belum siap';
                case 'pending':
                default:
                    return 'Persiapan GA Menunggu';
            }
        } elseif ($this->meeting_type === 'hybrid') {
            $gaNotReady = $this->ga_status === 'not_ready';
            $itNotReady = $this->it_status === 'not_ready';
            
            if ($gaNotReady && $itNotReady) {
                return 'GA dan IT belum siap';
            }
            if ($gaNotReady) {
                return 'GA belum siap';
            }
            if ($itNotReady) {
                return 'IT belum siap';
            }

            if ($this->ga_status === 'ready' && $this->it_status === 'ready') {
                return 'Meeting Hybrid Siap Digunakan';
            }
            if ($this->ga_status === 'ready' && $this->it_status === 'pending') {
                return 'Menunggu Persiapan IT';
            }
            if ($this->ga_status === 'ready' && $this->it_status === 'in_progress') {
                return 'Persiapan Hybrid Berlangsung';
            }
            if ($this->ga_status === 'in_progress' || $this->it_status === 'in_progress') {
                return 'Persiapan Hybrid Berlangsung';
            }
            return 'Persiapan Hybrid Menunggu';
        }

        return 'Disetujui';
    }

    public function isBlocking(): bool
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    public static function syncCompletedBookings(): void
    {
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $today = $now->format('Y-m-d');
        $time = $now->format('H:i:s');

        // Query bookings with status = approved that reached or passed end_time
        $bookings = self::where('status', 'approved')
            ->where(function ($query) use ($today, $time) {
                $query->whereDate('booking_date', '<', $today)
                    ->orWhere(function ($q) use ($today, $time) {
                        $q->whereDate('booking_date', '=', $today)
                            ->whereColumn('end_time', '>=', 'start_time')
                            ->where('end_time', '<=', $time);
                    });
            })
            ->get();

        foreach ($bookings as $booking) {
            // If all preparation fields are null, this booking is not waiting for preparation tasks (e.g. legacy/test).
            // It should bypass cancellation and transition directly to completed status.
            if ($booking->ga_status === null && $booking->it_status === null && $booking->preparation_status === null) {
                $booking->update(['status' => 'completed']);
                continue;
            }

            $shouldCancel = false;
            $reason = '';

            if ($booking->meeting_type === 'offline') {
                if ($booking->ga_status !== null) {
                    if ($booking->ga_status !== 'ready') {
                        $shouldCancel = true;
                        $reason = 'GA_TIMEOUT';
                    }
                } else {
                    if ($booking->preparation_status !== 'Siap') {
                        $shouldCancel = true;
                        $reason = 'GA_TIMEOUT';
                    }
                }
            } elseif ($booking->meeting_type === 'online') {
                if ($booking->it_status !== null) {
                    if ($booking->it_status !== 'ready') {
                        $shouldCancel = true;
                        $reason = 'IT_TIMEOUT';
                    }
                } else {
                    if ($booking->preparation_status !== 'Siap') {
                        $shouldCancel = true;
                        $reason = 'IT_TIMEOUT';
                    }
                }
            } elseif ($booking->meeting_type === 'hybrid') {
                $gaReady = $booking->ga_status === 'ready';
                $itReady = $booking->it_status === 'ready';

                if (!$gaReady && !$itReady) {
                    $shouldCancel = true;
                    $reason = 'BOTH_TIMEOUT';
                } elseif (!$gaReady) {
                    $shouldCancel = true;
                    $reason = 'GA_TIMEOUT';
                } elseif (!$itReady) {
                    $shouldCancel = true;
                    $reason = 'IT_TIMEOUT';
                }
            }

            if ($shouldCancel) {
                // Map reason to a human-readable note for the cancellation record
                $note = '';
                switch ($reason) {
                    case 'GA_TIMEOUT':
                        $note = 'Persiapan ruangan oleh Tim GA belum selesai sampai batas waktu meeting berakhir.';
                        break;
                    case 'IT_TIMEOUT':
                        $note = 'Persiapan teknis oleh Tim IT belum selesai sampai batas waktu meeting berakhir.';
                        break;
                    case 'BOTH_TIMEOUT':
                        $note = 'Persiapan oleh Tim GA dan Tim IT belum selesai sampai batas waktu meeting berakhir.';
                        break;
                }

                $booking->update([
                    'status'                          => 'cancelled',
                    'cancellation_reason'             => $reason,
                    'cancellation_note'               => $note,
                    'cancelled_by'                    => null,  // automated — no individual user responsible
                    'cancelled_at'                    => \Carbon\Carbon::now('Asia/Jakarta'),
                    'cancellation_popup_acknowledged' => false,
                ]);

                // Reverse Cost Center transaction if applicable
                app(\App\Services\CostCenterTransactionService::class)->reverseRoomBooking($booking);

                // Map reason to notification message
                $message = '';
                switch ($reason) {
                    case 'GA_TIMEOUT':
                        $message = "Booking '{$booking->title}' dibatalkan karena persiapan ruangan oleh Tim GA belum selesai sampai batas waktu meeting.";
                        break;
                    case 'IT_TIMEOUT':
                        $message = "Booking '{$booking->title}' dibatalkan karena persiapan teknis oleh Tim IT belum selesai sampai batas waktu meeting.";
                        break;
                    case 'BOTH_TIMEOUT':
                        $message = "Booking '{$booking->title}' dibatalkan karena persiapan Tim GA dan Tim IT belum selesai sampai batas waktu meeting.";
                        break;
                }

                if ($message) {
                    \App\Models\Notification::send(
                        $booking->user_id,
                        'Booking Dibatalkan',
                        $message,
                        route('bookings.show', $booking)
                    );
                }
            } else {
                // If everything was ready, transition to completed
                $booking->update(['status' => 'completed']);
            }
        }
    }
}
