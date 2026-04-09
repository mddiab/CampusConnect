<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $recentUsers = User::query()
            ->with('department')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $allDepartments = Department::query()
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->with(['categories' => fn ($query) => $query->orderBy('name')])
            ->when($request->filled('dept') && $request->dept !== 'all', function ($query) use ($request) {
                $query->where('id', $request->dept);
            })
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $logs = ServiceRequest::query()
            ->with(['user', 'department', 'serviceCategory'])
            ->when($request->filled('status') && $request->status !== 'all', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->take(10)
            ->get();

        $totalUsers = User::count();
        $totalDepartments = Department::count();
        $todayRequests = ServiceRequest::whereDate('created_at', today())->count();

        $roles = [
            'admin' => User::where('role', User::ROLE_ADMIN)->count(),
            'staff' => User::where('role', User::ROLE_STAFF)->count(),
            'student' => User::where('role', User::ROLE_STUDENT)->count(),
        ];

        return view('dashboards.admin', compact(
            'totalUsers',
            'totalDepartments',
            'todayRequests',
            'roles',
            'recentUsers',
            'departments',
            'allDepartments',
            'logs',
        ));
    }

    // --- User Management Methods ---

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:admin,staff,student',
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id'),
                Rule::requiredIf(fn () => $request->input('role') === User::ROLE_STAFF),
            ],
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['role'] === User::ROLE_STAFF ? $validated['department_id'] : null,
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
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id'),
                Rule::requiredIf(fn () => $request->input('role') === User::ROLE_STAFF),
            ],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['role'] === User::ROLE_STAFF ? $validated['department_id'] : null,
        ]);

        return back()->with('success', 'User updated successfully!');
    }

    public function destroyUser(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted successfully!');
    }
}
