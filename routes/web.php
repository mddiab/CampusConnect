<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'create'])->name('login');
Route::get('/login', fn () => redirect()->route('login'));
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/student/dashboard', [DashboardController::class, 'student'])
        ->middleware('role:student')
        ->name('student.dashboard');
    Route::get('/staff/dashboard', [DashboardController::class, 'staff'])
        ->middleware('role:staff')
        ->name('staff.dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');
});
