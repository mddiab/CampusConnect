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
<style>
    .report-toolbar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 18px;
    }

    .report-select {
        min-height: 44px;
        padding: 0 14px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: var(--bg-field);
        color: var(--text);
        min-width: 220px;
    }

    .breakdown-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 10px;
    }

    .breakdown-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: rgba(10, 17, 42, 0.74);
    }

    .pagination-wrap nav {
        margin-top: 18px;
    }
</style>

<main class="page">
    <div class="container">
        <section class="hero-card">
            <h1>Reports</h1>
            <p>
                Review request volume by status and department, filter the full request list,
                and export the current result set as a CSV file.
            </p>

            <div class="stat-row">
                <div class="stat-box">
                    <strong>{{ $totalRequests }}</strong>
                    <span>Total requests in the current filter.</span>
                </div>
                <div class="stat-box">
                    <strong>{{ $pendingRequestCount }}</strong>
                    <span>Pending requests awaiting staff action.</span>
                </div>
                <div class="stat-box">
                    <strong>{{ $inProgressRequestCount + $completedRequestCount }}</strong>
                    <span>Requests already being handled or completed.</span>
                </div>
            </div>
        </section>

        <section class="panel-grid">
            <article class="panel">
                <div class="panel-header">
                    <h2>Request Filters</h2>
                    <a href="{{ route('admin.dashboard') }}" class="text-link">Back to Dashboard</a>
                </div>

                <form method="GET" action="{{ route('admin.reports') }}" class="report-toolbar">
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

                    <button type="submit" class="button button-secondary">Apply Filters</button>
                    <a href="{{ route('admin.reports') }}" class="button button-plain">Reset</a>
                    <a href="{{ route('admin.reports.export', request()->query()) }}" class="button button-primary">Export CSV</a>
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Student</th>
                                <th>Department</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($serviceRequests as $serviceRequest)
                                <tr>
                                    <td>{{ $serviceRequest->title }}</td>
                                    <td>{{ $serviceRequest->user?->name ?? 'Unknown' }}</td>
                                    <td>{{ $serviceRequest->departmentName() }}</td>
                                    <td>{{ $serviceRequest->categoryName() }}</td>
                                    <td>
                                        <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                            {{ $serviceRequest->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $serviceRequest->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-state">No requests matched the current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrap">
                    {{ $serviceRequests->links('pagination.galaxy') }}
                </div>
            </article>

            <article class="panel">
                <h2>Department Breakdown</h2>
                <p class="section-note">
                    Departments are ordered by current request volume.
                </p>

                <ul class="breakdown-list">
                    @foreach ($departmentBreakdown as $department)
                        <li class="breakdown-item">
                            <strong>{{ $department->name }}</strong>
                            <span class="role-badge">{{ $department->service_requests_count }} requests</span>
                        </li>
                    @endforeach
                </ul>
            </article>
        </section>
    </div>
</main>
@endsection
