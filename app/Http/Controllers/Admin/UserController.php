<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\UserModule;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}



    /**
     * Display a listing of registered users.
     */
    public function index(Request $request)
    {
        // Default management context if not specified in the request
        if (!$request->has('management')) {
            $request->merge(['management' => 'all']);
        }

        $query = User::with(['modules', 'costCenter'])->orderBy('name', 'asc');

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
        $atkCount = UserModule::where('module', 'atk')->count();
        $sikCount = UserModule::where('module', 'sik')->count();
        $vehicleCount = UserModule::where('module', 'vehicle')->count();

        return view('admin.settings.users.index', compact(
            'users', 
            'totalUsers', 
            'meetingRoomCount', 
            'toiletInspectionCount',
            'atkCount',
            'sikCount',
            'vehicleCount'
        ));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $costCenters = \App\Models\CostCenter::where('status', 'active')->orderBy('name', 'asc')->get();
        return view('admin.settings.users.create', compact('costCenters'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $createdUser = $this->userService->createUser($request->validated());

        AuditLogService::log(
            'user_management',
            'create_user',
            "Admin membuat pengguna baru: {$createdUser->name} ({$createdUser->email}) dengan peran {$createdUser->role}",
            [
                'created_user_id' => $createdUser->id,
                'email' => $createdUser->email,
                'role' => $createdUser->role,
                'modules' => $request->input('modules', []),
            ],
            auth()->user()
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load(['modules', 'costCenter']);
        $costCenters = \App\Models\CostCenter::where('status', 'active')
            ->orWhere('id', $user->cost_center_id)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.settings.users.edit', compact('user', 'costCenters'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:user,admin,spv,ga,it,security'],
            'modules' => ['required_unless:role,admin,security', 'array'],
            'modules.*' => ['string', 'in:meeting_room,toilet_inspection,atk,sik,vehicle'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:255'],
            'joined_at' => ['nullable', 'date'],
            'cost_center_id' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('cost_centers', 'id')->where(function ($query) use ($user) {
                    $query->where('status', 'active')->orWhere('id', $user->cost_center_id);
                }),
            ],
        ], [
            'cost_center_id.exists' => 'Cost Center yang dipilih tidak valid atau tidak aktif.',
        ]);

        // Prevent modifying the role of the last active Admin to a non-admin role
        if ($user->role === 'admin' && $user->status === 'active' && $request->input('role') !== 'admin') {
            $activeAdminCount = User::where('role', 'admin')->where('status', 'active')->count();
            if ($activeAdminCount <= 1) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Tidak dapat mengubah peran pengguna: Ini adalah satu-satunya akun Admin aktif yang tersisa di sistem.');
            }
        }

        $data = $request->only(['name', 'email', 'password', 'role', 'modules', 'department', 'position', 'employee_id', 'joined_at', 'cost_center_id']);

        $this->userService->updateUser($user, $data);

        AuditLogService::log(
            'user_management',
            'update_user',
            "Admin memperbarui data pengguna: {$user->name} ({$user->email}) - Role: {$data['role']}",
            [
                'target_user_id' => $user->id,
                'email' => $user->email,
                'role' => $data['role'],
                'modules' => $data['modules'] ?? [],
                'department' => $data['department'] ?? null,
                'position' => $data['position'] ?? null,
            ],
            auth()->user()
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // 1. Current user
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus pengguna: Anda sedang masuk menggunakan akun pengguna ini.');
        }

        // 2. Only admin (Do not allow deleting any admin account)
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus pengguna dengan peran Admin.');
        }

        // 3. Meeting Room Bookings
        if ($user->bookings()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus pengguna: Pengguna ini memiliki riwayat pemesanan ruang meeting.');
        }

        // 4. Approved Bookings (as Admin approver)
        if ($user->approvedBookings()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus pengguna: Pengguna ini memiliki riwayat persetujuan pemesanan ruang meeting.');
        }

        // 5. Toilet Inspections (as Inspector)
        if ($user->inspections()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus pengguna: Pengguna ini memiliki riwayat inspeksi toilet yang telah dikirim.');
        }

        // 6. Inspection Approvals (as SPV reviewer)
        if ($user->approvals()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menghapus pengguna: Pengguna ini memiliki riwayat ulasan persetujuan inspeksi toilet.');
        }

        // 7. GA/IT task history
        if ($user->isGa() || $user->isIt()) {
            if ($user->hasHistory()) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Akun GA/IT tidak dapat dihapus karena memiliki riwayat tugas persiapan.');
            }
        }

        $deletedName = $user->name;
        $deletedEmail = $user->email;
        $deletedId = $user->id;

        // Safe to delete: Delete modules first to prevent FK constraint failures
        DB::transaction(function () use ($user) {
            $user->modules()->delete();
            $user->delete();
        });

        AuditLogService::log(
            'user_management',
            'delete_user',
            "Admin menghapus pengguna: {$deletedName} ({$deletedEmail})",
            ['deleted_user_id' => $deletedId, 'email' => $deletedEmail],
            auth()->user()
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Display the specified user details.
     */
    public function show(User $user)
    {
        $user->load(['modules', 'costCenter']);

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
        } elseif ($user->isGa() || $user->isIt()) {
            $role = $user->role;
            if ($role === 'ga') {
                $pending = \App\Models\Booking::whereIn('meeting_type', ['offline', 'hybrid'])->where('status', 'approved')
                    ->where(function($q) {
                        $q->where('ga_status', 'pending')
                          ->orWhere(function($sub) {
                              $sub->whereNull('ga_status')->where('preparation_status', 'Menunggu Persiapan');
                          });
                    })->count();
                $inProgress = \App\Models\Booking::whereIn('meeting_type', ['offline', 'hybrid'])->where('status', 'approved')
                    ->where(function($q) {
                        $q->where('ga_status', 'in_progress')
                          ->orWhere(function($sub) {
                              $sub->whereNull('ga_status')->where('preparation_status', 'Sedang Dipersiapkan');
                          });
                    })->count();
                $ready = \App\Models\Booking::whereIn('meeting_type', ['offline', 'hybrid'])->where('status', 'approved')
                    ->where(function($q) {
                        $q->where('ga_status', 'ready')
                          ->orWhere(function($sub) {
                              $sub->whereNull('ga_status')->where('preparation_status', 'Siap');
                          });
                    })->count();
                $notReady = \App\Models\Booking::whereIn('meeting_type', ['offline', 'hybrid'])
                    ->where(function($q) {
                        $q->where('ga_status', 'not_ready')
                          ->orWhere(function($sub) {
                              $sub->whereNull('ga_status')->where('preparation_status', 'Tidak Siap');
                          });
                    })->count();
                $completed = \App\Models\Booking::whereIn('meeting_type', ['offline', 'hybrid'])->where('status', 'completed')->count();
                $total = $pending + $inProgress + $ready + $notReady + $completed;
                
                // Tasks list where GA has worked or could work
                $bookings = \App\Models\Booking::whereIn('meeting_type', ['offline', 'hybrid'])
                    ->where(function ($q) use ($user) {
                        $q->where('prepared_by_ga', $user->id)
                          ->orWhereNull('prepared_by_ga');
                    })
                    ->whereIn('status', ['approved', 'completed', 'cancelled'])
                    ->with(['user', 'room'])
                    ->orderBy('booking_date', 'desc')
                    ->orderBy('start_time', 'desc')
                    ->get();
            } else {
                // IT is hybrid only, but we support legacy online meetings for statistics!
                $pending = \App\Models\Booking::where('status', 'approved')
                    ->where(function($q) {
                        $q->where('meeting_type', 'hybrid')->where('it_status', 'pending')
                          ->orWhere(function($sub) {
                              $sub->where('meeting_type', 'online')->whereNull('it_status')->where('preparation_status', 'Menunggu Persiapan');
                          });
                    })->count();
                
                $inProgress = \App\Models\Booking::where('status', 'approved')
                    ->where(function($q) {
                        $q->where('meeting_type', 'hybrid')->where('it_status', 'in_progress')
                          ->orWhere(function($sub) {
                              $sub->where('meeting_type', 'online')->whereNull('it_status')->where('preparation_status', 'Sedang Dipersiapkan');
                          });
                    })->count();
                
                $ready = \App\Models\Booking::where('status', 'approved')
                    ->where(function($q) {
                        $q->where('meeting_type', 'hybrid')->where('it_status', 'ready')
                          ->orWhere(function($sub) {
                              $sub->where('meeting_type', 'online')->whereNull('it_status')->where('preparation_status', 'Siap');
                          });
                    })->count();
                
                $notReady = \App\Models\Booking::where(function($q) {
                    $q->where('meeting_type', 'hybrid')->where('it_status', 'not_ready')
                      ->orWhere(function($sub) {
                          $sub->where('meeting_type', 'online')->whereNull('it_status')->where('preparation_status', 'Tidak Siap');
                      });
                })->count();
                
                $completed = \App\Models\Booking::where(function($q) {
                    $q->where('meeting_type', 'hybrid')
                      ->orWhere(function($sub) {
                          $sub->where('meeting_type', 'online')->whereNull('it_status');
                      });
                })->where('status', 'completed')->count();
                $total = $pending + $inProgress + $ready + $notReady + $completed;

                $bookings = \App\Models\Booking::where(function($q) use ($user) {
                    $q->where('meeting_type', 'hybrid')
                      ->orWhere(function($sub) {
                          $sub->where('meeting_type', 'online')->whereNull('it_status');
                      });
                })
                ->where(function ($q) use ($user) {
                    $q->where('prepared_by_it', $user->id)
                      ->orWhereNull('prepared_by_it');
                })
                ->whereIn('status', ['approved', 'completed', 'cancelled'])
                ->with(['user', 'room'])
                ->orderBy('booking_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->get();
            }

            $meetingRoomStats = [
                'total' => $total,
                'pending' => $pending,
                'in_progress' => $inProgress,
                'ready' => $ready,
                'not_ready' => $notReady,
                'completed' => $completed,
            ];
            $approvedBookings = collect();
        } else {
            $meetingRoomStats = [
                'total' => $user->bookings()->count(),
                'approved' => $user->bookings()->where('status', 'approved')->count(),
                'rejected' => $user->bookings()->where('status', 'rejected')->count(),
                'pending' => $user->bookings()->where('status', 'pending')->count(),
                'cancelled' => $user->bookings()->where('status', 'cancelled')->count(),
                'completed' => $user->bookings()->where('status', 'completed')->count(),
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

        // 3. ATK Activity Stats & History
        $atkStats = [
            'total' => 0,
            'diajukan' => 0,
            'disetujui' => 0,
            'diproses' => 0,
            'selesai' => 0,
            'ditolak' => 0,
            'dibatalkan' => 0,
        ];
        $atkRequests = collect();

        if ($user->isAdmin()) {
            $atkStats = [
                'total' => \App\Models\AtkRequest::count(),
                'diajukan' => \App\Models\AtkRequest::where('status', 'diajukan')->count(),
                'disetujui' => \App\Models\AtkRequest::where('status', 'disetujui')->count(),
                'diproses' => \App\Models\AtkRequest::where('status', 'diproses')->count(),
                'selesai' => \App\Models\AtkRequest::where('status', 'selesai')->count(),
                'ditolak' => \App\Models\AtkRequest::where('status', 'ditolak')->count(),
                'dibatalkan' => \App\Models\AtkRequest::where('status', 'dibatalkan')->count(),
            ];
            $atkRequests = \App\Models\AtkRequest::with(['user', 'items.variant.item'])->orderBy('created_at', 'desc')->get();
        } elseif ($user->isGa()) {
            $atkStats = [
                'total' => \App\Models\AtkRequest::where(function($q) use ($user) {
                    $q->where('approved_by', $user->id)
                      ->orWhere('processed_by', $user->id)
                      ->orWhere('completed_by', $user->id)
                      ->orWhere('rejected_by', $user->id);
                })->count(),
                'diajukan' => 0,
                'disetujui' => \App\Models\AtkRequest::where('approved_by', $user->id)->count(),
                'diproses' => \App\Models\AtkRequest::where('processed_by', $user->id)->count(),
                'selesai' => \App\Models\AtkRequest::where('completed_by', $user->id)->count(),
                'ditolak' => \App\Models\AtkRequest::where('rejected_by', $user->id)->count(),
                'dibatalkan' => 0,
            ];
            $atkRequests = \App\Models\AtkRequest::where(function($q) use ($user) {
                    $q->where('approved_by', $user->id)
                      ->orWhere('processed_by', $user->id)
                      ->orWhere('completed_by', $user->id)
                      ->orWhere('rejected_by', $user->id);
                })
                ->with(['user', 'items.variant.item'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $atkStats = [
                'total' => \App\Models\AtkRequest::where('user_id', $user->id)->count(),
                'diajukan' => \App\Models\AtkRequest::where('user_id', $user->id)->where('status', 'diajukan')->count(),
                'disetujui' => \App\Models\AtkRequest::where('user_id', $user->id)->where('status', 'disetujui')->count(),
                'diproses' => \App\Models\AtkRequest::where('user_id', $user->id)->where('status', 'diproses')->count(),
                'selesai' => \App\Models\AtkRequest::where('user_id', $user->id)->where('status', 'selesai')->count(),
                'ditolak' => \App\Models\AtkRequest::where('user_id', $user->id)->where('status', 'ditolak')->count(),
                'dibatalkan' => \App\Models\AtkRequest::where('user_id', $user->id)->where('status', 'dibatalkan')->count(),
            ];
            $atkRequests = \App\Models\AtkRequest::where('user_id', $user->id)
                ->with(['items.variant.item'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // 4. SIK Activity Stats & History
        $sikStats = [
            'total' => 0,
            'approved' => 0,
            'pending' => 0,
            'outside' => 0,
            'returned' => 0,
            'rejected' => 0,
        ];
        $sikPermits = collect();
        $sikScans = collect();

        if ($user->isAdmin()) {
            $sikStats = [
                'total' => \App\Models\ExitPermit::count(),
                'approved' => \App\Models\ExitPermit::where('status', \App\Models\ExitPermit::STATUS_APPROVED)->count(),
                'pending' => \App\Models\ExitPermit::where('status', \App\Models\ExitPermit::STATUS_PENDING_APPROVAL)->count(),
                'outside' => \App\Models\ExitPermit::where('status', \App\Models\ExitPermit::STATUS_OUTSIDE)->count(),
                'returned' => \App\Models\ExitPermit::where('status', \App\Models\ExitPermit::STATUS_RETURNED)->count(),
                'rejected' => \App\Models\ExitPermit::where('status', \App\Models\ExitPermit::STATUS_REJECTED)->count(),
            ];
            $sikPermits = \App\Models\ExitPermit::with(['user', 'approver'])->orderBy('created_at', 'desc')->get();
        } elseif ($user->isSecurity()) {
            $totalScans = \App\Models\SikScanLog::where('scanned_by', $user->id)->count();
            $exitScans = \App\Models\SikScanLog::where('scanned_by', $user->id)->where('scan_type', 'EXIT')->count();
            $returnScans = \App\Models\SikScanLog::where('scanned_by', $user->id)->where('scan_type', 'RETURN')->count();
            $sikStats = [
                'total' => $totalScans,
                'exit' => $exitScans,
                'return' => $returnScans,
            ];
            $sikScans = \App\Models\SikScanLog::where('scanned_by', $user->id)->with(['exitPermit.user'])->orderBy('scanned_at', 'desc')->get();
        } elseif ($user->isSikApprover()) {
            $totalEvaluated = \App\Models\ExitPermit::where('approver_id', $user->id)->count();
            $approved = \App\Models\ExitPermit::where('approver_id', $user->id)->whereIn('status', [\App\Models\ExitPermit::STATUS_APPROVED, \App\Models\ExitPermit::STATUS_OUTSIDE, \App\Models\ExitPermit::STATUS_RETURNED])->count();
            $rejected = \App\Models\ExitPermit::where('approver_id', $user->id)->where('status', \App\Models\ExitPermit::STATUS_REJECTED)->count();
            $pending = \App\Models\ExitPermit::where('approver_id', $user->id)->where('status', \App\Models\ExitPermit::STATUS_PENDING_APPROVAL)->count();
            $sikStats = [
                'total' => $totalEvaluated,
                'approved' => $approved,
                'pending' => $pending,
                'rejected' => $rejected,
            ];
            $sikPermits = \App\Models\ExitPermit::where('approver_id', $user->id)->with(['user'])->orderBy('created_at', 'desc')->get();
        } else {
            // Regular employee / applicant (e.g. Petugas toilet yang diberi akses SIK)
            $totalUserPermits = \App\Models\ExitPermit::where('user_id', $user->id)->count();
            $approved = \App\Models\ExitPermit::where('user_id', $user->id)->whereIn('status', [\App\Models\ExitPermit::STATUS_APPROVED, \App\Models\ExitPermit::STATUS_OUTSIDE, \App\Models\ExitPermit::STATUS_RETURNED])->count();
            $pending = \App\Models\ExitPermit::where('user_id', $user->id)->where('status', \App\Models\ExitPermit::STATUS_PENDING_APPROVAL)->count();
            $outside = \App\Models\ExitPermit::where('user_id', $user->id)->where('status', \App\Models\ExitPermit::STATUS_OUTSIDE)->count();
            $returned = \App\Models\ExitPermit::where('user_id', $user->id)->where('status', \App\Models\ExitPermit::STATUS_RETURNED)->count();
            $rejected = \App\Models\ExitPermit::where('user_id', $user->id)->where('status', \App\Models\ExitPermit::STATUS_REJECTED)->count();
            $sikStats = [
                'total' => $totalUserPermits,
                'approved' => $approved,
                'pending' => $pending,
                'outside' => $outside,
                'returned' => $returned,
                'rejected' => $rejected,
            ];
            $sikPermits = \App\Models\ExitPermit::where('user_id', $user->id)->with(['approver'])->orderBy('created_at', 'desc')->get();
        }

        return view('admin.settings.users.show', compact(
            'user',
            'meetingRoomStats',
            'bookings',
            'approvedBookings',
            'toiletStats',
            'inspections',
            'approvals',
            'atkStats',
            'atkRequests',
            'sikStats',
            'sikPermits',
            'sikScans'
        ));
    }

    /**
     * Activate the specified user.
     */
    public function activate(User $user)
    {
        $user->status = 'active';
        $user->save();

        AuditLogService::log(
            'user_management',
            'activate_user',
            "Admin mengaktifkan pengguna: {$user->name} ({$user->email})",
            ['target_user_id' => $user->id, 'email' => $user->email],
            auth()->user()
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil diaktifkan.");
    }

    /**
     * Deactivate the specified user.
     */
    public function deactivate(User $user)
    {
        // Prevent self-deactivation
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak dapat menonaktifkan pengguna: Anda sedang masuk menggunakan akun pengguna ini.');
        }

        // Prevent deactivating the last active Admin
        if ($user->role === 'admin' && $user->status === 'active') {
            $activeAdminCount = User::where('role', 'admin')->where('status', 'active')->count();
            if ($activeAdminCount <= 1) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Tidak dapat menonaktifkan pengguna: Ini adalah satu-satunya akun Admin aktif yang tersisa di sistem.');
            }
        }

        $user->status = 'inactive';
        $user->save();

        AuditLogService::log(
            'user_management',
            'deactivate_user',
            "Admin menonaktifkan pengguna: {$user->name} ({$user->email})",
            ['target_user_id' => $user->id, 'email' => $user->email],
            auth()->user()
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil dinonaktifkan.");
    }
}

