<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffRequestController;
use App\Http\Controllers\StudentRequestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', [LoginController::class, 'create'])->name('login');
Route::get('/login', fn () => redirect()->route('login'));
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:student')->group(function () {
        Route::get('/student/dashboard', [DashboardController::class, 'student'])->name('student.dashboard');
        Route::post('/student/requests', [StudentRequestController::class, 'store'])->name('student.requests.store');
        Route::get('/student/requests/{serviceRequest}', [StudentRequestController::class, 'show'])->name('student.requests.show');
        Route::get('/student/requests/{serviceRequest}/attachment', [StudentRequestController::class, 'download'])
            ->name('student.requests.attachment');
    });

    Route::middleware('role:staff')->group(function () {
        Route::get('/staff/dashboard', [StaffRequestController::class, 'index'])->name('staff.dashboard');
        Route::get('/staff/requests/{serviceRequest}', [StaffRequestController::class, 'show'])->name('staff.requests.show');
        Route::patch('/staff/requests/{serviceRequest}', [StaffRequestController::class, 'update'])->name('staff.requests.update');
        Route::get('/staff/requests/{serviceRequest}/attachment', [StaffRequestController::class, 'download'])
            ->name('staff.requests.attachment');
    });
});

Route::middleware(['auth', 'role:admin'])->group(function () {

    // Admin Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    // --- User Management Routes (For the Modals) ---
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // --- Placeholders ---
    Route::get('/admin/users', function () {
        return 'Users Page Coming Soon';
    })->name('admin.users');

    Route::get('/admin/departments', function () {
        return 'Departments Page Coming Soon';
    })->name('admin.departments');

    Route::get('/admin/categories', function () {
        return 'Categories Page Coming Soon';
    })->name('admin.categories');

    Route::get('/admin/reports', function () {
        return 'Reports Page Coming Soon';
    })->name('admin.reports');
});
