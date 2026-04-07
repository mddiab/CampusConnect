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
                    Submit a new request, follow its status, and review your own request history from one place.
                    The summary cards below separate requests by their current stage so the information is easier to scan.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <strong>{{ $pendingRequestCount }}</strong>
                        <span>Pending requests waiting for staff review.</span>
                    </div>

                    <div class="stat-box">
                        <strong>{{ $inProgressRequestCount }}</strong>
                        <span>Requests currently being handled by the department.</span>
                    </div>

                    <div class="stat-box">
                        <strong>{{ $completedRequestCount }}</strong>
                        <span>Requests marked completed by the department.</span>
                    </div>

                    <!-- FEATURE: Request History & Archiving - Show archived count -->
                    <div class="stat-box">
                        <strong>{{ $archivedCount }}</strong>
                        <span>Requests moved to archive after 24 hours of completion.</span>
                    </div>
                </div>
            </section>

            <section class="page-grid">
                <article class="mini-card">
                    <h2>Student Tools</h2>
                    <ul>
                        <li><a href="#request-form" class="text-link">Submit a new service request.</a></li>
                        <li><a href="#request-history" class="text-link">Review all previously submitted requests.</a></li>
                        <li>Open any request from the table to check its full details and status.</li>
                    </ul>
                </article>

                <article class="mini-card">
                    <h2>Latest Activity</h2>

                    @if ($recentRequests->isEmpty())
                        <p>No recent activity yet. Once you submit a request, the latest changes will appear here.</p>
                    @else
                        <ul>
                            @foreach ($recentRequests as $serviceRequest)
                                <li>
                                    <strong>{{ $serviceRequest->title }}</strong><br>
                                    Current status: {{ $serviceRequest->statusLabel() }}. Last updated
                                    {{ $serviceRequest->updated_at->diffForHumans() }}.
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>

                <article class="mini-card">
                    <h2>Before You Submit</h2>
                    <ul>
                        <li>Use a short title that clearly explains the issue.</li>
                        <li>Select the department and category that best match the request.</li>
                        <li>Include the location, date, and any details staff need to respond.</li>
                    </ul>
                </article>
            </section>

            <section class="panel-grid" style="margin-top: 22px;">
                <article class="panel" id="request-form">
                    <h2>Submit a New Request</h2>
                    <p class="section-note">
                        Fill in the details below so the correct department can review and process your request.
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
                            <label for="department">Department</label>
                            <select id="department" name="department" required>
                                <option value="">Select the department that should handle this request</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department }}" @selected(old('department') === $department)>{{ $department }}</option>
                                @endforeach
                            </select>
                            <span class="field-help">Choose the office or department responsible for this issue.</span>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="">Select the category that best matches the request</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                            <span class="field-help">Choose the type of help or service being requested.</span>
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

                        <!-- FEATURE: Priority Levels - Urgent checkbox -->
                        <div class="form-group">
                            <label style="display: flex; align-items: center; font-weight: normal;">
                                <input 
                                    type="checkbox" 
                                    name="is_urgent" 
                                    value="1"
                                    @checked(old('is_urgent'))
                                    style="margin-right: 8px; width: 18px; height: 18px; cursor: pointer;"
                                />
                                🚨 <strong style="margin-left: 5px;">Mark this request as URGENT</strong>
                            </label>
                            <span class="field-help">Check this if your request needs immediate attention from the department.</span>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="button button-primary">Submit Request</button>
                            <span class="muted-text">Your request will be saved with a default status of Pending.</span>
                        </div>
                    </form>
                </article>

                <article class="panel">
                    <h2>Form Guide</h2>
                    <p class="section-note">
                        Keep the request direct and specific so staff can understand it quickly.
                    </p>
                    <ul>
                        <li>The request title should summarize the issue in one short sentence.</li>
                        <li>The department should match the office that is expected to handle the request.</li>
                        <li>The description should include exact details, not only one-word statements like "problem".</li>
                        <li>The attachment is optional and should only be used when a file will help staff understand the issue.</li>
                        <li><strong>Urgent flag:</strong> Check the urgent option only for time-sensitive requests that need priority handling.</li>
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

                <!-- FEATURE: Request Search & Filtering -->
                <div class="search-filter-box" style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
                    <form method="GET" action="{{ route('student.dashboard') }}" class="search-filter-form">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <!-- Search by Title -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="search" style="display: block; margin-bottom: 5px; font-weight: 500;">
                                    🔍 Search by Title
                                </label>
                                <input
                                    type="text"
                                    id="search"
                                    name="search"
                                    placeholder="Search in title or description..."
                                    value="{{ $currentSearch }}"
                                    class="form-control"
                                />
                            </div>

                            <!-- Filter by Department -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="department" style="display: block; margin-bottom: 5px; font-weight: 500;">
                                    📂 Department
                                </label>
                                <select id="department" name="department" class="form-control">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept }}" @selected($currentDepartment === $dept)>
                                            {{ $dept }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter by Category -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="category" style="display: block; margin-bottom: 5px; font-weight: 500;">
                                    🏷️ Category
                                </label>
                                <select id="category" name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}" @selected($currentCategory === $cat)>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter by Status -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="status" style="display: block; margin-bottom: 5px; font-weight: 500;">
                                    📊 Status
                                </label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" @selected($currentStatus === 'pending')>Pending</option>
                                    <option value="in_progress" @selected($currentStatus === 'in_progress')>In Progress</option>
                                    <option value="completed" @selected($currentStatus === 'completed')>Completed</option>
                                </select>
                            </div>

                            <!-- Sort Options -->
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="sort" style="display: block; margin-bottom: 5px; font-weight: 500;">
                                    ⬇️ Sort By
                                </label>
                                <select id="sort" name="sort" class="form-control">
                                    <option value="latest" @selected($currentSort === 'latest')>Newest First</option>
                                    <option value="oldest" @selected($currentSort === 'oldest')>Oldest First</option>
                                    <option value="title" @selected($currentSort === 'title')>Title (A-Z)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filter Actions -->
                        <div style="display: flex; gap: 10px; margin-top: 12px;">
                            <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9em;">
                                🔎 Apply Filters
                            </button>
                            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9em; text-decoration: none; display: inline-block;">
                                ✕ Clear Filters
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Results Summary -->
                @if ($currentSearch || $currentDepartment || $currentCategory || $currentStatus)
                    <div style="margin-bottom: 15px; padding: 10px; background-color: #e7f3ff; border-left: 4px solid #2196F3; border-radius: 4px;">
                        <p style="margin: 0; color: #0c5aa0; font-size: 0.95em;">
                            📋 Found <strong>{{ $serviceRequests->count() }}</strong> request(s) matching your filters
                        </p>
                    </div>
                @endif

                @if ($serviceRequests->isEmpty())
                    <div class="empty-state">
                        @if ($currentSearch || $currentDepartment || $currentCategory || $currentStatus)
                            No requests match your filter criteria. Try adjusting the filters or <a href="{{ route('student.dashboard') }}" class="text-link">clear filters</a> to see all requests.
                        @else
                            No requests have been submitted yet. Once you send your first request, it will appear here with
                            its department, category, status, submission date, and a link to the full details page.
                        @endif
                    </div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Urgent</th>
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
                                        <!-- FEATURE: Priority Levels - Show urgent indicator -->
                                        <td>
                                            @if ($serviceRequest->is_urgent)
                                                <span style="color: #dc3545; font-weight: bold; font-size: 1.2em;">🚨</span>
                                            @else
                                                <span style="color: #999;">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $serviceRequest->department }}</td>
                                        <td>{{ $serviceRequest->category }}</td>
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

            <!-- FEATURE: Request History & Archiving - Archived requests section -->
            <section class="panel" id="archived-history" style="margin-top: 22px;">
                <div class="panel-header">
                    <h2>📦 Archived Requests</h2>
                    <span>{{ $archivedCount }} archived</span>
                </div>
                <p class="section-note">
                    Completed requests are automatically moved here 24 hours after you first view them. This keeps your active list organized.
                </p>

                @if ($archivedRequests->isEmpty())
                    <div class="empty-state">
                        No archived requests yet. Once requests are completed and viewed, they will automatically move here after 24 hours.
                    </div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Category</th>
                                    <th>Completed</th>
                                    <th>Archived On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($archivedRequests as $request)
                                    <tr>
                                        <td>{{ $request->title }}</td>
                                        <td>{{ $request->department }}</td>
                                        <td>{{ $request->category }}</td>
                                        <td>{{ $request->updated_at->format('M d, Y') }}</td>
                                        <td>{{ $request->archived_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('student.requests.show', $request) }}" class="text-link">View</a>
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
@endsection
