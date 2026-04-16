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

                    @if ($archivedRequestCount > 0)
                        <div class="stat-box">
                            <strong>{{ $archivedRequestCount }}</strong>
                            <span>Archived requests from past submissions.</span>
                        </div>
                    @endif
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

                        <div class="form-group" style="display: flex; align-items: center; gap: 12px;">
                            <input
                                type="checkbox"
                                id="is_urgent"
                                name="is_urgent"
                                value="1"
                                {{ old('is_urgent') ? 'checked' : '' }}
                            >
                            <label for="is_urgent" style="margin: 0; cursor: pointer;">
                                <strong>🚨 Mark as Urgent</strong> - Check this if your request needs priority attention
                            </label>
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

                <!-- Search & Filter Form (Feature 4: Request Search & Filtering) -->
                <form method="GET" action="{{ route('student.dashboard') }}" style="margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 16px;">
                        <!-- Search Input -->
                        <div class="form-group" style="margin: 0;">
                            <label for="search" style="display: block; font-size: 14px; margin-bottom: 4px;">Search</label>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search title or description..."
                                style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                            >
                        </div>

                        <!-- Department Filter -->
                        <div class="form-group" style="margin: 0;">
                            <label for="department" style="display: block; font-size: 14px; margin-bottom: 4px;">Department</label>
                            <select
                                id="department"
                                name="department_id"
                                style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                            >
                                <option value="">All Departments</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Category Filter -->
                        <div class="form-group" style="margin: 0;">
                            <label for="category" style="display: block; font-size: 14px; margin-bottom: 4px;">Category</label>
                            <select
                                id="category"
                                name="service_category_id"
                                style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                            >
                                <option value="">All Categories</option>
                                @if (request('department_id'))
                                    @foreach ($categoriesByDepartment[request('department_id')] ?? [] as $category)
                                        <option value="{{ $category['id'] }}" @selected(request('service_category_id') == $category['id'])>
                                            {{ $category['name'] }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="form-group" style="margin: 0;">
                            <label for="status" style="display: block; font-size: 14px; margin-bottom: 4px;">Status</label>
                            <select
                                id="status"
                                name="status"
                                style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                            >
                                <option value="">All Statuses</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                            </select>
                        </div>

                        <!-- Sort Options -->
                        <div class="form-group" style="margin: 0;">
                            <label for="sort" style="display: block; font-size: 14px; margin-bottom: 4px;">Sort By</label>
                            <select
                                id="sort"
                                name="sort"
                                style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                            >
                                <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest First</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest First</option>
                                <option value="urgent" @selected(request('sort') === 'urgent')>Urgent First</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="button button-primary" style="padding: 8px 16px; font-size: 14px;">
                            Search & Filter
                        </button>
                        <a href="{{ route('student.dashboard') }}" class="button" style="padding: 8px 16px; font-size: 14px; background-color: #f3f4f6; text-decoration: none; border-radius: 4px; display: inline-block;">
                            Clear Filters
                        </a>
                    </div>
                </form>

                @if (request('search') || request('department_id') || request('service_category_id') || request('status') || request('sort'))
                    <p style="font-size: 14px; color: #666; margin-bottom: 16px;">
                        Showing {{ $activeRequests->count() }} matched request(s)
                        @if (request('search')) matching "{{ request('search') }}"@endif
                    </p>
                @endif

                <p class="section-note">
                    This table shows all requests submitted by the logged-in student, from newest to oldest.
                </p>

                @if ($activeRequests->isEmpty() && $archivedRequests->isEmpty())
                    <div class="empty-state">
                        No requests have been submitted yet. Once you send your first request, it will appear here with
                        its department, category, status, submission date, and a link to the full details page.
                    </div>
                @else
                    @if ($activeRequests->isEmpty())
                        <div class="empty-state" style="margin-bottom: 20px;">
                            No active requests match your search. Try adjusting your filters.
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
                                        <th>Priority</th>
                                        <th>Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activeRequests as $serviceRequest)
                                        <tr>
                                            <td>{{ $serviceRequest->title }}</td>
                                            <td>{{ $serviceRequest->departmentName() }}</td>
                                            <td>{{ $serviceRequest->categoryName() }}</td>
                                            <td>
                                                <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                                    {{ $serviceRequest->statusLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($serviceRequest->is_urgent)
                                                    <span style="color: #dc2626; font-weight: bold;">🚨 Urgent</span>
                                                @else
                                                    <span style="color: #059669;">Normal</span>
                                                @endif
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

                    @if ($archivedRequests->isNotEmpty())
                        <!-- Archived Requests Section (Feature 7: Request History & Archiving) -->
                        <section style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                            <h3 style="margin-bottom: 12px;">📦 Archived Requests ({{ $archivedRequests->count() }})</h3>
                            <p style="font-size: 14px; color: #666; margin-bottom: 16px;">
                                These requests were automatically archived 24 hours after you first viewed them when they were in completed status.
                            </p>

                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Department</th>
                                            <th>Category</th>
                                            <th>Completed Date</th>
                                            <th>Archived Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($archivedRequests as $serviceRequest)
                                            <tr style="opacity: 0.75;">
                                                <td>{{ $serviceRequest->title }}</td>
                                                <td>{{ $serviceRequest->departmentName() }}</td>
                                                <td>{{ $serviceRequest->categoryName() }}</td>
                                                <td>{{ $serviceRequest->resolved_at?->format('M d, Y') ?? 'N/A' }}</td>
                                                <td>{{ $serviceRequest->archived_at?->format('M d, Y') ?? 'N/A' }}</td>
                                                <td>
                                                    <a href="{{ route('student.requests.show', $serviceRequest) }}" class="text-link">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endif
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
