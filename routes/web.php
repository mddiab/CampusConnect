<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentRequestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', [LoginController::class, 'create'])->name('login');
Route::get('/login', fn () => redirect()->route('login'));
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/staff/dashboard', [DashboardController::class, 'staff'])
        ->middleware('role:staff')
        ->name('staff.dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::middleware('role:student')->group(function () {
        Route::get('/student/dashboard', [DashboardController::class, 'student'])->name('student.dashboard');
        Route::post('/student/requests', [StudentRequestController::class, 'store'])->name('student.requests.store');
        Route::get('/student/requests/{serviceRequest}', [StudentRequestController::class, 'show'])->name('student.requests.show');
        Route::get('/student/requests/{serviceRequest}/attachment', [StudentRequestController::class, 'download'])
            ->name('student.requests.attachment');
    });
});

Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

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
