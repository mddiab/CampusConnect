@extends('layouts.app')

@section('title', 'Student Dashboard')

@php
    $statusClasses = [
        'pending' => 'status-pending',
        'in_progress' => 'status-in-progress',
        'completed' => 'status-completed',
    ];
@endphp

@section('content')
    <main class="page">
        <div class="container">
            @if (session('status'))
                <div class="success-box">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="hero-card">
                <h1>Student Dashboard</h1>
                <p>
                    Submit a new request, track updates from the assigned department, and review your own request history from one place.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <strong>{{ $pendingRequestCount }}</strong>
                        <span>Pending requests waiting for department review.</span>
                    </div>

                    <div class="stat-box">
                        <strong>{{ $inProgressRequestCount }}</strong>
                        <span>Requests currently being handled by staff.</span>
                    </div>

                    <div class="stat-box">
                        <strong>{{ $completedRequestCount }}</strong>
                        <span>Requests that have been resolved and marked completed.</span>
                    </div>
                </div>
            </section>

            <section class="page-grid">
                <article class="mini-card">
                    <h2>Student Tools</h2>
                    <ul>
                        <li><a href="#request-form" class="text-link">Submit a new service request.</a></li>
                        <li><a href="#request-history" class="text-link">Review all previously submitted requests.</a></li>
                        <li>Open any request to read staff notes, status updates, and attachment details.</li>
                    </ul>
                </article>

                <article class="mini-card">
                    <h2>Latest Activity</h2>

                    @if ($recentRequests->isEmpty())
                        <p>No recent activity yet. Once you submit a request, the latest updates will appear here.</p>
                    @else
                        <ul>
                            @foreach ($recentRequests as $serviceRequest)
                                <li>
                                    <strong>{{ $serviceRequest->title }}</strong><br>
                                    {{ $serviceRequest->departmentName() }} /
                                    {{ $serviceRequest->categoryName() }}.
                                    Current status: {{ $serviceRequest->statusLabel() }}.
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>

                <article class="mini-card">
                    <h2>Before You Submit</h2>
                    <ul>
                        <li>Choose the department first so the category list only shows valid options for that team.</li>
                        <li>Use a short title that clearly explains the issue.</li>
                        <li>Include location, timing, and any details staff need before they can act.</li>
                    </ul>
                </article>
            </section>

            <section class="panel-grid" style="margin-top: 22px;">
                <article class="panel" id="request-form">
                    <h2>Submit a New Request</h2>
                    <p class="section-note">
                        Fill in the details below so the correct department and category are assigned to the request.
                    </p>

                    <form method="POST" action="{{ route('student.requests.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="title">Request Title</label>
                            <input
                                id="title"
                                type="text"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="Write a short title that clearly identifies the service request"
                                required
                            >
                            <span class="field-help">Example: Internet connection problem in Building A classroom.</span>
                        </div>

                        <div class="form-group">
                            <label for="department_id">Department</label>
                            <select id="department_id" name="department_id" required>
                                <option value="">Select the department that should handle this request</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="field-help">Choose the office or department responsible for this issue.</span>
                        </div>

                        <div class="form-group">
                            <label for="service_category_id">Category</label>
                            <select
                                id="service_category_id"
                                name="service_category_id"
                                data-selected="{{ old('service_category_id') }}"
                                required
                            >
                                <option value="">Select a department first</option>
                            </select>
                            <span class="field-help">Only categories that belong to the selected department are shown here.</span>
                        </div>

                        <div class="form-group">
                            <label for="description">Request Description</label>
                            <textarea
                                id="description"
                                name="description"
                                placeholder="Explain what happened, where it happened, when it happened, and what support or action you need from the department"
                                required
                            >{{ old('description') }}</textarea>
                            <span class="field-help">Include enough detail so the department can understand and act on the request.</span>
                        </div>

                        <div class="form-group">
                            <label for="attachment">Attachment</label>
                            <input id="attachment" type="file" name="attachment">
                            <span class="field-help">Upload a supporting image, PDF, or document if it helps explain the request.</span>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="button button-primary">Submit Request</button>
                            <span class="muted-text">New requests start with a Pending status.</span>
                        </div>
                    </form>
                </article>

                <article class="panel">
                    <h2>Form Guide</h2>
                    <p class="section-note">
                        Keep the request direct and specific so staff can understand it quickly.
                    </p>
                    <ul>
                        <li>The category list depends on the selected department, so choose the department first.</li>
                        <li>The request title should summarize the issue in one short sentence.</li>
                        <li>The description should include the exact problem, location, and what support is needed.</li>
                        <li>The attachment is optional and should only be used when a file helps staff verify the issue.</li>
                    </ul>
                </article>
            </section>

            <section class="panel" id="request-history" style="margin-top: 22px;">
                <div class="panel-header">
                    <h2>My Submitted Requests</h2>
                    <span>{{ $totalRequestCount }} total</span>
                </div>
                <p class="section-note">
                    This table shows all requests submitted by the logged-in student, from newest to oldest.
                </p>

                @if ($serviceRequests->isEmpty())
                    <div class="empty-state">
                        No requests have been submitted yet. Once you send your first request, it will appear here with
                        its department, category, status, submission date, and a link to the full details page.
                    </div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($serviceRequests as $serviceRequest)
                                    <tr>
                                        <td>{{ $serviceRequest->title }}</td>
                                        <td>{{ $serviceRequest->departmentName() }}</td>
                                        <td>{{ $serviceRequest->categoryName() }}</td>
                                        <td>
                                            <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                                {{ $serviceRequest->statusLabel() }}
                                            </span>
                                        </td>
                                        <td>{{ $serviceRequest->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('student.requests.show', $serviceRequest) }}" class="text-link">View Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </main>

    <script>
        (() => {
            const categoriesByDepartment = @json($categoriesByDepartment);
            const departmentSelect = document.getElementById('department_id');
            const categorySelect = document.getElementById('service_category_id');

            if (!departmentSelect || !categorySelect) {
                return;
            }

            const renderCategories = (selectedCategoryId = categorySelect.dataset.selected || '') => {
                const departmentId = departmentSelect.value;
                const categories = categoriesByDepartment[departmentId] ?? [];

                categorySelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = departmentId
                    ? 'Select the category that best matches the request'
                    : 'Select a department first';

                categorySelect.appendChild(placeholder);

                categories.forEach((category) => {
                    const option = document.createElement('option');
                    option.value = String(category.id);
                    option.textContent = category.name;

                    if (String(category.id) === String(selectedCategoryId)) {
                        option.selected = true;
                    }

                    categorySelect.appendChild(option);
                });

                categorySelect.disabled = categories.length === 0;
            };

            departmentSelect.addEventListener('change', () => renderCategories(''));

            renderCategories();
        })();
    </script>
@endsection
