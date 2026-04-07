<?php

namespace App\Http\Controllers;

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
        // FEATURE: Request History & Archiving
        // Build base query for student's requests - exclude archived by default
        $query = $request->user()->serviceRequests()->notArchived();

        // Search by title (keyword search)
        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerm = $request->get('search');
            // Search in title or description for the keyword
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by department
        if ($request->has('department') && !empty($request->get('department'))) {
            $query->where('department', $request->get('department'));
        }

        // Filter by category
        if ($request->has('category') && !empty($request->get('category'))) {
            $query->where('category', $request->get('category'));
        }

        // Filter by status (pending, in_progress, completed)
        if ($request->has('status') && !empty($request->get('status'))) {
            $query->where('status', $request->get('status'));
        }

        // Sort options
        $sortBy = $request->get('sort', 'latest'); // default: latest
        switch ($sortBy) {
            case 'oldest':
                $query->oldest();
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            default: // 'latest'
                $query->latest();
        }

        // Execute the query
        $serviceRequests = $query->get();

        // FEATURE: Request History & Archiving - Get archived requests separately
        $archivedRequests = $request->user()->serviceRequests()->archived()->latest()->get();

        return view('dashboards.student', [
            'serviceRequests' => $serviceRequests,
            'archivedRequests' => $archivedRequests,
            'recentRequests' => $serviceRequests->sortByDesc('updated_at')->take(3),
            'pendingRequestCount' => $request->user()->serviceRequests()->notArchived()->where('status', ServiceRequest::STATUS_PENDING)->count(),
            'inProgressRequestCount' => $request->user()->serviceRequests()->notArchived()->where('status', ServiceRequest::STATUS_IN_PROGRESS)->count(),
            'completedRequestCount' => $request->user()->serviceRequests()->notArchived()->where('status', ServiceRequest::STATUS_COMPLETED)->count(),
            'totalRequestCount' => $request->user()->serviceRequests()->notArchived()->count(),
            'archivedCount' => $archivedRequests->count(),
            'departments' => ServiceRequest::departments(),
            'categories' => ServiceRequest::categories(),
            // Pass current filter values to view for form persistence
            'currentSearch' => $request->get('search', ''),
            'currentDepartment' => $request->get('department', ''),
            'currentCategory' => $request->get('category', ''),
            'currentStatus' => $request->get('status', ''),
            'currentSort' => $sortBy,
        ]);
    }

    public function staff(): View
    {
        return view('dashboards.staff');
    }

    public function admin(): View
    {
        return view('dashboards.admin');
    }
}
