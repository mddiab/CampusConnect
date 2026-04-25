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
                        <div class="stat-icon" aria-hidden="true"><i class="fas fa-hourglass-half"></i></div>
                        <strong>{{ $pendingRequestCount }}</strong>
                        <span>Pending requests waiting for department review.</span>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon" aria-hidden="true"><i class="fas fa-arrows-rotate"></i></div>
                        <strong>{{ $inProgressRequestCount }}</strong>
                        <span>Requests currently being handled by staff.</span>
                    </div>

                    <div class="stat-box">
                        <div class="stat-icon" aria-hidden="true"><i class="fas fa-circle-check"></i></div>
                        <strong>{{ $completedRequestCount }}</strong>
                        <span>Requests that have been resolved and marked completed.</span>
                    </div>

                    @if ($archivedRequestCount > 0)
                        <div class="stat-box">
                            <div class="stat-icon" aria-hidden="true"><i class="fas fa-box-archive"></i></div>
                            <strong>{{ $archivedRequestCount }}</strong>
                            <span>Archived requests from past submissions.</span>
                        </div>
                    @endif
                </div>
            </section>

            <section class="dashboard-duo-grid">
                <article class="mini-card">
                    <div class="mini-card-stack">
                        <section class="mini-card-section">
                            <h2>Student Tools</h2>
                            <ul>
                                <li><a href="#request-form" class="text-link">Submit a new service request.</a></li>
                                <li><a href="#request-history" class="text-link">Review all previously submitted requests.</a></li>
                            </ul>
                        </section>

                        <section class="mini-card-section">
                            <h2>Before You Submit</h2>
                            <ul>
                                <li>Choose the department first so the category list only shows valid options for that team.</li>
                                <li>Use a short title that clearly explains the issue.</li>
                                <li>Include location, timing, and any details staff need before they can act.</li>
                            </ul>
                        </section>
                    </div>
                </article>

                <article class="mini-card">
                    <h2>Latest Activity</h2>

                    @if ($recentRequests->isEmpty())
                        <p>No recent activity yet. Once you submit a request, the latest updates will appear here.</p>
                    @else
                        <ul class="compact-list">
                            @foreach ($recentRequests as $serviceRequest)
                                <li>
                                    <a href="{{ route('student.requests.show', $serviceRequest) }}" class="list-title text-link">
                                        {{ $serviceRequest->title }}
                                    </a>
                                    <span class="list-meta">
                                        {{ $serviceRequest->departmentName() }} / {{ $serviceRequest->categoryName() }}
                                    </span>
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                                        <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                            {{ $serviceRequest->statusLabel() }}
                                        </span>
                                        <span class="list-meta" style="margin-top: 0;">
                                            Updated {{ $serviceRequest->updated_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            </section>

            <section style="margin-top: 22px;">
                <article class="panel" id="request-form">
                    <h2>Submit a New Request</h2>

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
                                <option value="">Select a category</option>
                                @foreach ($departments as $department)
                                    @foreach ($department->categories as $category)
                                        <option
                                            value="{{ $category->id }}"
                                            data-department-id="{{ $department->id }}"
                                            @selected((string) old('service_category_id') === (string) $category->id)
                                        >
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @endforeach
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
                                class="urgent-toggle-input"
                                {{ old('is_urgent') ? 'checked' : '' }}
                            >
                            <label for="is_urgent" style="margin: 0; cursor: pointer;">
                                <strong>🚨 Mark as Urgent</strong>
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="attachment">Attachment</label>
                            <input id="attachment" type="file" name="attachment">
                            <span class="field-help">Upload a supporting image, PDF, or document if it helps explain the request.</span>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="button button-primary">Submit Request</button>
                        </div>
                    </form>
                </article>
            </section>

            <section class="panel" id="request-history" style="margin-top: 22px;">
                <div class="panel-header">
                    <h2>My Submitted Requests</h2>
                    <span>{{ $totalRequestCount }} total</span>
                </div>

                <!-- Search & Filter Form (Feature 4: Request Search & Filtering) -->
                <form method="GET" action="{{ route('student.dashboard') }}" style="margin-bottom: 20px;" data-preserve-scroll>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 16px;">
                        <!-- Search Input -->
                        <div class="form-group" style="margin: 0;">
                            <label for="search" style="display: block; font-size: 14px; margin-bottom: 4px;">Search</label>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ $searchTerm }}"
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
                                    <option value="{{ $dept->id }}" @selected((string) $selectedDepartmentId === (string) $dept->id)>
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
                                @if ($selectedDepartmentId)
                                    @foreach ($categoriesByDepartment[$selectedDepartmentId] ?? [] as $category)
                                        <option value="{{ $category['id'] }}" @selected((string) $selectedCategoryId === (string) $category['id'])>
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
                                <option value="pending" @selected($selectedStatus === 'pending')>Pending</option>
                                <option value="in_progress" @selected($selectedStatus === 'in_progress')>In Progress</option>
                                <option value="completed" @selected($selectedStatus === 'completed')>Completed</option>
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
                                <option value="newest" @selected($selectedSort === 'newest' || $selectedSort === 'latest')>Newest First</option>
                                <option value="oldest" @selected($selectedSort === 'oldest')>Oldest First</option>
                                <option value="urgent" @selected($selectedSort === 'urgent')>Urgent First</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="button button-primary" style="padding: 8px 16px; font-size: 14px;">
                            Search & Filter
                        </button>
                        <a href="{{ route('student.dashboard') }}" class="button button-secondary" style="padding: 8px 16px; font-size: 14px;" data-preserve-scroll>
                            Clear Filters
                        </a>
                    </div>
                </form>

                @if ($searchTerm !== '' || $selectedDepartmentId || $selectedCategoryId || ($selectedStatus !== '' && $selectedStatus !== 'all') || $selectedSort !== 'newest')
                    <p style="font-size: 14px; color: #666; margin-bottom: 16px;">
                        Showing {{ $activeRequests->total() }} matched active request(s)
                        @if ($searchTerm !== '') matching "{{ $searchTerm }}"@endif
                    </p>
                @endif

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

                        {{ $activeRequests->links('pagination.galaxy') }}
                    @endif

                    @if ($archivedRequests->isNotEmpty())
                        <!-- Archived Requests Section (Feature 7: Request History & Archiving) -->
                        <section style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                            <h3 style="margin-bottom: 12px;">📦 Archived Requests ({{ $archivedRequests->total() }})</h3>
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

                            {{ $archivedRequests->links('pagination.galaxy') }}
                        </section>
                    @endif
                @endif
            </section>
        </div>
    </main>
@endsection
