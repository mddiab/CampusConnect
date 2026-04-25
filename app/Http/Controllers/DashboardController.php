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
        $this->archiveCompletedRequestsFor($request);

        $searchTerm = trim($request->string('search')->toString());
        $selectedStatus = $request->string('status')->toString();
        $selectedDepartmentId = $request->integer('department_id') ?: null;
        $selectedCategoryId = $request->integer('service_category_id') ?: null;
        $selectedSort = $request->string('sort')->toString() ?: 'newest';

        if (! in_array($selectedStatus, ['all', '', ...ServiceRequest::statuses()], true)) {
            $selectedStatus = 'all';
        }

        if (! in_array($selectedSort, ['newest', 'latest', 'oldest', 'title', 'title-desc', 'urgent'], true)) {
            $selectedSort = 'newest';
        }

        $query = $request->user()
            ->serviceRequests()
            ->with(['department', 'serviceCategory']);

        if ($searchTerm !== '') {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        if ($selectedStatus !== '' && $selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        if ($selectedDepartmentId) {
            $query->where('department_id', $selectedDepartmentId);
        }

        if ($selectedCategoryId) {
            $query->where('service_category_id', $selectedCategoryId);
        }

        $activeRequests = $this->applyStudentRequestSort(
            (clone $query)->notArchived(),
            $selectedSort,
        )
            ->paginate(12, ['*'], 'active_page')
            ->withQueryString();

        $archivedRequests = (clone $query)
            ->archived()
            ->latest('archived_at')
            ->paginate(8, ['*'], 'archived_page')
            ->withQueryString();

        $allActiveRequestsQuery = $request->user()->serviceRequests()->notArchived();

        $departments = Department::query()
            ->with(['categories' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('dashboards.student', [
            'activeRequests' => $activeRequests,
            'archivedRequests' => $archivedRequests,
            'recentRequests' => (clone $allActiveRequestsQuery)
                ->with(['department', 'serviceCategory'])
                ->latest('updated_at')
                ->take(3)
                ->get(),
            'pendingRequestCount' => (clone $allActiveRequestsQuery)->where('status', ServiceRequest::STATUS_PENDING)->count(),
            'inProgressRequestCount' => (clone $allActiveRequestsQuery)->where('status', ServiceRequest::STATUS_IN_PROGRESS)->count(),
            'completedRequestCount' => (clone $allActiveRequestsQuery)->where('status', ServiceRequest::STATUS_COMPLETED)->count(),
            'totalRequestCount' => (clone $allActiveRequestsQuery)->count(),
            'archivedRequestCount' => $request->user()->serviceRequests()->archived()->count(),
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
            'selectedDepartmentId' => $selectedDepartmentId,
            'selectedCategoryId' => $selectedCategoryId,
            'selectedStatus' => $selectedStatus,
            'selectedSort' => $selectedSort,
            'searchTerm' => $searchTerm,
        ]);
    }

    public function admin(): View
    {
        return view('dashboards.admin');
    }

    private function applyStudentRequestSort($query, string $sort)
    {
        return match ($sort) {
            'oldest' => $query->oldest(),
            'title' => $query->orderBy('title'),
            'title-desc' => $query->orderByDesc('title'),
            'urgent' => $query->orderByDesc('is_urgent')->latest(),
            default => $query->latest(),
        };
    }

    private function archiveCompletedRequestsFor(Request $request): void
    {
        $request->user()
            ->serviceRequests()
            ->where('status', ServiceRequest::STATUS_COMPLETED)
            ->whereNull('archived_at')
            ->whereNotNull('first_completed_view_at')
            ->where('first_completed_view_at', '<=', now()->subDay())
            ->update(['archived_at' => now()]);
    }
}
