<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffRequestController extends Controller
{
    public function index(Request $request): View
    {
        $staff = $request->user()->loadMissing('department');
        $selectedStatus = $request->string('status')->toString();

        if (! in_array($selectedStatus, ['all', ...ServiceRequest::statuses()], true)) {
            $selectedStatus = 'all';
        }

        $serviceRequests = collect();
        $pendingRequestCount = 0;
        $inProgressRequestCount = 0;
        $completedRequestCount = 0;

        if ($staff->department_id) {
            $departmentRequests = ServiceRequest::query()
                ->with(['user', 'department', 'serviceCategory'])
                ->forDepartment($staff->department_id);

            $serviceRequests = (clone $departmentRequests)
                ->when(
                    $selectedStatus !== 'all',
                    fn (Builder $query) => $query->where('status', $selectedStatus),
                )
                ->latest()
                ->get();

            $pendingRequestCount = (clone $departmentRequests)
                ->where('status', ServiceRequest::STATUS_PENDING)
                ->count();

            $inProgressRequestCount = (clone $departmentRequests)
                ->where('status', ServiceRequest::STATUS_IN_PROGRESS)
                ->count();

            $completedRequestCount = (clone $departmentRequests)
                ->where('status', ServiceRequest::STATUS_COMPLETED)
                ->count();
        }

        return view('dashboards.staff', [
            'department' => $staff->department,
            'serviceRequests' => $serviceRequests,
            'recentRequests' => $serviceRequests->take(3),
            'pendingRequestCount' => $pendingRequestCount,
            'inProgressRequestCount' => $inProgressRequestCount,
            'completedRequestCount' => $completedRequestCount,
            'filteredRequestCount' => $serviceRequests->count(),
            'selectedStatus' => $selectedStatus,
        ]);
    }

    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        $serviceRequest->loadMissing(['user', 'department', 'serviceCategory']);

        abort_unless($serviceRequest->canBeManagedBy($request->user()), 403);

        return view('staff.requests.show', [
            'serviceRequest' => $serviceRequest,
            'statuses' => ServiceRequest::statuses(),
        ]);
    }

    public function update(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $serviceRequest->loadMissing(['department', 'serviceCategory']);

        abort_unless($serviceRequest->canBeManagedBy($request->user()), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(ServiceRequest::statuses())],
            'staff_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $serviceRequest->update($validated);

        return redirect()
            ->route('staff.requests.show', $serviceRequest)
            ->with('status', 'The request was updated successfully.');
    }

    public function download(Request $request, ServiceRequest $serviceRequest): StreamedResponse
    {
        $serviceRequest->loadMissing(['department']);

        abort_unless($serviceRequest->canBeManagedBy($request->user()), 403);
        abort_unless($serviceRequest->attachment_path, 404);
        abort_unless(Storage::exists($serviceRequest->attachment_path), 404);

        return Storage::download(
            $serviceRequest->attachment_path,
            $serviceRequest->attachment_original_name ?? basename($serviceRequest->attachment_path),
        );
    }
}
