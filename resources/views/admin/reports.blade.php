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
    .reports-main {
        display: grid;
        gap: 22px;
    }

    .report-panel-stack {
        display: grid;
        gap: 18px;
    }

    .report-intro {
        margin: 0;
        color: var(--muted);
        line-height: 1.75;
    }

    .report-toolbar {
        display: grid;
        gap: 14px;
        padding: 18px;
        margin: 0;
        border: 1px solid var(--line);
        border-radius: 18px;
        background: linear-gradient(180deg, #fcfaff, #f5eefb);
    }

    .report-field-row,
    .report-action-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .report-action-row {
        justify-content: flex-start;
        padding-top: 4px;
        border-top: 1px solid rgba(95, 67, 167, 0.08);
    }

    .report-select {
        min-height: 44px;
        padding: 0 14px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: var(--bg-field);
        color: var(--text);
        min-width: 220px;
        flex: 1 1 220px;
    }

    .report-table-block {
        display: grid;
        gap: 16px;
    }

    .report-table {
        width: 100%;
        table-layout: auto;
    }

    .report-table th,
    .report-table td {
        vertical-align: top;
    }

    .report-table .title-column {
        width: 33%;
    }

    .report-table .student-column {
        width: 18%;
    }

    .report-table .department-column {
        width: 20%;
    }

    .report-table .category-column {
        width: 17%;
    }

    .report-table .status-column {
        width: 160px;
        min-width: 160px;
        white-space: nowrap;
    }

    .report-table td.status-column,
    .report-table th.status-column {
        padding-right: 24px;
    }

    .breakdown-card {
        display: grid;
        gap: 16px;
    }

    .breakdown-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 10px;
    }

    .stat-box {
        display: grid;
        gap: 4px;
        position: relative;
        padding: 24px;
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(249, 244, 253, 0.96));
        border: 1px solid rgba(95, 67, 167, 0.12);
        transition: transform 220ms ease, border-color 220ms ease;
    }

    .stat-box:hover {
        transform: translateY(-4px);
        border-color: rgba(95, 67, 167, 0.24);
    }

    .stat-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(95, 67, 167, 0.08), rgba(142, 181, 232, 0.08));
        color: var(--primary);
        font-size: 1.3rem;
        border: 1px solid rgba(95, 67, 167, 0.1);
    }

    .stat-box strong {
        font-size: 2.2rem;
        margin-bottom: 2px;
        background: linear-gradient(135deg, var(--text), var(--primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .stat-box span {
        color: var(--muted);
        font-size: 0.94rem;
        line-height: 1.5;
        max-width: 22ch;
    }

    .breakdown-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.96);
        transition: transform 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
    }

    .breakdown-item:hover {
        transform: translateX(4px);
        border-color: var(--primary);
        box-shadow: 0 8px 24px rgba(63, 40, 111, 0.06);
    }

    .breakdown-name-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .breakdown-name-wrap i {
        color: var(--primary);
        font-size: 1.1rem;
        opacity: 0.8;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px !important;
        color: var(--muted);
    }

    .empty-state-ghost {
        display: block;
        font-size: 2.5rem;
        margin-bottom: 14px;
        opacity: 0.12;
        color: var(--primary);
    }

    .report-table tr {
        transition: background 180ms ease;
    }

    .report-table tbody tr:hover {
        background: rgba(95, 67, 167, 0.02);
    }

    .button i {
        margin-right: 8px;
    }

    .pagination-wrap nav {
        margin-top: 0;
    }

    @media (max-width: 720px) {
        .report-toolbar {
            padding: 16px;
        }

        .report-field-row,
        .report-action-row {
            flex-direction: column;
            align-items: stretch;
        }

        .stat-row {
            grid-template-columns: 1fr;
        }

        .stat-icon {
            position: static;
            margin-bottom: 12px;
        }
    }

    .hero-header-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .hero-header-actions h1 {
        margin: 0;
    }
</style>

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

                    <form method="GET" action="{{ route('admin.reports') }}" class="report-toolbar">
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
                            <a href="{{ route('admin.reports') }}" class="button button-plain">Reset</a>
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
