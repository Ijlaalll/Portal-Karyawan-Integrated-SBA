<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;

class PreparationController extends Controller
{
    /**
     * Display GA physical preparation tasks.
     */
    public function gaIndex(Request $request)
    {
        // Enforce GA role access inline
        if (!auth()->user()->isGa()) {
            abort(403, 'Akses tidak sah.');
        }

        Booking::syncCompletedBookings();

        $status = $request->query('status', 'active'); // 'active' or 'history'

        if ($status === 'active') {
            $query = Booking::where(function ($q) {
                // New flow (GA needs offline & hybrid)
                $q->whereNotNull('ga_status')
                  ->where('status', 'approved')
                  ->whereIn('ga_status', ['pending', 'in_progress']);
            })->orWhere(function ($q) {
                // Legacy flow
                $q->whereNull('ga_status')
                  ->where('meeting_type', 'offline')
                  ->where('status', 'approved')
                  ->whereIn('preparation_status', ['Menunggu Persiapan', 'Sedang Dipersiapkan']);
            });
        } else {
            // History
            $query = Booking::where(function ($q) {
                $q->whereNotNull('ga_status')
                  ->where(function ($sub) {
                      $sub->whereIn('ga_status', ['ready', 'not_ready'])
                          ->orWhereIn('status', ['completed', 'cancelled']);
                  });
            })->orWhere(function ($q) {
                $q->whereNull('ga_status')
                  ->where('meeting_type', 'offline')
                  ->where(function ($sub) {
                      $sub->whereIn('preparation_status', ['Siap', 'Tidak Siap'])
                          ->orWhereIn('status', ['completed', 'cancelled']);
                  });
            });
        }

        $bookings = $query->with(['user', 'room'])
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate(10);

        // GA Statistics (Offline + Hybrid)
        $pending = Booking::whereIn('meeting_type', ['offline', 'hybrid'])->where('status', 'approved')
            ->where(function($q) {
                $q->where('ga_status', 'pending')
                  ->orWhere(function($sub) {
                      $sub->whereNull('ga_status')->where('preparation_status', 'Menunggu Persiapan');
                  });
            })->count();

        $inProgress = Booking::whereIn('meeting_type', ['offline', 'hybrid'])->where('status', 'approved')
            ->where(function($q) {
                $q->where('ga_status', 'in_progress')
                  ->orWhere(function($sub) {
                      $sub->whereNull('ga_status')->where('preparation_status', 'Sedang Dipersiapkan');
                  });
            })->count();

        $ready = Booking::whereIn('meeting_type', ['offline', 'hybrid'])->where('status', 'approved')
            ->where(function($q) {
                $q->where('ga_status', 'ready')
                  ->orWhere(function($sub) {
                      $sub->whereNull('ga_status')->where('preparation_status', 'Siap');
                  });
            })->count();

        $notReady = Booking::whereIn('meeting_type', ['offline', 'hybrid'])
            ->where(function($q) {
                $q->where('ga_status', 'not_ready')
                  ->orWhere(function($sub) {
                      $sub->whereNull('ga_status')->where('preparation_status', 'Tidak Siap');
                  });
            })->count();

        $completed = Booking::whereIn('meeting_type', ['offline', 'hybrid'])->where('status', 'completed')->count();

        $stats = [
            'total' => $pending + $inProgress + $ready + $notReady + $completed,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'ready' => $ready,
            'not_ready' => $notReady,
            'completed' => $completed,
        ];

        return view('preparations.ga', compact('bookings', 'status', 'stats'));
    }

