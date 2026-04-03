<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\Department;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 📊 Totals
        $totalUsers = User::count();
        $totalDepartments = Department::count();
        $todayRequests = ServiceRequest::whereDate('created_at', today())->count();

        // 👥 Role distribution
        $roles = [
            'admin' => User::where('role', User::ROLE_ADMIN)->count(),
            'staff' => User::where('role', User::ROLE_STAFF)->count(),
            'student' => User::where('role', User::ROLE_STUDENT)->count(),
        ];

        // 🆕 Recent users
        $recentUsers = User::latest()->take(5)->get();

        // 🏢 Departments with staff count
        $departments = Department::with('categories')->withCount('users')->get();

        // 📋 Categories per department (from DB)
        foreach ($departments as $dept) {
            $dept->categories_list = $dept->categories->pluck('name');
        }

        // 📜 Activity logs (fallback using requests if you don't have logs table)
        $logs = ServiceRequest::with('user')->latest()->take(10)->get();

        return view('dashboards.admin', compact(
            'totalUsers',
            'totalDepartments',
            'todayRequests',
            'roles',
            'recentUsers',
            'departments',
            'logs'
        ));
    }
}