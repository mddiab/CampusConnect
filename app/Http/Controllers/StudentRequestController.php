<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', Rule::in(ServiceRequest::departments())],
            'category' => ['required', 'string', Rule::in(ServiceRequest::categories())],
            'description' => ['required', 'string', 'max:5000'],
            'is_urgent' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:2048'],
        ]);

        $attachmentPath = null;
        $attachmentOriginalName = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('service-requests');
            $attachmentOriginalName = $request->file('attachment')->getClientOriginalName();
        }

        $serviceRequest = $request->user()->serviceRequests()->create([
            'title' => $validated['title'],
            'department' => $validated['department'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'status' => ServiceRequest::STATUS_PENDING,
            'is_urgent' => $validated['is_urgent'] ?? false,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
        ]);

        return redirect()
            ->route('student.requests.show', $serviceRequest)
            ->with('status', 'Your request was submitted successfully.');
    }

    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        // Verify the request belongs to the authenticated user
        abort_unless($serviceRequest->user_id === $request->user()->id, 403);

        // FEATURE: Request History & Archiving - Mark first view time for completed requests
        // This triggers the 24-hour archiving timer
        if ($serviceRequest->status === ServiceRequest::STATUS_COMPLETED && $serviceRequest->first_completed_view_at === null) {
            $serviceRequest->update(['first_completed_view_at' => now()]);
        }

        // FEATURE: Staff Communication & Notes - Load notes with the staff member who wrote them
        $serviceRequest->load('notes.user');

        return view('students.requests.show', [
            'serviceRequest' => $serviceRequest,
        ]);
    }

    public function download(Request $request, ServiceRequest $serviceRequest): StreamedResponse
    {
        abort_unless($serviceRequest->user_id === $request->user()->id, 403);
        abort_unless($serviceRequest->attachment_path, 404);
        abort_unless(Storage::exists($serviceRequest->attachment_path), 404);

        return Storage::download(
            $serviceRequest->attachment_path,
            $serviceRequest->attachment_original_name ?? basename($serviceRequest->attachment_path),
        );
    }

    /**
     * Show the edit form for a student's service request
     * Only allows editing if:
     * - User owns the request
     * - Request status is still "pending"
     */
    public function edit(Request $request, ServiceRequest $serviceRequest): View
    {
        // Verify the request belongs to the authenticated user
        abort_unless($serviceRequest->user_id === $request->user()->id, 403);

        // Prevent editing if request has already been picked up by staff
        abort_unless($serviceRequest->status === ServiceRequest::STATUS_PENDING, 403);

        return view('students.requests.edit', [
            'serviceRequest' => $serviceRequest,
            'departments' => ServiceRequest::departments(),
            'categories' => ServiceRequest::categories(),
        ]);
    }

    /**
     * Update a student's service request
     * Only allows updates if:
     * - User owns the request
     * - Request status is still "pending"
     */
    public function update(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        // Verify the request belongs to the authenticated user
        abort_unless($serviceRequest->user_id === $request->user()->id, 403);

        // Prevent updates if request has already been picked up by staff
        abort_unless($serviceRequest->status === ServiceRequest::STATUS_PENDING, 403);

        // Validate the updated request data
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'is_urgent' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:2048'],
        ]);

        // Handle new attachment if uploaded
        if ($request->hasFile('attachment')) {
            // Delete old attachment if it exists
            if ($serviceRequest->attachment_path) {
                Storage::delete($serviceRequest->attachment_path);
            }

            // Store new attachment
            $attachmentPath = $request->file('attachment')->store('service-requests');
            $attachmentOriginalName = $request->file('attachment')->getClientOriginalName();

            $serviceRequest->update([
                'attachment_path' => $attachmentPath,
                'attachment_original_name' => $attachmentOriginalName,
            ]);
        }

        // Update title, description, and urgent status
        $serviceRequest->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_urgent' => $validated['is_urgent'] ?? false,
        ]);

        return redirect()
            ->route('student.requests.show', $serviceRequest)
            ->with('status', 'Your request has been updated successfully.');
    }
}
