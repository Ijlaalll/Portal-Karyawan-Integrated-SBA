<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\RoomSearchController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated User Module Selection & Global Routes
Route::middleware(['auth', 'check_status'])->group(function () {
    Route::get('/select-module', [AuthController::class, 'showSelector'])->name('select-module');
    Route::post('/select-module', [AuthController::class, 'selectModule'])->name('select-module.post');
    Route::get('/no-access', [AuthController::class, 'noAccess'])->name('no-access');

    // Global Notifications Management (Accessible across all roles & modules)
    Route::get('/notifications/{notification}/read', function (\App\Models\Notification $notification) {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
        $notification->update(['is_read' => true]);
        if ($notification->link) {
            return redirect($notification->link);
        }
        return redirect()->back();
    })->name('notifications.read');

    Route::match(['get', 'post'], '/notifications/read-all', function () {
        \App\Models\Notification::where('user_id', auth()->id())
            ->update(['is_read' => true]);
        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    })->name('notifications.mark-all-read');
});

// Authenticated User Route Group Foundation (Meeting Room Module)
Route::middleware(['auth', 'check_status', 'module:meeting_room'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rooms/search', [RoomSearchController::class, 'index'])->name('rooms.search');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    
    Route::resource('bookings', BookingController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/review', [BookingController::class, 'storeReview'])->name('bookings.review.store');
    Route::post('/bookings/{booking}/snooze', [BookingController::class, 'snoozeReview'])->name('bookings.review.snooze');
    Route::post('/bookings/{booking}/acknowledge-ready', [BookingController::class, 'acknowledgeReady'])->name('bookings.acknowledge-ready');
    Route::post('/bookings/{booking}/acknowledge-active', [BookingController::class, 'acknowledgeActive'])->name('bookings.acknowledge-active');
    Route::post('/bookings/{booking}/acknowledge-cancellation', [BookingController::class, 'acknowledgeCancellation'])->name('bookings.acknowledge-cancellation');

    // GA and IT Operations Workspaces
    Route::get('/ga/preparations', [\App\Http\Controllers\PreparationController::class, 'gaIndex'])->name('ga.preparations');
    Route::get('/it/preparations', [\App\Http\Controllers\PreparationController::class, 'itIndex'])->name('it.preparations');
    Route::post('/preparations/{booking}/status', [\App\Http\Controllers\PreparationController::class, 'updateStatus'])->name('preparations.status.update');
});

// Toilet Inspection Module Routes
Route::middleware(['auth', 'check_status', 'module:toilet_inspection'])->prefix('toilet-inspection')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\ToiletInspection\InspectionDashboardController::class, 'index'])->name('toilet_inspection.dashboard');
    Route::get('/report', [\App\Http\Controllers\ToiletInspection\InspectionController::class, 'report'])->name('toilet_inspection.report');
    Route::resource('inspections', \App\Http\Controllers\ToiletInspection\InspectionController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/approvals', [\App\Http\Controllers\ToiletInspection\InspectionApprovalController::class, 'index'])->name('toilet_inspection.approvals.index');
    Route::get('/inspections/{inspection}/review', [\App\Http\Controllers\ToiletInspection\InspectionApprovalController::class, 'showReview'])->name('toilet_inspection.review');
    Route::post('/inspections/{inspection}/approve', [\App\Http\Controllers\ToiletInspection\InspectionApprovalController::class, 'approve'])->name('toilet_inspection.approve');
    Route::post('/inspections/{inspection}/reject', [\App\Http\Controllers\ToiletInspection\InspectionApprovalController::class, 'reject'])->name('toilet_inspection.reject');
});

