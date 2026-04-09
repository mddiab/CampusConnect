<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\ServiceCategory;
use Illuminate\Database\Query\Builder;
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
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'service_category_id' => [
                'required',
                'integer',
                Rule::exists('service_categories', 'id')->where(
                    fn (Builder $query) => $query->where('department_id', $request->integer('department_id')),
                ),
            ],
            'description' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:2048'],
        ]);

        $serviceCategory = ServiceCategory::query()
            ->with('department')
            ->findOrFail($validated['service_category_id']);

        $attachmentPath = null;
        $attachmentOriginalName = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('service-requests');
            $attachmentOriginalName = $request->file('attachment')->getClientOriginalName();
        }

        $serviceRequest = $request->user()->serviceRequests()->create([
            'title' => $validated['title'],
            'department_id' => $serviceCategory->department_id,
            'service_category_id' => $serviceCategory->id,
            'description' => $validated['description'],
            'status' => ServiceRequest::STATUS_PENDING,
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
        ]);

        return redirect()
            ->route('student.requests.show', $serviceRequest)
            ->with('status', 'Your request was submitted successfully.');
    }

    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        abort_unless($serviceRequest->user_id === $request->user()->id, 403);

        $serviceRequest->loadMissing(['user', 'department', 'serviceCategory']);

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
}
