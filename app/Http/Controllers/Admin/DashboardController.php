<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\Facility;
use App\Models\Booking;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with administrative stats.
     */
    public function index(Request $request)
    {
        Booking::syncCompletedBookings();
        $now = Carbon::now('Asia/Jakarta');

        $period = $request->input('period', 'monthly');
        $dateInput = $request->input('date');

        if (empty($dateInput)) {
            if ($period === 'yearly') {
                $dateInput = $now->format('Y');
            } elseif ($period === 'monthly') {
                $dateInput = $now->format('Y-m');
            } else {
                $dateInput = $now->format('Y-m-d');
            }
        }

        try {
            if ($period === 'yearly') {
                $date = Carbon::createFromFormat('Y-m-d', $dateInput . '-01-01', 'Asia/Jakarta');
                $start = $date->copy()->startOfYear();
                $end = $date->copy()->endOfYear();
            } elseif ($period === 'monthly') {
                $date = Carbon::createFromFormat('Y-m-d', $dateInput . '-01', 'Asia/Jakarta');
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();
            } else {
                $date = Carbon::createFromFormat('Y-m-d', $dateInput, 'Asia/Jakarta');
                if ($period === 'weekly') {
                    $start = $date->copy()->startOfWeek();
                    $end = $date->copy()->endOfWeek();
                } else { // daily
                    $start = $date->copy()->startOfDay();
                    $end = $date->copy()->endOfDay();
                }
            }
        } catch (\Exception $e) {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $period = 'monthly';
            $dateInput = $now->format('Y-m');
        }

        // Query bookings in range
        $bookings = Booking::with(['user', 'room'])
            ->whereBetween('booking_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        $totalBookings = $bookings->count();
        $pendingBookings = Booking::where('status', 'pending')->count(); // Global pending backlog count
        $pendingBookingsInPeriod = $bookings->where('status', 'pending')->count();
        $approvedBookings = $bookings->where('status', 'approved')->count();
        $rejectedBookings = $bookings->where('status', 'rejected')->count();
        $cancelledBookings = $bookings->where('status', 'cancelled')->count();
        $completedBookings = $bookings->where('status', 'completed')->count();

        // Rating metrics
        $reviewedBookings = $bookings->where('status', 'completed')->whereNotNull('rating');
        $totalReviews = $reviewedBookings->count();
        $averageRating = $reviewedBookings->count() > 0 ? round($reviewedBookings->avg('rating'), 1) : 0;

        // Room performance (Utilisation and rating)
        $rooms = Room::all();
        $roomPerformance = [];
        foreach ($rooms as $room) {
            $roomBookings = $bookings->where('room_id', $room->id);
            $roomCompletedBookings = $roomBookings->where('status', 'completed')->whereNotNull('rating');
            $avgRoomRating = $roomCompletedBookings->count() > 0 ? round($roomCompletedBookings->avg('rating'), 1) : null;
            
            $roomPerformance[] = [
                'id' => $room->id,
                'name' => $room->name,
                'location' => $room->location,
                'bookings_count' => $roomBookings->count(),
                'reviews_count' => $roomCompletedBookings->count(),
                'average_rating' => $avgRoomRating,
            ];
        }

        // Sort by average rating desc, then bookings count desc
        usort($roomPerformance, function ($a, $b) {
            if ($a['average_rating'] === null && $b['average_rating'] !== null) return 1;
            if ($a['average_rating'] !== null && $b['average_rating'] === null) return -1;
            if ($a['average_rating'] === null && $b['average_rating'] === null) {
                return $b['bookings_count'] <=> $a['bookings_count'];
            }
            if ($a['average_rating'] != $b['average_rating']) {
                return $b['average_rating'] <=> $a['average_rating'];
            }
            return $b['bookings_count'] <=> $a['bookings_count'];
        });

        // Reviews / Feedback list in range
        $reviews = Booking::with(['user', 'room'])
            ->whereNotNull('rating')
            ->whereBetween('booking_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        // Generate trend labels and data for Chart.js
        $trendLabels = [];
        $trendData = [];

        if ($period === 'daily') {
            for ($h = 8; $h <= 18; $h++) {
                $lbl = sprintf('%02d:00', $h);
                $trendLabels[] = $lbl;
                $trendData[$lbl] = 0;
            }
            foreach ($bookings as $b) {
                $startHour = (int) explode(':', $b->start_time)[0];
                if ($startHour >= 8 && $startHour <= 18) {
                    $lbl = sprintf('%02d:00', $startHour);
                    $trendData[$lbl]++;
                }
            }
        } elseif ($period === 'weekly') {
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $dName) {
                $trendLabels[] = $dName;
                $trendData[$dName] = 0;
            }
            foreach ($bookings as $b) {
                $dayName = $b->booking_date->setTimezone('Asia/Jakarta')->format('l');
                if (isset($trendData[$dayName])) {
                    $trendData[$dayName]++;
                }
            }
        } elseif ($period === 'yearly') {
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($months as $idx => $mName) {
                $trendLabels[] = $mName;
                $trendData[$idx + 1] = 0;
            }
            foreach ($bookings as $b) {
                $monthNum = (int) $b->booking_date->setTimezone('Asia/Jakarta')->format('n');
                if (isset($trendData[$monthNum])) {
                    $trendData[$monthNum]++;
                }
            }
            $trendData = array_values($trendData);
        } else { // monthly
            $daysInMonth = $start->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $trendLabels[] = (string)$day;
                $trendData[$day] = 0;
            }
            foreach ($bookings as $b) {
                $dayNum = (int) $b->booking_date->setTimezone('Asia/Jakarta')->format('j');
                if (isset($trendData[$dayNum])) {
                    $trendData[$dayNum]++;
                }
            }
            $trendData = array_values($trendData);
        }

        $stats = [
            'total_bookings' => $totalBookings,
            'pending_bookings' => $pendingBookings,
            'pending_bookings_in_period' => $pendingBookingsInPeriod,
            'approved_bookings' => $approvedBookings,
            'rejected_bookings' => $rejectedBookings,
            'cancelled_bookings' => $cancelledBookings,
            'completed_bookings' => $completedBookings,
            'total_reviews' => $totalReviews,
            'average_rating' => $averageRating,
            'total_rooms' => Room::count(),
            'active_rooms' => Room::where('status', 'active')->count(),
            'total_facilities' => Facility::count(),
        ];

        // 5 latest bookings overall for recent activity
        $recentBookings = Booking::with(['user', 'room'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->take(5)
            ->get();

        // 5 latest reviews in this period
        $recentReviews = $reviews->take(5);

        return view('admin.dashboard.index', compact(
            'stats',
            'roomPerformance',
            'reviews',
            'recentBookings',
            'recentReviews',
            'trendLabels',
            'trendData',
            'period',
            'dateInput',
            'start',
            'end'
        ));
    }

    /**
     * Display the full booking usage history page with multiple filters.
     */
    public function bookingHistory(Request $request)
    {
        Booking::syncCompletedBookings();
        $query = Booking::with(['user', 'room']);

        // 1. Period filter
        $period = $request->input('period');
        $dateInput = $request->input('date');
        $now = Carbon::now('Asia/Jakarta');

        if ($period) {
            if (empty($dateInput)) {
                if ($period === 'yearly') {
                    $dateInput = $now->format('Y');
                } elseif ($period === 'monthly') {
                    $dateInput = $now->format('Y-m');
                } else {
                    $dateInput = $now->format('Y-m-d');
                }
            }

            try {
                if ($period === 'yearly') {
                    $date = Carbon::createFromFormat('Y-m-d', $dateInput . '-01-01', 'Asia/Jakarta');
                    $start = $date->copy()->startOfYear();
                    $end = $date->copy()->endOfYear();
                } elseif ($period === 'monthly') {
                    $date = Carbon::createFromFormat('Y-m-d', $dateInput . '-01', 'Asia/Jakarta');
                    $start = $date->copy()->startOfMonth();
                    $end = $date->copy()->endOfMonth();
                } else {
                    $date = Carbon::createFromFormat('Y-m-d', $dateInput, 'Asia/Jakarta');
                    if ($period === 'weekly') {
                        $start = $date->copy()->startOfWeek();
                        $end = $date->copy()->endOfWeek();
                    } else { // daily
                        $start = $date->copy()->startOfDay();
                        $end = $date->copy()->endOfDay();
                    }
                }
                $query->whereBetween('booking_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
            } catch (\Exception $e) {
                // ignore period date parse error
            }
        }

        // 2. Custom Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('booking_date', [$request->input('start_date'), $request->input('end_date')]);
        }

        // 3. Room filter
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        // 4. Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 5. Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 6. User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $bookings = $query->orderBy('booking_date', 'desc')
                          ->orderBy('start_time', 'desc')
                          ->paginate(15)
                          ->withQueryString();

        $rooms = Room::all();
        $users = \App\Models\User::orderBy('name', 'asc')->get();

        return view('admin.dashboard.booking_history', compact('bookings', 'rooms', 'users', 'period', 'dateInput'));
    }

    /**
     * Display all reviews with pagination and custom filtering.
     */
    public function reviews(Request $request)
    {
        Booking::syncCompletedBookings();
        $query = Booking::with(['user', 'room'])
            ->whereNotNull('rating');

        // 1. Period filter
        $period = $request->input('period');
        $dateInput = $request->input('date');
        $now = Carbon::now('Asia/Jakarta');

        if ($period) {
            if (empty($dateInput)) {
                if ($period === 'yearly') {
                    $dateInput = $now->format('Y');
                } elseif ($period === 'monthly') {
                    $dateInput = $now->format('Y-m');
                } else {
                    $dateInput = $now->format('Y-m-d');
                }
            }

            try {
                if ($period === 'yearly') {
                    $date = Carbon::createFromFormat('Y-m-d', $dateInput . '-01-01', 'Asia/Jakarta');
                    $start = $date->copy()->startOfYear();
                    $end = $date->copy()->endOfYear();
                } elseif ($period === 'monthly') {
                    $date = Carbon::createFromFormat('Y-m-d', $dateInput . '-01', 'Asia/Jakarta');
                    $start = $date->copy()->startOfMonth();
                    $end = $date->copy()->endOfMonth();
                } else {
                    $date = Carbon::createFromFormat('Y-m-d', $dateInput, 'Asia/Jakarta');
                    if ($period === 'weekly') {
                        $start = $date->copy()->startOfWeek();
                        $end = $date->copy()->endOfWeek();
                    } else { // daily
                        $start = $date->copy()->startOfDay();
                        $end = $date->copy()->endOfDay();
                    }
                }
                $query->whereBetween('booking_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
            } catch (\Exception $e) {
                // ignore date parse error
            }
        }

        // 2. Custom Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('booking_date', [$request->input('start_date'), $request->input('end_date')]);
        }

        // 3. Room filter
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        // 4. Rating filter
        if ($request->filled('rating')) {
            $query->where('rating', $request->input('rating'));
        }

        // 5. Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('feedback', 'like', "%{$search}%")
                  ->orWhereHas('user', function($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('room', function($qr) use ($search) {
                      $qr->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Calculate statistics based on the matching filtered query
        $statsQuery = clone $query;
        $totalReviews = $statsQuery->count();
        $averageRating = $statsQuery->avg('rating') ?: 0;
        
        $ratingDistribution = [
            5 => (clone $statsQuery)->where('rating', 5)->count(),
            4 => (clone $statsQuery)->where('rating', 4)->count(),
            3 => (clone $statsQuery)->where('rating', 3)->count(),
            2 => (clone $statsQuery)->where('rating', 2)->count(),
            1 => (clone $statsQuery)->where('rating', 1)->count(),
        ];

        $reviews = $query->orderBy('booking_date', 'desc')
                          ->orderBy('start_time', 'desc')
                          ->paginate(15)
                          ->withQueryString();

        $rooms = Room::all();

        return view('admin.dashboard.reviews', compact(
            'reviews',
            'rooms',
            'period',
            'dateInput',
            'totalReviews',
            'averageRating',
            'ratingDistribution'
        ));
    }

    /**
     * Display aggregated room performance metrics for all rooms.
     */
    public function roomPerformance(Request $request)
    {
        Booking::syncCompletedBookings();
        $now = Carbon::now('Asia/Jakarta');

        $period = $request->input('period', 'monthly');
        $dateInput = $request->input('date');

        if (empty($dateInput)) {
            if ($period === 'yearly') {
                $dateInput = $now->format('Y');
            } elseif ($period === 'monthly') {
                $dateInput = $now->format('Y-m');
            } else {
                $dateInput = $now->format('Y-m-d');
            }
        }

        try {
            if ($period === 'yearly') {
                $date = Carbon::createFromFormat('Y-m-d', $dateInput . '-01-01', 'Asia/Jakarta');
                $start = $date->copy()->startOfYear();
                $end = $date->copy()->endOfYear();
            } elseif ($period === 'monthly') {
                $date = Carbon::createFromFormat('Y-m-d', $dateInput . '-01', 'Asia/Jakarta');
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();
            } else {
                $date = Carbon::createFromFormat('Y-m-d', $dateInput, 'Asia/Jakarta');
                if ($period === 'weekly') {
                    $start = $date->copy()->startOfWeek();
                    $end = $date->copy()->endOfWeek();
                } else { // daily
                    $start = $date->copy()->startOfDay();
                    $end = $date->copy()->endOfDay();
                }
            }
        } catch (\Exception $e) {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $period = 'monthly';
            $dateInput = $now->format('Y-m');
        }

        // Query bookings in range
        $bookings = Booking::whereBetween('booking_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])->get();
        $totalBookings = $bookings->count();

        // Room performance
        $rooms = Room::all();
        $roomPerformance = [];
        foreach ($rooms as $room) {
            $roomBookings = $bookings->where('room_id', $room->id);
            $roomCompletedBookings = $roomBookings->where('status', 'completed')->whereNotNull('rating');
            $avgRoomRating = $roomCompletedBookings->count() > 0 ? round($roomCompletedBookings->avg('rating'), 1) : null;
            
            $roomPerformance[] = [
                'id' => $room->id,
                'name' => $room->name,
                'location' => $room->location,
                'bookings_count' => $roomBookings->count(),
                'reviews_count' => $roomCompletedBookings->count(),
                'average_rating' => $avgRoomRating,
            ];
        }

        // Sort by average rating desc, then bookings count desc
        usort($roomPerformance, function ($a, $b) {
            if ($a['average_rating'] === null && $b['average_rating'] !== null) return 1;
            if ($a['average_rating'] !== null && $b['average_rating'] === null) return -1;
            if ($a['average_rating'] === null && $b['average_rating'] === null) {
                return $b['bookings_count'] <=> $a['bookings_count'];
            }
            if ($a['average_rating'] != $b['average_rating']) {
                return $b['average_rating'] <=> $a['average_rating'];
            }
            return $b['bookings_count'] <=> $a['bookings_count'];
        });

        return view('admin.dashboard.room_performance', compact('roomPerformance', 'period', 'dateInput', 'start', 'end', 'totalBookings'));
    }
}
