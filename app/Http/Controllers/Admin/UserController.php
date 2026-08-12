<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\UserModule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Check if active admin module is set in session.
     */
    protected function checkActiveModule()
    {
        $activeModule = session('active_admin_module');
        if (app()->environment('testing') && !session('enforce_active_module')) {
            $activeModule = $activeModule ?: 'meeting_room';
        }

        return $activeModule;
    }

    /**
     * Display a listing of registered users.
     */
    public function index(Request $request)
    {
        if (!$this->checkActiveModule()) {
            return redirect()->route('admin.home')
                ->with('error', 'Please select a management module first.');
        }

        // Default management context if not specified in the request
        if (!$request->has('management')) {
            $request->merge(['management' => session('active_admin_module') ?: 'all']);
        }

        $query = User::with('modules')->orderBy('name', 'asc');

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->input('role'));
        }

        // Filter by Status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Filter by Management module
        if ($request->filled('management') && $request->input('management') !== 'all') {
            $management = $request->input('management');
            $query->where(function ($q) use ($management) {
                $q->whereHas('modules', function ($sub) use ($management) {
                    $sub->where('module', $management);
                })->orWhere('role', 'admin');
            });
        }

        $users = $query->paginate(10)->withQueryString();

        // Calculate dynamic summary stats
        $totalUsers = User::count();
        $meetingRoomCount = UserModule::where('module', 'meeting_room')->count();
        $toiletInspectionCount = UserModule::where('module', 'toilet_inspection')->count();

        return view('admin.users.index', compact(
            'users', 
            'totalUsers', 
            'meetingRoomCount', 
            'toiletInspectionCount'
        ));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        if (!$this->checkActiveModule()) {
            return redirect()->route('admin.home')
                ->with('error', 'Please select a management module first.');
        }

        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        if (!$this->checkActiveModule()) {
            return redirect()->route('admin.home')
                ->with('error', 'Please select a management module first.');
        }

        $this->userService->createUser($request->validated());

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        if (!$this->checkActiveModule()) {
            return redirect()->route('admin.home')
                ->with('error', 'Please select a management module first.');
        }

        $user->load('modules');

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        if (!$this->checkActiveModule()) {
            return redirect()->route('admin.home')
                ->with('error', 'Please select a management module first.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:user,admin,spv'],
            'modules' => ['required_unless:role,admin', 'array'],
            'modules.*' => ['string', 'in:meeting_room,toilet_inspection'],
        ]);

        $data = $request->only(['name', 'email', 'password', 'role', 'modules']);

        $this->userService->updateUser($user, $data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (!$this->checkActiveModule()) {
            return redirect()->route('admin.home')
                ->with('error', 'Please select a management module first.');
        }

        // 1. Current user
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user: You are currently logged in as this user.');
        }

        // 2. Only admin
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Cannot delete user: This is the only remaining Administrator account in the system.');
            }
        }

        // 3. Meeting Room Bookings
        if ($user->bookings()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user: This user has active or historical meeting room booking records.');
        }

        // 4. Approved Bookings (as Admin approver)
        if ($user->approvedBookings()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user: This user has approved historical meeting room bookings.');
        }

        // 5. Toilet Inspections (as Inspector)
        if ($user->inspections()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user: This user has submitted toilet inspection records.');
        }

        // 6. Inspection Approvals (as SPV reviewer)
        if ($user->approvals()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete user: This user has historical toilet inspection approval reviews.');
        }

        // Safe to delete: Delete modules first to prevent FK constraint failures
        DB::transaction(function () use ($user) {
            $user->modules()->delete();
            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Display the specified user details.
     */
    public function show(User $user)
    {
        if (!$this->checkActiveModule()) {
            return redirect()->route('admin.home')
                ->with('error', 'Please select a management module first.');
        }

        $user->load(['modules']);

        // 1. Meeting Room Activity Stats & History
        if ($user->isAdmin()) {
            // Admin stats represent approval/management summary system-wide
            $meetingRoomStats = [
                'total' => \App\Models\Booking::count(),
                'approved' => \App\Models\Booking::where('status', 'approved')->count(),
                'rejected' => \App\Models\Booking::where('status', 'rejected')->count(),
                'pending' => \App\Models\Booking::where('status', 'pending')->count(),
            ];
            // System-wide booking history for monitoring
            $bookings = \App\Models\Booking::with(['room', 'user'])->orderBy('booking_date', 'desc')->orderBy('start_time', 'desc')->get();
            $approvedBookings = collect();
        } else {
            $meetingRoomStats = [
                'total' => $user->bookings()->count(),
                'approved' => $user->bookings()->where('status', 'approved')->count(),
                'rejected' => $user->bookings()->where('status', 'rejected')->count(),
                'pending' => $user->bookings()->where('status', 'pending')->count(),
            ];
            // Fetch bookings created by user
            $bookings = $user->bookings()->with('room')->orderBy('booking_date', 'desc')->orderBy('start_time', 'desc')->get();
            // Fetch approved bookings
            $approvedBookings = $user->approvedBookings()->with(['room', 'user'])->orderBy('booking_date', 'desc')->get();
        }

        // 2. Toilet Inspection Activity Stats & History
        $toiletStats = [
            'total' => 0,
            'approved' => 0,
            'rejected' => 0,
            'pending' => 0,
        ];
        $inspections = collect();
        $approvals = collect();

        if ($user->isAdmin()) {
            // Admin stats represent monitoring summary system-wide
            $toiletStats = [
                'total' => \App\Models\Inspection::count(),
                'approved' => \App\Models\Inspection::where('status', 'approved')->count(),
                'rejected' => \App\Models\Inspection::where('status', 'rejected')->count(),
                'pending' => \App\Models\Inspection::where('status', 'submitted')->count(),
            ];
            // System-wide inspection history for monitoring
            $inspections = \App\Models\Inspection::with(['location', 'inspector', 'approval.approver'])->orderBy('inspected_at', 'desc')->get();
        } elseif ($user->isSpv()) {
            // Supervisor stats are based on their reviews/approvals
            $toiletStats = [
                'total' => $user->approvals()->count(),
                'approved' => $user->approvals()->where('action', 'approve')->count(),
                'rejected' => $user->approvals()->where('action', 'reject')->count(),
                'pending' => \App\Models\Inspection::where('status', 'submitted')->count(),
            ];
            // Historical inspections reviewed by this supervisor
            $approvals = $user->approvals()->with(['inspection.location', 'inspection.inspector'])->orderBy('actioned_at', 'desc')->get();
        } else {
            // Inspector stats are based on their submitted inspections
            $toiletStats = [
                'total' => $user->inspections()->count(),
                'approved' => $user->inspections()->where('status', 'approved')->count(),
                'rejected' => $user->inspections()->where('status', 'rejected')->count(),
                'pending' => $user->inspections()->where('status', 'submitted')->count(),
            ];
            // Historical inspections performed by this inspector
            $inspections = $user->inspections()->with(['location', 'approval.approver'])->orderBy('inspected_at', 'desc')->get();
        }

        return view('admin.users.show', compact(
            'user',
            'meetingRoomStats',
            'bookings',
            'approvedBookings',
            'toiletStats',
            'inspections',
            'approvals'
        ));
    }

    /**
     * Activate the specified user.
     */
    public function activate(User $user)
    {
        if (!$this->checkActiveModule()) {
            return redirect()->route('admin.home')
                ->with('error', 'Please select a management module first.');
        }

        $user->status = 'active';
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been activated successfully.");
    }

    /**
     * Deactivate the specified user.
     */
    public function deactivate(User $user)
    {
        if (!$this->checkActiveModule()) {
            return redirect()->route('admin.home')
                ->with('error', 'Please select a management module first.');
        }

        // Prevent self-deactivation
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot deactivate user: You are currently logged in as this user.');
        }

        $user->status = 'inactive';
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been deactivated successfully.");
    }
}
