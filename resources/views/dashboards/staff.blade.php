@extends('layouts.app')

@section('title', 'Staff Dashboard')

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

            @if ($department)
                <section class="hero-card">
                    <h1>Staff Dashboard</h1>
                    <p>
                        Review and update service requests assigned to the <strong>{{ $department->name }}</strong> department.
                    </p>

                    <div class="stat-row">
                        <div class="stat-box">
                            <strong>{{ $pendingRequestCount }}</strong>
                            <span>Requests waiting for an initial review.</span>
                        </div>

                        <div class="stat-box">
                            <strong>{{ $inProgressRequestCount }}</strong>
                            <span>Requests currently being handled by the department.</span>
                        </div>

                        <div class="stat-box">
                            <strong>{{ $completedRequestCount }}</strong>
                            <span>Requests already resolved by this department.</span>
                        </div>
                    </div>
                </section>

                <section class="page-grid">
                    <article class="mini-card">
                        <h2>Staff Actions</h2>
                        <ul>
                            <li>Review the newest requests assigned to your department.</li>
                            <li>Open any request to update its status and leave staff notes.</li>
                            <li>Download attachments when supporting files are needed for the review.</li>
                        </ul>
                    </article>

                    <article class="mini-card">
                        <h2>Latest Activity</h2>

                        @if ($recentRequests->isEmpty())
                            <p>No requests have been assigned to this department yet.</p>
                        @else
                            <ul>
                                @foreach ($recentRequests as $serviceRequest)
                                    <li>
                                        <strong>{{ $serviceRequest->title }}</strong><br>
                                        {{ $serviceRequest->user->name }} submitted this request under
                                        {{ $serviceRequest->categoryName() }}.
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>

                    <article class="mini-card">
                        <h2>Workflow</h2>
                        <ul>
                            <li>Pending requests are newly submitted and waiting for review.</li>
                            <li>In Progress requests are actively being handled by department staff.</li>
                            <li>Completed requests should include final notes before closure.</li>
                        </ul>
                    </article>
                </section>

                <section class="panel" style="margin-top: 22px;">
                    <div class="panel-header">
                        <h2>Department Request Queue</h2>
                        <span>{{ $filteredRequestCount }} visible</span>
                    </div>

                    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px;">
                        @foreach (['all' => 'All', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $status => $label)
                            <a
                                href="{{ request()->fullUrlWithQuery(['status' => $status]) }}"
                                class="button {{ $selectedStatus === $status ? 'button-primary' : 'button-plain' }}"
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    @if ($serviceRequests->isEmpty())
                        <div class="empty-state">
                            No requests match the current filter for {{ $department->name }}.
                        </div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Student</th>
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
                                            <td>{{ $serviceRequest->user->name }}</td>
                                            <td>{{ $serviceRequest->categoryName() }}</td>
                                            <td>
                                                <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                                    {{ $serviceRequest->statusLabel() }}
                                                </span>
                                            </td>
                                            <td>{{ $serviceRequest->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('staff.requests.show', $serviceRequest) }}" class="text-link">Review Request</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @else
                <section class="hero-card">
                    <h1>Staff Dashboard</h1>
                    <p>
                        Your account does not have a department assignment yet. An administrator needs to assign a department
                        before you can review or update service requests.
                    </p>
                </section>
            @endif
        </div>
    </main>
@endsection
