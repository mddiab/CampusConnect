<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        return view('dashboards.admin', $this->dashboardData($request));
    }

    public function users(): RedirectResponse
    {
        return redirect()->to(route('admin.dashboard').'#user-management');
    }

    public function departments(): RedirectResponse
    {
        return redirect()->to(route('admin.dashboard').'#department-management');
    }

    public function categories(): RedirectResponse
    {
        return redirect()->to(route('admin.dashboard').'#category-management');
    }

    public function reports(Request $request): View
    {
        $selectedStatus = $request->string('status')->toString();
        $selectedDepartmentId = $this->selectedDepartmentId($request);

        if (! in_array($selectedStatus, ['all', ...ServiceRequest::statuses()], true)) {
            $selectedStatus = 'all';
        }

        $requestsQuery = ServiceRequest::query()
            ->with(['user', 'department', 'serviceCategory'])
            ->when(
                $selectedStatus !== 'all',
                fn ($query) => $query->where('status', $selectedStatus),
            )
            ->when(
                $selectedDepartmentId,
                fn ($query) => $query->where('department_id', $selectedDepartmentId),
            );

        $serviceRequests = (clone $requestsQuery)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summaryQuery = ServiceRequest::query()
            ->when(
                $selectedStatus !== 'all',
                fn ($query) => $query->where('status', $selectedStatus),
            )
            ->when(
                $selectedDepartmentId,
                fn ($query) => $query->where('department_id', $selectedDepartmentId),
            );

        return view('admin.reports', [
            'allDepartments' => Department::query()->orderBy('name')->get(),
            'departmentBreakdown' => Department::query()
                ->withCount('serviceRequests')
                ->orderByDesc('service_requests_count')
                ->orderBy('name')
                ->get(),
            'serviceRequests' => $serviceRequests,
            'selectedDepartmentId' => $selectedDepartmentId ?: null,
            'selectedStatus' => $selectedStatus,
            'totalRequests' => (clone $summaryQuery)->count(),
            'pendingRequestCount' => (clone $summaryQuery)->where('status', ServiceRequest::STATUS_PENDING)->count(),
            'inProgressRequestCount' => (clone $summaryQuery)->where('status', ServiceRequest::STATUS_IN_PROGRESS)->count(),
            'completedRequestCount' => (clone $summaryQuery)->where('status', ServiceRequest::STATUS_COMPLETED)->count(),
        ]);
    }

    public function exportReports(Request $request): StreamedResponse
    {
        $selectedStatus = $request->string('status')->toString();
        $selectedDepartmentId = $this->selectedDepartmentId($request);

        if (! in_array($selectedStatus, ['all', ...ServiceRequest::statuses()], true)) {
            $selectedStatus = 'all';
        }

        $serviceRequests = ServiceRequest::query()
            ->with(['user', 'department', 'serviceCategory'])
            ->when(
                $selectedStatus !== 'all',
                fn ($query) => $query->where('status', $selectedStatus),
            )
            ->when(
                $selectedDepartmentId,
                fn ($query) => $query->where('department_id', $selectedDepartmentId),
            )
            ->latest()
            ->get();

        $filename = 'campusconnect-service-requests-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($serviceRequests): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Title',
                'Student',
                'Student Email',
                'Department',
                'Category',
                'Status',
                'Submitted At',
                'Resolved At',
                'Staff Notes',
            ]);

            foreach ($serviceRequests as $serviceRequest) {
                fputcsv($handle, [
                    $serviceRequest->id,
                    $serviceRequest->title,
                    $serviceRequest->user?->name,
                    $serviceRequest->user?->email,
                    $serviceRequest->departmentName(),
                    $serviceRequest->categoryName(),
                    $serviceRequest->statusLabel(),
                    $serviceRequest->created_at?->format('Y-m-d H:i:s'),
                    $serviceRequest->resolved_at?->format('Y-m-d H:i:s'),
                    $serviceRequest->staff_notes,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
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

        if ($validated['role'] === User::ROLE_STAFF) {
            $this->ensureDepartmentHasStaffCapacity((int) $validated['department_id']);
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['role'] === User::ROLE_STAFF ? $validated['department_id'] : null,
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'User added successfully.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|string|in:admin,staff,student',
            'department_id' => [
                'nullable',
                'integer',
                Rule::exists('departments', 'id'),
                Rule::requiredIf(fn () => $request->input('role') === User::ROLE_STAFF),
            ],
        ]);

        if ($validated['role'] === User::ROLE_STAFF) {
            $this->ensureDepartmentHasStaffCapacity((int) $validated['department_id'], $user);
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['role'] === User::ROLE_STAFF ? $validated['department_id'] : null,
        ]);

        return back()->with('success', 'User updated successfully.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'You cannot delete your own admin account while you are signed in.');
        }

        if ($user->role === User::ROLE_ADMIN && User::query()->where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'At least one admin account must remain in the system.');
        }

        $user->loadCount(['serviceRequests', 'serviceRequestMessages']);

        if ($user->service_requests_count > 0 || $user->service_request_messages_count > 0) {
            return back()->with('error', 'This user cannot be deleted while they still have request history or conversation messages.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
        ]);

        Department::create($validated);

        return back()->with('success', 'Department created successfully.');
    }

    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
        ]);

        $department->update($validated);

        return back()->with('success', 'Department updated successfully.');
    }

    public function destroyDepartment(Department $department): RedirectResponse
    {
        $department->loadCount(['users', 'categories', 'serviceRequests']);

        if ($department->users_count > 0 || $department->categories_count > 0 || $department->service_requests_count > 0) {
            return back()->with('error', 'This department cannot be deleted while it still has users, categories, or requests.');
        }

        $department->delete();

        return back()->with('success', 'Department deleted successfully.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories', 'name')->where(
                    fn ($query) => $query->where('department_id', $request->input('department_id')),
                ),
            ],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
        ]);

        ServiceCategory::create($validated);

        return back()->with('success', 'Service category created successfully.');
    }

    public function updateCategory(Request $request, ServiceCategory $serviceCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories', 'name')
                    ->where(fn ($query) => $query->where('department_id', $request->input('department_id')))
                    ->ignore($serviceCategory->id),
            ],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
        ]);

        $departmentChanged = (int) $serviceCategory->department_id !== (int) $validated['department_id'];

        if ($departmentChanged && $serviceCategory->serviceRequests()->exists()) {
            throw ValidationException::withMessages([
                'department_id' => 'This category already has service requests, so it cannot be moved to another department.',
            ]);
        }

        $serviceCategory->update($validated);

        return back()->with('success', 'Service category updated successfully.');
    }

    public function destroyCategory(ServiceCategory $serviceCategory): RedirectResponse
    {
        if ($serviceCategory->serviceRequests()->exists()) {
            return back()->with('error', 'This category cannot be deleted while it is assigned to service requests.');
        }

        $serviceCategory->delete();

        return back()->with('success', 'Service category deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardData(Request $request): array
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
            ->paginate(20, ['*'], 'users_page')
            ->withQueryString();

        $allDepartments = Department::query()
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->with([
                'categories' => fn ($query) => $query
                    ->withCount('serviceRequests')
                    ->orderBy('name'),
            ])
            ->when($request->filled('dept') && $request->dept !== 'all', function ($query) use ($request) {
                $query->where('id', $request->dept);
            })
            ->withCount(['staffMembers', 'categories', 'serviceRequests'])
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

        return [
            'totalUsers' => User::count(),
            'totalDepartments' => Department::count(),
            'todayRequests' => ServiceRequest::whereDate('created_at', today())->count(),
            'roles' => [
                'admin' => User::where('role', User::ROLE_ADMIN)->count(),
                'staff' => User::where('role', User::ROLE_STAFF)->count(),
                'student' => User::where('role', User::ROLE_STUDENT)->count(),
            ],
            'recentUsers' => $recentUsers,
            'departments' => $departments,
            'allDepartments' => $allDepartments,
            'logs' => $logs,
        ];
    }

    private function selectedDepartmentId(Request $request): ?int
    {
        if ($request->filled('department_id')) {
            return $request->integer('department_id') ?: null;
        }

        if ($request->filled('dept') && $request->input('dept') !== 'all') {
            return $request->integer('dept') ?: null;
        }

        return null;
    }

    private function ensureDepartmentHasStaffCapacity(int $departmentId, ?User $ignoreUser = null): void
    {
        $staffCount = User::query()
            ->where('role', User::ROLE_STAFF)
            ->where('department_id', $departmentId)
            ->when($ignoreUser, fn ($query) => $query->whereKeyNot($ignoreUser->id))
            ->count();

        if ($staffCount >= 3) {
            throw ValidationException::withMessages([
                'department_id' => 'Each department can have a maximum of 3 staff accounts.',
            ]);
        }
    }
}
