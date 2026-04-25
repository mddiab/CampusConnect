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
        $selectedPriority = $request->string('priority')->toString();
        $searchTerm = trim($request->string('search')->toString());

        if (! in_array($selectedStatus, ['all', ...ServiceRequest::statuses()], true)) {
            $selectedStatus = 'all';
        }

        if (! in_array($selectedPriority, ['all', 'urgent', 'standard'], true)) {
            $selectedPriority = 'all';
        }

        $serviceRequests = null;
        $recentRequests = collect();
        $pendingRequestCount = 0;
        $inProgressRequestCount = 0;
        $completedRequestCount = 0;
        $urgentRequestCount = 0;
        $filteredRequestCount = 0;

        if ($staff->department_id) {
            $departmentRequests = ServiceRequest::query()
                ->with(['user', 'department', 'serviceCategory'])
                ->notArchived()
                ->forDepartment($staff->department_id);

            $filteredRequestQuery = (clone $departmentRequests)
                ->when(
                    $selectedStatus !== 'all',
                    fn (Builder $query) => $query->where('status', $selectedStatus),
                )
                ->when(
                    $selectedPriority === 'urgent',
                    fn (Builder $query) => $query->where('is_urgent', true),
                )
                ->when(
                    $selectedPriority === 'standard',
                    fn (Builder $query) => $query->where(function (Builder $priorityQuery): void {
                        $priorityQuery
                            ->where('is_urgent', false)
                            ->orWhereNull('is_urgent');
                    }),
                )
                ->when(
                    $searchTerm !== '',
                    function (Builder $query) use ($searchTerm): void {
                        $query->where(function (Builder $searchQuery) use ($searchTerm): void {
                            $searchQuery
                                ->where('title', 'like', "%{$searchTerm}%")
                                ->orWhere('description', 'like', "%{$searchTerm}%")
                                ->orWhereHas('user', function (Builder $userQuery) use ($searchTerm): void {
                                    $userQuery
                                        ->where('name', 'like', "%{$searchTerm}%")
                                        ->orWhere('email', 'like', "%{$searchTerm}%");
                                })
                                ->orWhereHas('serviceCategory', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', "%{$searchTerm}%"));
                        });
                    },
                );

            $filteredRequests = (clone $filteredRequestQuery)
                ->latest()
                ->paginate(12)
                ->withQueryString();

            $recentRequests = (clone $filteredRequestQuery)
                ->latest()
                ->take(4)
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

            $urgentRequestCount = (clone $departmentRequests)
                ->where('is_urgent', true)
                ->count();

            $filteredRequestCount = $filteredRequests->total();
            $serviceRequests = $filteredRequests;
        }

        return view('dashboards.staff', [
            'department' => $staff->department,
            'serviceRequests' => $serviceRequests,
            'recentRequests' => $recentRequests,
            'pendingRequestCount' => $pendingRequestCount,
            'inProgressRequestCount' => $inProgressRequestCount,
            'completedRequestCount' => $completedRequestCount,
            'urgentRequestCount' => $urgentRequestCount,
            'filteredRequestCount' => $filteredRequestCount,
            'selectedStatus' => $selectedStatus,
            'selectedPriority' => $selectedPriority,
            'searchTerm' => $searchTerm,
        ]);
    }

    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        abort_unless($serviceRequest->canBeManagedBy($request->user()), 403);
        abort_if($serviceRequest->isArchived(), 403);

        $serviceRequest->loadMissing([
            'user',
            'department',
            'serviceCategory',
            'messages' => fn ($query) => $query->oldest(),
        ]);

        $departmentRequests = ServiceRequest::query()
            ->with(['user', 'serviceCategory'])
            ->notArchived()
            ->forDepartment($serviceRequest->department_id);

        return view('staff.requests.show', [
            'serviceRequest' => $serviceRequest,
            'statuses' => ServiceRequest::statuses(),
            'relatedRequests' => (clone $departmentRequests)
                ->whereKeyNot($serviceRequest->id)
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }

    public function storeMessage(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        abort_unless($serviceRequest->canBeManagedBy($request->user()), 403);
        abort_if($serviceRequest->isArchived(), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $serviceRequest->addMessageFrom($request->user(), $validated['message']);

        return redirect()
            ->route('staff.requests.show', $serviceRequest)
            ->with('status', 'Your reply was added to the request conversation.');
    }

    public function update(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $serviceRequest->loadMissing(['department', 'serviceCategory']);

        abort_unless($serviceRequest->canBeManagedBy($request->user()), 403);
        abort_if($serviceRequest->isArchived(), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(ServiceRequest::statuses())],
            'is_urgent' => ['required', 'boolean'],
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
