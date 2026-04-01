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
        $serviceRequests = $request->user()
            ->serviceRequests()
            ->latest()
            ->get();

        return view('dashboards.student', [
            'serviceRequests' => $serviceRequests,
            'recentRequests' => $serviceRequests->sortByDesc('updated_at')->take(3),
            'pendingRequestCount' => $serviceRequests->where('status', ServiceRequest::STATUS_PENDING)->count(),
            'inProgressRequestCount' => $serviceRequests->where('status', ServiceRequest::STATUS_IN_PROGRESS)->count(),
            'completedRequestCount' => $serviceRequests->where('status', ServiceRequest::STATUS_COMPLETED)->count(),
            'totalRequestCount' => $serviceRequests->count(),
            'departments' => ServiceRequest::departments(),
            'categories' => ServiceRequest::categories(),
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
