<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ServiceRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard(Request $request)
{
    // 1. Filter Users (Search)
    $recentUsers = User::query()
        ->when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        })
        ->latest()
        ->get(); 

    // 2. Filter Departments & Categories (FIXED)
    $departments = Department::with('categories')
        ->when($request->filled('dept') && $request->dept !== 'all', function ($query) use ($request) {
            $query->where('id', $request->dept);
        })
        ->withCount('users')
        ->get();

    foreach ($departments as $dept) {
        $dept->categories_list = $dept->categories->pluck('name');
    }

    // 3. Filter Activity Logs (Service Requests)
    $logs = ServiceRequest::with('user')
        ->when($request->filled('status') && $request->status !== 'all', function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->latest()
        ->take(10)
        ->get();

    // 4. Totals & Stats
    $totalUsers = User::count();
    $totalDepartments = Department::count();
    $todayRequests = ServiceRequest::whereDate('created_at', today())->count();

    $roles = [
        'admin' => User::where('role', User::ROLE_ADMIN ?? 'admin')->count(),
        'staff' => User::where('role', User::ROLE_STAFF ?? 'staff')->count(),
        'student' => User::where('role', User::ROLE_STUDENT ?? 'student')->count(),
    ];

    return view('dashboards.admin', compact(
        'totalUsers', 'totalDepartments', 'todayRequests', 'roles', 'recentUsers', 'departments', 'logs'
    ));
}

    // --- User Management Methods ---

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:admin,staff,student',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'User added successfully!');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,staff,student',
        ]);

        $user->update($validated);

        return back()->with('success', 'User updated successfully!');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted successfully!');
    }
}