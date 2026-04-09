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
        $serviceRequests = $request->user()
            ->serviceRequests()
            ->with(['department', 'serviceCategory'])
            ->latest()
            ->get();

        $departments = Department::query()
            ->with(['categories' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('dashboards.student', [
            'serviceRequests' => $serviceRequests,
            'recentRequests' => $serviceRequests->sortByDesc('updated_at')->take(3),
            'pendingRequestCount' => $serviceRequests->where('status', ServiceRequest::STATUS_PENDING)->count(),
            'inProgressRequestCount' => $serviceRequests->where('status', ServiceRequest::STATUS_IN_PROGRESS)->count(),
            'completedRequestCount' => $serviceRequests->where('status', ServiceRequest::STATUS_COMPLETED)->count(),
            'totalRequestCount' => $serviceRequests->count(),
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