    /**
     * Display IT online/hybrid meeting preparation tasks.
     */
    public function itIndex(Request $request)
    {
        // Enforce IT role access inline
        if (!auth()->user()->isIt()) {
            abort(403, 'Akses tidak sah.');
        }

        Booking::syncCompletedBookings();

        $status = $request->query('status', 'active'); // 'active' or 'history'

        if ($status === 'active') {
            $query = Booking::where(function ($q) {
                // New flow (IT needs hybrid only)
                $q->whereNotNull('it_status')
                  ->where('status', 'approved')
                  ->whereIn('it_status', ['pending', 'in_progress']);
            })->orWhere(function ($q) {
                // Legacy flow
                $q->whereNull('it_status')
                  ->where('meeting_type', 'online')
                  ->where('status', 'approved')
                  ->whereIn('preparation_status', ['Menunggu Persiapan', 'Sedang Dipersiapkan']);
            });
        } else {
            // History
            $query = Booking::where(function ($q) {
                $q->whereNotNull('it_status')
                  ->where(function ($sub) {
                      $sub->whereIn('it_status', ['ready', 'not_ready'])
                          ->orWhereIn('status', ['completed', 'cancelled']);
                  });
            })->orWhere(function ($q) {
                $q->whereNull('it_status')
                  ->where('meeting_type', 'online')
                  ->where(function ($sub) {
                      $sub->whereIn('preparation_status', ['Siap', 'Tidak Siap'])
                          ->orWhereIn('status', ['completed', 'cancelled']);
                  });
            });
        }

        $bookings = $query->with(['user', 'room'])
            ->orderBy('booking_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate(10);

        // IT Statistics (Hybrid + Legacy Online)
        $pending = Booking::where('status', 'approved')
            ->where(function($q) {
                $q->where('meeting_type', 'hybrid')->where('it_status', 'pending')
                  ->orWhere(function($sub) {
                      $sub->where('meeting_type', 'online')->whereNull('it_status')->where('preparation_status', 'Menunggu Persiapan');
                  });
            })->count();

        $inProgress = Booking::where('status', 'approved')
            ->where(function($q) {
                $q->where('meeting_type', 'hybrid')->where('it_status', 'in_progress')
                  ->orWhere(function($sub) {
                      $sub->where('meeting_type', 'online')->whereNull('it_status')->where('preparation_status', 'Sedang Dipersiapkan');
                  });
            })->count();

        $ready = Booking::where('status', 'approved')
            ->where(function($q) {
                $q->where('meeting_type', 'hybrid')->where('it_status', 'ready')
                  ->orWhere(function($sub) {
                      $sub->where('meeting_type', 'online')->whereNull('it_status')->where('preparation_status', 'Siap');
                  });
            })->count();

        $notReady = Booking::where(function($q) {
            $q->where('meeting_type', 'hybrid')->where('it_status', 'not_ready')
              ->orWhere(function($sub) {
                  $sub->where('meeting_type', 'online')->whereNull('it_status')->where('preparation_status', 'Tidak Siap');
              });
        })->count();

        $completed = Booking::where(function($q) {
            $q->where('meeting_type', 'hybrid')
              ->orWhere(function($sub) {
                  $sub->where('meeting_type', 'online')->whereNull('it_status');
              });
        })->where('status', 'completed')->count();

        $stats = [
            'total' => $pending + $inProgress + $ready + $notReady + $completed,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'ready' => $ready,
            'not_ready' => $notReady,
            'completed' => $completed,
        ];

        return view('preparations.it', compact('bookings', 'status', 'stats'));
    }

    /**
     * Update the preparation status of a booking task.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $user = auth()->user();

        // Enforce role-matching boundaries inline
        if ($user->isGa()) {
            if ($booking->ga_status === null && $booking->meeting_type !== 'offline') {
                abort(403, 'Tindakan tidak sah. GA hanya dapat mengelola pertemuan fisik/hybrid.');
            }
        } elseif ($user->isIt()) {
            if ($booking->it_status === null && $booking->meeting_type !== 'online') {
                abort(403, 'Tindakan tidak sah. IT hanya dapat mengelola pertemuan hybrid/online.');
            }
        } else {
            abort(403, 'Akses tidak sah.');
        }

        $prepStatus = $request->input('preparation_status');
        $isNotReady = $prepStatus === 'not_ready';

        // Validate preparation status and cancellation_note (if not_ready)
        $request->validate([
            'preparation_status' => ['required', 'string', 'in:Menunggu Persiapan,Sedang Dipersiapkan,Siap,pending,in_progress,ready,not_ready'],
            'cancellation_note' => [
                $isNotReady ? 'required' : 'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'cancellation_note.required'   => 'Alasan pembatalan wajib diisi saat memilih Tidak Siap.',
        ]);

        $updateData = [];

        if ($user->isGa()) {
            if ($booking->ga_status !== null) {
                $updateData['ga_status'] = $prepStatus;
                $updateData['prepared_by_ga'] = $user->id;
            } else {
                $updateData['preparation_status'] = $prepStatus;
            }
        } elseif ($user->isIt()) {
            if ($booking->it_status !== null) {
                $updateData['it_status'] = $prepStatus;
                $updateData['prepared_by_it'] = $user->id;
            } else {
                $updateData['preparation_status'] = $prepStatus;
            }
        }

        $booking->update($updateData);

        // Refresh the booking status/attributes after save
        $booking->refresh();

        if ($booking->status !== 'cancelled') {
            $shouldCancel = false;
            $reason = '';

            if ($booking->meeting_type === 'offline') {
                if ($booking->ga_status !== null) {
                    if ($booking->ga_status === 'not_ready') {
                        $shouldCancel = true;
                        $reason = 'GA_FAILURE';
                    }
                } else {
                    if ($booking->preparation_status === 'not_ready') {
                        $shouldCancel = true;
                        $reason = 'GA_FAILURE';
                    }
                }
            } elseif ($booking->meeting_type === 'online') {
                if ($booking->it_status !== null) {
                    if ($booking->it_status === 'not_ready') {
                        $shouldCancel = true;
                        $reason = 'IT_FAILURE';
                    }
                } else {
                    if ($booking->preparation_status === 'not_ready') {
                        $shouldCancel = true;
                        $reason = 'IT_FAILURE';
                    }
                }
            } elseif ($booking->meeting_type === 'hybrid') {
                $gaNotReady = $booking->ga_status === 'not_ready';
                $itNotReady = $booking->it_status === 'not_ready';

                if ($gaNotReady && $itNotReady) {
                    $shouldCancel = true;
                    $reason = 'BOTH_FAILURE';
                } elseif ($gaNotReady) {
                    $shouldCancel = true;
                    $reason = 'GA_FAILURE';
                } elseif ($itNotReady) {
                    $shouldCancel = true;
                    $reason = 'IT_FAILURE';
                }
            }

            if ($shouldCancel) {
                $booking->update([
                    'status'                          => 'cancelled',
                    'cancellation_reason'             => $reason,
                    'cancellation_note'               => $request->input('cancellation_note'),
                    'cancelled_by'                    => $user->id,
                    'cancelled_at'                    => \Carbon\Carbon::now('Asia/Jakarta'),
                    'cancellation_popup_acknowledged' => false,
                ]);

                // Send email notification for rejection (cancelled by GA/IT)
                $emailService = app(\App\Services\EmailService::class);
                $emailService->sendReservationRejected($booking);

                // Notify user about the cancellation due to preparation failure
                $message = '';
                switch ($reason) {
                    case 'GA_FAILURE':
                        $message = "Booking '{$booking->title}' dibatalkan karena persiapan ruangan oleh Tim GA tidak siap.";
                        break;
                    case 'IT_FAILURE':
                        $message = "Booking '{$booking->title}' dibatalkan karena persiapan teknis oleh Tim IT tidak siap.";
                        break;
                    case 'BOTH_FAILURE':
                        $message = "Booking '{$booking->title}' dibatalkan karena persiapan Tim GA dan Tim IT tidak siap.";
                        break;
                }

                if ($message) {
                    Notification::send(
                        $booking->user_id,
                        'Booking Dibatalkan',
                        $message,
                        route('bookings.show', $booking)
                    );
                }
            }
        }

        // Notify user when preparation is complete (status becomes 'Siap' or 'ready' and booking is not cancelled)
        if (($prepStatus === 'Siap' || $prepStatus === 'ready') && $booking->status !== 'cancelled') {
            if ($user->isGa()) {
                Notification::send(
                    $booking->user_id,
                    'Ruangan Siap Digunakan',
                    "Ruangan '{$booking->room->name}' untuk meeting '{$booking->title}' siap digunakan.",
                    route('bookings.show', $booking)
                );
            } elseif ($user->isIt()) {
                if ($booking->meeting_type === 'hybrid') {
                    Notification::send(
                        $booking->user_id,
                        'Meeting Hybrid Siap',
                        "Persiapan teknis hybrid untuk meeting '{$booking->title}' siap.",
                        route('bookings.show', $booking)
                    );
                } else {
                    Notification::send(
                        $booking->user_id,
                        'Meeting Online Siap',
                        "Meeting online '{$booking->title}' siap. Silakan bergabung menggunakan Microsoft Teams.",
                        route('bookings.show', $booking)
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Status persiapan berhasil diperbarui.');
    }
}
