@extends('layouts.app')

@php
    $statusClasses = [
        'pending' => 'status-pending',
        'in_progress' => 'status-in-progress',
        'completed' => 'status-completed',
    ];
@endphp

@section('title', 'Admin Reports')

@section('content')
<main class="page">
    <div class="container">
        <div class="reports-main">
            <section class="hero-card">
                <div class="hero-header-actions">
                    <h1>Reports</h1>
                    <a href="{{ route('admin.dashboard') }}" class="button button-plain btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <p>
                    Review request volume by status and department, filter the full request list,
                    and export the current result set as a CSV file.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
                        <strong>{{ $totalRequests }}</strong>
                        <span>Total requests in the current filter.</span>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-clock-rotate-left"></i></div>
                        <strong>{{ $pendingRequestCount }}</strong>
                        <span>Pending requests awaiting staff action.</span>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                        <strong>{{ $inProgressRequestCount + $completedRequestCount }}</strong>
                        <span>Requests already being handled or completed.</span>
                    </div>
                </div>
            </section>

            <section class="section-stack">
                <article class="panel breakdown-card">
                    <div>
                        <h2>Department Breakdown</h2>
                        <p class="section-note">
                            Departments are ordered by current request volume.
                        </p>
                    </div>

                    <ul class="breakdown-list">
                        @foreach ($departmentBreakdown as $department)
                            <li class="breakdown-item">
                                <div class="breakdown-name-wrap">
                                    <i class="fas fa-building-user"></i>
                                    <strong>{{ $department->name }}</strong>
                                </div>
                                <span class="role-badge">{{ $department->service_requests_count }} requests</span>
                            </li>
                        @endforeach
                    </ul>
                </article>

                <article class="panel report-panel-stack">
                    <div class="panel-header">
                        <h2>Request Filters</h2>
                    </div>

                    <p class="report-intro">
                        Narrow the request list by department and workflow status, then export exactly what is currently displayed.
                    </p>

                    <form method="GET" action="{{ route('admin.reports') }}" class="report-toolbar" data-preserve-scroll>
                        <div class="report-field-row">
                            <select name="department_id" class="report-select">
                                <option value="">All Departments</option>
                                @foreach ($allDepartments as $department)
                                    <option value="{{ $department->id }}" @selected((string) $selectedDepartmentId === (string) $department->id)>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="status" class="report-select">
                                <option value="all" @selected($selectedStatus === 'all')>All Statuses</option>
                                <option value="pending" @selected($selectedStatus === 'pending')>Pending</option>
                                <option value="in_progress" @selected($selectedStatus === 'in_progress')>In Progress</option>
                                <option value="completed" @selected($selectedStatus === 'completed')>Completed</option>
                            </select>
                        </div>

                        <div class="report-action-row">
                            <button type="submit" class="button button-secondary">Apply Filters</button>
                            <a href="{{ route('admin.reports') }}" class="button button-plain" data-preserve-scroll>Reset</a>
                            <a href="{{ route('admin.reports.export', request()->query()) }}" class="button button-primary">Export CSV</a>
                        </div>
                    </form>

                    <div class="report-table-block">
                        <div class="table-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th class="title-column">Title</th>
                                        <th class="student-column">Student</th>
                                        <th class="department-column">Department</th>
                                        <th class="category-column">Category</th>
                                        <th class="status-column">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($serviceRequests as $serviceRequest)
                                        <tr>
                                            <td class="title-column">{{ $serviceRequest->title }}</td>
                                            <td class="student-column">{{ $serviceRequest->user?->name ?? 'Unknown' }}</td>
                                            <td class="department-column">{{ $serviceRequest->departmentName() }}</td>
                                            <td class="category-column">{{ $serviceRequest->categoryName() }}</td>
                                            <td class="status-column">
                                                <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                                    {{ $serviceRequest->statusLabel() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="empty-state">
                                                <i class="fas fa-magnifying-glass empty-state-ghost"></i>
                                                No requests matched the current filters.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-wrap">
                            {{ $serviceRequests->links('pagination.galaxy') }}
                        </div>
                    </div>
                </article>
            </section>
        </div>
    </div>
</main>
@endsection
