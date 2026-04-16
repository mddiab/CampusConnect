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
        // FEATURE: Edit/Update Requests
        Route::get('/student/requests/{serviceRequest}/edit', [StudentRequestController::class, 'edit'])->name('student.requests.edit');
        Route::patch('/student/requests/{serviceRequest}', [StudentRequestController::class, 'update'])->name('student.requests.update');
        Route::post('/student/requests/{serviceRequest}/messages', [StudentRequestController::class, 'storeMessage'])
            ->name('student.requests.messages.store');
        Route::get('/student/requests/{serviceRequest}/attachment', [StudentRequestController::class, 'download'])
            ->name('student.requests.attachment');
    });

    Route::middleware('role:staff')->group(function () {
        Route::get('/staff/dashboard', [StaffRequestController::class, 'index'])->name('staff.dashboard');
        Route::get('/staff/requests/{serviceRequest}', [StaffRequestController::class, 'show'])->name('staff.requests.show');
        Route::post('/staff/requests/{serviceRequest}/messages', [StaffRequestController::class, 'storeMessage'])
            ->name('staff.requests.messages.store');
        Route::patch('/staff/requests/{serviceRequest}', [StaffRequestController::class, 'update'])->name('staff.requests.update');
        Route::get('/staff/requests/{serviceRequest}/attachment', [StaffRequestController::class, 'download'])
            ->name('staff.requests.attachment');
    });
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    Route::get('/admin/departments', [AdminController::class, 'departments'])->name('admin.departments');
    Route::post('/admin/departments', [AdminController::class, 'storeDepartment'])->name('admin.departments.store');
    Route::put('/admin/departments/{department}', [AdminController::class, 'updateDepartment'])->name('admin.departments.update');
    Route::delete('/admin/departments/{department}', [AdminController::class, 'destroyDepartment'])->name('admin.departments.destroy');

    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::post('/admin/categories', [AdminController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/admin/categories/{serviceCategory}', [AdminController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/admin/categories/{serviceCategory}', [AdminController::class, 'destroyCategory'])->name('admin.categories.destroy');

    Route::get('/admin/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::get('/admin/reports/export', [AdminController::class, 'exportReports'])->name('admin.reports.export');
});
