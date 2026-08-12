<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\RoomSearchController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BookingApprovalController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Unified module selection and no-access routes
Route::middleware(['auth', 'check_status'])->group(function () {
    Route::get('/select-module', [AuthController::class, 'showSelector'])->name('select-module');
    Route::post('/select-module', [AuthController::class, 'selectModule'])->name('select-module.post');
    Route::get('/no-access', [AuthController::class, 'noAccess'])->name('no-access');
});

// Authenticated User Route Group Foundation (Meeting Room Module)
Route::middleware(['auth', 'check_status', 'module:meeting_room'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rooms/search', [RoomSearchController::class, 'index'])->name('rooms.search');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    
    Route::resource('bookings', BookingController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
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

// Admin Route Group Foundation
Route::middleware(['auth', 'check_status', 'admin'])->prefix('admin')->as('admin.')->group(function () {
    // 1. Admin Home / Management Selection
    Route::get('/', [\App\Http\Controllers\Admin\AdminHomeController::class, 'index'])->name('home');
    Route::post('/select-module', [\App\Http\Controllers\Admin\AdminHomeController::class, 'selectModule'])->name('select_module');

    // Global User Management System (shared between modules)
    Route::post('users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
    Route::post('users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
    Route::resource('users', AdminUserController::class);

    // 2. Meeting Room Admin Routes
    Route::middleware(['active_module:meeting_room'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        Route::resource('rooms', AdminRoomController::class);
        Route::resource('facilities', AdminFacilityController::class);
        
        Route::get('/approvals', [BookingApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{booking}/approve', [BookingApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{booking}/reject', [BookingApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // 3. Toilet Inspection Admin Routes
    Route::middleware(['active_module:toilet_inspection'])->prefix('toilet-inspection')->as('toilet_inspection.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\ToiletInspection\InspectionDashboardController::class, 'index'])->name('dashboard');
        Route::resource('locations', \App\Http\Controllers\Admin\AdminToiletLocationController::class);
        Route::resource('checklist-items', \App\Http\Controllers\Admin\AdminChecklistItemController::class);
        Route::get('/inspections', [\App\Http\Controllers\ToiletInspection\InspectionDashboardController::class, 'inspections'])->name('inspections.index');
        Route::get('/approvals', [\App\Http\Controllers\ToiletInspection\InspectionDashboardController::class, 'approvals'])->name('approvals.index');
    });
});
