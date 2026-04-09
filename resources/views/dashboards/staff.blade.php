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
                    <h1>{{ $department->name }} Queue</h1>
                    <p>
                        Review, update, and close requests for <strong>{{ $department->name }}</strong>.
                    </p>

                    <div class="stat-row">
                        <div class="stat-box">
                            <span class="stat-kicker">Pending</span>
                            <strong>{{ $pendingRequestCount }}</strong>
                            <span>New requests</span>
                        </div>

                        <div class="stat-box">
                            <span class="stat-kicker">In Progress</span>
                            <strong>{{ $inProgressRequestCount }}</strong>
                            <span>Active work</span>
                        </div>

                        <div class="stat-box">
                            <span class="stat-kicker">Completed</span>
                            <strong>{{ $completedRequestCount }}</strong>
                            <span>Closed requests</span>
                        </div>
                    </div>
                </section>

                <section class="page-grid">
                    <article class="mini-card">
                        <h2>Recent</h2>
                        @if ($recentRequests->isEmpty())
                            <p>No requests have been assigned yet.</p>
                        @else
                            <ul class="compact-list">
                                @foreach ($recentRequests as $serviceRequest)
                                    <li>
                                        <span class="list-title">{{ $serviceRequest->title }}</span>
                                        <span class="list-meta">{{ $serviceRequest->user->name }} • {{ $serviceRequest->categoryName() }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>

                    <article class="mini-card">
                        <h2>Status Guide</h2>
                        <div class="status-guide">
                            <div class="status-guide-row">
                                <span class="status-badge status-pending">Pending</span>
                                <span>New and waiting.</span>
                            </div>
                            <div class="status-guide-row">
                                <span class="status-badge status-in-progress">In Progress</span>
                                <span>Being handled now.</span>
                            </div>
                            <div class="status-guide-row">
                                <span class="status-badge status-completed">Completed</span>
                                <span>Closed with final notes.</span>
                            </div>
                        </div>
                    </article>

                    <article class="mini-card">
                        <h2>Actions</h2>
                        <ul>
                            <li>Open a request.</li>
                            <li>Update status and notes.</li>
                            <li>Download attachments if needed.</li>
                        </ul>
                    </article>
                </section>

                <section class="panel" style="margin-top: 22px;">
                    <div class="panel-header">
                        <h2>Request Queue</h2>
                        <span>{{ $filteredRequestCount }} requests</span>
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
                                        <th>Request</th>
                                        <th>Student</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Open</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($serviceRequests as $serviceRequest)
                                        <tr>
                                            <td><span class="list-title">{{ $serviceRequest->title }}</span></td>
                                            <td>{{ $serviceRequest->user->name }}</td>
                                            <td>{{ $serviceRequest->categoryName() }}</td>
                                            <td>
                                                <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                                    {{ $serviceRequest->statusLabel() }}
                                                </span>
                                            </td>
                                            <td>{{ $serviceRequest->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('staff.requests.show', $serviceRequest) }}" class="text-link">Open</a>
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
                        Your account does not have a department yet. An administrator must assign one before you can review requests.
                    </p>
                </section>
            @endif
        </div>
    </main>
@endsection
