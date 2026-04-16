@extends('layouts.app')

@section('content')
    <main class="page">
        <div class="container">
            @if ($errors->any())
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="hero-card">
                <h1>Edit Request: {{ $serviceRequest->title }}</h1>
                <p>Update the details of your pending request below.</p>
            </section>

            <section class="panel" style="margin-top: 22px;">
                <h2>Edit Request Details</h2>
                <p class="section-note">
                    You can only edit requests that are in "Pending" status. Department and Category cannot be changed.
                </p>

                <form method="POST" action="{{ route('student.requests.update', $serviceRequest) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label for="title">Request Title</label>
                        <input
                            id="title"
                            type="text"
                            name="title"
                            value="{{ old('title', $serviceRequest->title) }}"
                            placeholder="Write a short title that clearly identifies the service request"
                            required
                        >
                        <span class="field-help">Brief summary of your request.</span>
                    </div>

                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" disabled>
                            <option>{{ $serviceRequest->department->name }}</option>
                        </select>
                        <span class="field-help">Cannot be changed after submission.</span>
                    </div>

                    <div class="form-group">
                        <label for="service_category_id">Category</label>
                        <select id="service_category_id" disabled>
                            <option>{{ $serviceRequest->serviceCategory->name }}</option>
                        </select>
                        <span class="field-help">Cannot be changed after submission.</span>
                    </div>

                    <div class="form-group">
                        <label for="description">Request Description</label>
                        <textarea
                            id="description"
                            name="description"
                            placeholder="Explain what happened, where it happened, when it happened, and what support or action you need from the department"
                            required
                        >{{ old('description', $serviceRequest->description) }}</textarea>
                        <span class="field-help">Include enough detail so the department can understand and act on the request.</span>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 12px;">
                        <input
                            type="checkbox"
                            id="is_urgent"
                            name="is_urgent"
                            value="1"
                            {{ old('is_urgent', $serviceRequest->is_urgent) ? 'checked' : '' }}
                        >
                        <label for="is_urgent" style="margin: 0; cursor: pointer;">
                            <strong>🚨 Mark as Urgent</strong> - Check this if your request needs priority attention
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Attachment</label>
                        <input id="attachment" type="file" name="attachment">
                        <span class="field-help">
                            @if ($serviceRequest->attachment_path)
                                Upload a new file to replace the current attachment ({{ $serviceRequest->attachment_original_name ?? 'file' }})
                            @else
                                Upload a supporting image, PDF, or document if it helps explain the request.
                            @endif
                        </span>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('student.requests.show', $serviceRequest) }}" class="button">Cancel</a>
                        <button type="submit" class="button button-primary">Save Changes</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
@endsection