// ATK Module Routes
Route::middleware(['auth', 'check_status', 'module:atk', 'active_module:atk'])->prefix('atk')->as('atk.')->group(function () {
    // 1. Requester / User Routes
    Route::get('/catalog', [\App\Http\Controllers\Atk\UserRequestController::class, 'catalog'])->name('catalog');
    Route::get('/requests/user', [\App\Http\Controllers\Atk\UserRequestController::class, 'myRequests'])->name('requests.user');
    Route::post('/requests', [\App\Http\Controllers\Atk\UserRequestController::class, 'storeRequest'])->name('requests.store');
    Route::post('/requests/{atkRequest}/cancel', [\App\Http\Controllers\Atk\UserRequestController::class, 'cancelRequest'])->name('requests.cancel');

    // 2. Manager Routes (Protected in controllers for Admin/GA)
    Route::get('/dashboard', [\App\Http\Controllers\Atk\AtkDashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('categories', \App\Http\Controllers\Atk\CategoryController::class)->except(['show']);
    Route::get('items/template', [\App\Http\Controllers\Atk\ItemController::class, 'downloadTemplate'])->name('items.template');
    Route::post('items/import', [\App\Http\Controllers\Atk\ItemController::class, 'import'])->name('items.import');
    Route::resource('items', \App\Http\Controllers\Atk\ItemController::class);
    Route::resource('variants', \App\Http\Controllers\Atk\VariantController::class)->except(['show']);
    
    Route::get('/stock', [\App\Http\Controllers\Atk\StockController::class, 'index'])->name('stock.index');
    Route::post('/stock/{variant}/adjust', [\App\Http\Controllers\Atk\StockController::class, 'adjust'])->name('stock.adjust');
    Route::get('/stock/history', [\App\Http\Controllers\Atk\StockController::class, 'history'])->name('stock.history');
    
    Route::get('/requests', [\App\Http\Controllers\Atk\RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{atkRequest}', [\App\Http\Controllers\Atk\RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{atkRequest}/approve', [\App\Http\Controllers\Atk\RequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{atkRequest}/reject', [\App\Http\Controllers\Atk\RequestController::class, 'reject'])->name('requests.reject');
    Route::post('/requests/{atkRequest}/process', [\App\Http\Controllers\Atk\RequestController::class, 'process'])->name('requests.process');
    Route::post('/requests/{atkRequest}/complete', [\App\Http\Controllers\Atk\RequestController::class, 'complete'])->name('requests.complete');
    
    Route::get('/history', [\App\Http\Controllers\Atk\RequestController::class, 'historyIndex'])->name('history.index');
});

// Admin Route Group Foundation
Route::middleware(['auth', 'check_status', 'admin'])->prefix('admin')->as('admin.')->group(function () {
    // 1. Admin Home / Management Selection
    Route::get('/', [\App\Http\Controllers\Admin\AdminHomeController::class, 'index'])->name('home');
    Route::post('/select-module', [\App\Http\Controllers\Admin\AdminHomeController::class, 'selectModule'])->name('select_module');

    // Reorganized settings routes (User Management, Cost Center, SMTP Settings)
    Route::prefix('settings')->group(function () {
        Route::post('users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
        Route::post('users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::resource('users', AdminUserController::class);

        // Cost Center Management Module (Tabbed: Overview, Cost Centers, Room Pricing, Activities & Transactions, Reports)
        Route::get('cost-centers/overview', [\App\Http\Controllers\Admin\CostCenterController::class, 'overview'])->name('settings.cost-centers.overview');
        Route::get('cost-centers/pricing', [\App\Http\Controllers\Admin\CostCenterController::class, 'pricing'])->name('settings.cost-centers.pricing');
        Route::post('cost-centers/pricing/{room}', [\App\Http\Controllers\Admin\CostCenterController::class, 'updatePricing'])->name('settings.cost-centers.pricing.update');
        Route::get('cost-centers/transactions', [\App\Http\Controllers\Admin\CostCenterController::class, 'transactions'])->name('settings.cost-centers.transactions');
        Route::get('cost-centers/reports', [\App\Http\Controllers\Admin\CostCenterController::class, 'reports'])->name('settings.cost-centers.reports');
        Route::resource('cost-centers', \App\Http\Controllers\Admin\CostCenterController::class)->names('settings.cost-centers');

        // SMTP Settings
        Route::get('smtp', [\App\Http\Controllers\Admin\SmtpSettingsController::class, 'show'])->name('settings.smtp.show');
        Route::post('smtp', [\App\Http\Controllers\Admin\SmtpSettingsController::class, 'update'])->name('settings.smtp.update');
        Route::delete('smtp/{id}', [\App\Http\Controllers\Admin\SmtpSettingsController::class, 'destroy'])->name('settings.smtp.delete');
        Route::post('smtp/{id}/activate', [\App\Http\Controllers\Admin\SmtpSettingsController::class, 'activate'])->name('settings.smtp.activate');
        Route::post('smtp/test-connection', [\App\Http\Controllers\Admin\SmtpSettingsController::class, 'testConnection'])->name('settings.smtp.test-connection');
        Route::post('smtp/test', [\App\Http\Controllers\Admin\SmtpSettingsController::class, 'testMail'])->name('settings.smtp.test');
        Route::put('smtp/templates/{template}', [\App\Http\Controllers\Admin\SmtpSettingsController::class, 'updateTemplate'])->name('settings.smtp.templates.update');
        Route::post('smtp/notifications', [\App\Http\Controllers\Admin\SmtpSettingsController::class, 'updateNotifications'])->name('settings.smtp.notifications.update');

        // Department Approvals Config for SIK
        Route::resource('department-approvals', \App\Http\Controllers\Admin\DepartmentApprovalController::class)->names('settings.department-approvals');

        // Audit Trail (Log Aktivitas Sistem)
        Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('settings.audit-logs.index');
        Route::get('audit-logs/export', [\App\Http\Controllers\Admin\AuditLogController::class, 'exportCsv'])->name('settings.audit-logs.export');
        Route::get('audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])->name('settings.audit-logs.show');
    });
});

// SIK Module Administrative Routes (Admin & GA with SIK access)
Route::middleware([
    'auth',
    'check_status',
    'module:sik',
    \App\Http\Middleware\SikManagementMiddleware::class
])->prefix('admin/sik')->as('admin.sik.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminSikController::class, 'dashboard'])->name('dashboard');
    Route::get('/all', [\App\Http\Controllers\Admin\AdminSikController::class, 'index'])->name('index');
    Route::get('/audit-logs', [\App\Http\Controllers\Admin\AdminSikController::class, 'auditLogs'])->name('audit-logs');
});

// SIK Module Routes - Requester & Management
Route::middleware(['auth', 'check_status', 'module:sik'])->group(function () {
    // 1. Pemakai / Requester Routes
    Route::get('/sik', [\App\Http\Controllers\Sik\SikController::class, 'index'])->name('sik.index');
    Route::get('/sik/create', [\App\Http\Controllers\Sik\SikController::class, 'create'])->name('sik.create');
    Route::post('/sik', [\App\Http\Controllers\Sik\SikController::class, 'store'])->name('sik.store');
    Route::get('/sik/{sik}', [\App\Http\Controllers\Sik\SikController::class, 'show'])->name('sik.show');
    Route::get('/sik/{sik}/edit', [\App\Http\Controllers\Sik\SikController::class, 'edit'])->name('sik.edit');
    Route::put('/sik/{sik}', [\App\Http\Controllers\Sik\SikController::class, 'update'])->name('sik.update');
    Route::post('/sik/{sik}/submit', [\App\Http\Controllers\Sik\SikController::class, 'submit'])->name('sik.submit');
    Route::post('/sik/{sik}/cancel', [\App\Http\Controllers\Sik\SikController::class, 'cancel'])->name('sik.cancel');
    Route::get('/sik/{sik}/print', [\App\Http\Controllers\Sik\SikController::class, 'print'])->name('sik.print');

    // 2. Pengelola / Approver / HOD Routes
    Route::prefix('sik-management')->as('sik.')->group(function () {
        Route::get('/approvals', [\App\Http\Controllers\Sik\SikApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{sik}', [\App\Http\Controllers\Sik\SikApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{sik}/approve', [\App\Http\Controllers\Sik\SikApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{sik}/reject', [\App\Http\Controllers\Sik\SikApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/approvals/{sik}/revoke', [\App\Http\Controllers\Sik\SikApprovalController::class, 'revoke'])->name('approvals.revoke');
        Route::post('/approvals/{sik}/close-manually', [\App\Http\Controllers\Sik\SikApprovalController::class, 'closeManually'])->name('approvals.close-manually');
        Route::get('/department', [\App\Http\Controllers\Sik\SikDepartmentController::class, 'index'])->name('department.index');
        Route::get('/history', [\App\Http\Controllers\Sik\SikDepartmentController::class, 'history'])->name('history.index');
    });
});

// SIK Module Routes - Security Role & Gate Scanner
Route::middleware(['auth', 'check_status'])->prefix('sik-security')->as('sik.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Sik\SikSecurityController::class, 'dashboard'])->name('security.dashboard');
    Route::get('/scanner', [\App\Http\Controllers\Sik\SikSecurityController::class, 'scanner'])->name('scanner');
    Route::post('/scan', [\App\Http\Controllers\Sik\SikSecurityController::class, 'validateQr'])->name('scanner.scan');
    Route::post('/{sik}/exit', [\App\Http\Controllers\Sik\SikSecurityController::class, 'confirmExit'])->name('scanner.exit');
    Route::post('/{sik}/return', [\App\Http\Controllers\Sik\SikSecurityController::class, 'confirmReturn'])->name('scanner.return');
    Route::get('/logs', [\App\Http\Controllers\Sik\SikSecurityController::class, 'scanLogs'])->name('scan-logs');
});

// 2. Shared Meeting Room Management Routes (Admin & GA)
Route::middleware([
    'auth',
    'check_status',
    'module:meeting_room',
    'active_module:meeting_room',
    \App\Http\Middleware\MeetingRoomManagementMiddleware::class
])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/booking-history', [AdminDashboardController::class, 'bookingHistory'])->name('booking_history');
    Route::get('/reviews', [AdminDashboardController::class, 'reviews'])->name('reviews');
    Route::get('/room-performance', [AdminDashboardController::class, 'roomPerformance'])->name('room_performance');
    
    Route::resource('rooms', AdminRoomController::class);
    Route::resource('facilities', AdminFacilityController::class);
});

// 3. Shared Toilet Inspection Management Routes (Admin, SPV, and GA)
Route::middleware([
    'auth',
    'check_status',
    'module:toilet_inspection',
    'active_module:toilet_inspection',
    \App\Http\Middleware\ToiletManagementMiddleware::class
])->prefix('admin/toilet-inspection')->as('admin.toilet_inspection.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\ToiletInspection\InspectionDashboardController::class, 'index'])->name('dashboard');
    Route::resource('locations', \App\Http\Controllers\Admin\AdminToiletLocationController::class);
    Route::resource('checklist-items', \App\Http\Controllers\Admin\AdminChecklistItemController::class);
    Route::get('/inspections', [\App\Http\Controllers\ToiletInspection\InspectionDashboardController::class, 'inspections'])->name('inspections.index');
    Route::get('/approvals', [\App\Http\Controllers\ToiletInspection\InspectionDashboardController::class, 'approvals'])->name('approvals.index');
});

// 4. Vehicle Management Module Routes
Route::middleware(['auth', 'check_status', 'module:vehicle'])->prefix('vehicles')->as('vehicles.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Vehicle\VehicleDashboardController::class, 'index'])->name('dashboard');

    // Requests
    Route::get('/requests', [\App\Http\Controllers\Vehicle\VehicleRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [\App\Http\Controllers\Vehicle\VehicleRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [\App\Http\Controllers\Vehicle\VehicleRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{vehicleRequest}', [\App\Http\Controllers\Vehicle\VehicleRequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{vehicleRequest}/cancel', [\App\Http\Controllers\Vehicle\VehicleRequestController::class, 'cancel'])->name('requests.cancel');

    // Operational Approval & Rejection (GA Only)
    Route::post('/requests/{vehicleRequest}/approve', [\App\Http\Controllers\Vehicle\VehicleRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{vehicleRequest}/reject', [\App\Http\Controllers\Vehicle\VehicleRequestController::class, 'reject'])->name('requests.reject');

    // Fuel Consumption (Konsumsi BBM)
    Route::prefix('fuel')->as('fuel.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Vehicle\VehicleFuelController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Vehicle\VehicleFuelController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Vehicle\VehicleFuelController::class, 'store'])->name('store');
        Route::get('/history/{vehicle}', [\App\Http\Controllers\Vehicle\VehicleFuelController::class, 'history'])->name('history');
        Route::get('/{fuelLog}/edit', [\App\Http\Controllers\Vehicle\VehicleFuelController::class, 'edit'])->name('edit');
        Route::put('/{fuelLog}', [\App\Http\Controllers\Vehicle\VehicleFuelController::class, 'update'])->name('update');
        Route::delete('/{fuelLog}', [\App\Http\Controllers\Vehicle\VehicleFuelController::class, 'destroy'])->name('destroy');
    });

    // Drivers Master
    Route::resource('drivers', \App\Http\Controllers\Vehicle\DriverController::class);

    // Vehicles Master
    Route::resource('/', \App\Http\Controllers\Vehicle\VehicleController::class)->parameters(['' => 'vehicle']);
});
