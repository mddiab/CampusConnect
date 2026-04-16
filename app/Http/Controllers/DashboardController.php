<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->dashboardRoute());
    }

    public function student(Request $request): View
    {
        $query = $request->user()->serviceRequests();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Apply department filter
        if ($request->filled('department') && $request->input('department') !== 'all') {
            $query->where('department_id', $request->input('department'));
        }

        // Apply category filter
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('service_category_id', $request->input('category'));
        }

        // Apply sort
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'title' => $query->orderBy('title', 'asc'),
            'title-desc' => $query->orderBy('title', 'desc'),
            default => $query->latest(),
        };

        // Get all requests with eager loading
        $allRequests = $query->with(['department', 'serviceCategory'])->get();

        // Separate archived and active
        $activeRequests = $allRequests->filter(fn ($r) => !$r->archived_at);
        $archivedRequests = $allRequests->filter(fn ($r) => $r->archived_at);

        $departments = Department::query()
            ->with(['categories' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('dashboards.student', [
            'activeRequests' => $activeRequests,
            'archivedRequests' => $archivedRequests,
            'recentRequests' => $activeRequests->sortByDesc('updated_at')->take(3),
            'pendingRequestCount' => $activeRequests->where('status', ServiceRequest::STATUS_PENDING)->count(),
            'inProgressRequestCount' => $activeRequests->where('status', ServiceRequest::STATUS_IN_PROGRESS)->count(),
            'completedRequestCount' => $activeRequests->where('status', ServiceRequest::STATUS_COMPLETED)->count(),
            'totalRequestCount' => $activeRequests->count(),
            'archivedRequestCount' => $archivedRequests->count(),
            'departments' => $departments,
            'categoriesByDepartment' => $departments
                ->mapWithKeys(fn (Department $department) => [
                    $department->id => $department->categories
                        ->map(fn ($category) => [
                            'id' => $category->id,
                            'name' => $category->name,
                        ])
                        ->values()
                        ->all(),
                ])
                ->all(),
        ]);
    }

    public function admin(): View
    {
        return view('dashboards.admin');
    }
}
