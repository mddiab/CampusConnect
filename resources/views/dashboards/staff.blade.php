@extends('layouts.app')

@section('title', 'Staff Dashboard')

@php
    $statusClasses = [
        'pending' => 'status-pending',
        'in_progress' => 'status-in-progress',
        'completed' => 'status-completed',
    ];

    $statusOptions = [
        'all' => 'All statuses',
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];

    $priorityOptions = [
        'all' => 'All priorities',
        'urgent' => 'Urgent only',
        'standard' => 'Standard only',
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
                    <p class="eyebrow">Staff Workspace</p>
                    <h1>{{ $department->name }} Operations Desk</h1>
                    <p>
                        Work only on the requests routed to <strong>{{ $department->name }}</strong>, update ticket status,
                        save staff notes, and respond to students from one focused queue.
                    </p>

                    <div class="stat-row stat-row-four">
                        <div class="stat-box">
                            <span class="stat-kicker">Pending</span>
                            <strong>{{ $pendingRequestCount }}</strong>
                            <span>New requests waiting for action</span>
                        </div>

                        <div class="stat-box">
                            <span class="stat-kicker">In Progress</span>
                            <strong>{{ $inProgressRequestCount }}</strong>
                            <span>Requests currently being handled</span>
                        </div>

                        <div class="stat-box">
                            <span class="stat-kicker">Completed</span>
                            <strong>{{ $completedRequestCount }}</strong>
                            <span>Closed requests in this department</span>
                        </div>

                        <div class="stat-box">
                            <span class="stat-kicker">Urgent</span>
                            <strong>{{ $urgentRequestCount }}</strong>
                            <span>Priority tickets needing attention</span>
                        </div>
                    </div>
                </section>

                <section class="section-stack">
                    <article class="panel">
                        <div class="panel-header">
                            <h2>Recent Tickets</h2>
                            <span>Latest 4 results</span>
                        </div>

                        @if ($recentRequests->isEmpty())
                            <div class="empty-state">
                                No tickets have been routed to {{ $department->name }} yet.
                            </div>
                        @else
                            <ul class="compact-list">
                                @foreach ($recentRequests as $serviceRequest)
                                    <li>
                                        <a href="{{ route('staff.requests.show', $serviceRequest) }}" class="list-title">{{ $serviceRequest->title }}</a>
                                        <span class="list-meta">
                                            {{ $serviceRequest->user->name }} | {{ $serviceRequest->categoryName() }} | {{ $serviceRequest->updated_at->diffForHumans() }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>

                    <article class="panel">
                        <div class="panel-header">
                            <h2>Department Queue</h2>
                            <span>{{ $filteredRequestCount }} matching requests</span>
                        </div>

                        <p class="section-note">
                            Search by ticket title, student, description, or category. Status and priority filters apply only to {{ $department->name }} requests.
                        </p>

                        <form method="GET" action="{{ route('staff.dashboard') }}" class="toolbar-grid">
                            <div class="form-group">
                                <label for="search">Search Queue</label>
                                <input
                                    id="search"
                                    type="text"
                                    name="search"
                                    value="{{ $searchTerm }}"
                                    placeholder="Search request title, student, or category"
                                >
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select id="priority" name="priority">
                                    @foreach ($priorityOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($selectedPriority === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="toolbar-actions">
                                <button type="submit" class="button button-primary">Apply Filters</button>
                                <a href="{{ route('staff.dashboard') }}" class="button button-plain">Reset</a>
                            </div>
                        </form>

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
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Last Activity</th>
                                            <th>Open</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($serviceRequests as $serviceRequest)
                                            <tr>
                                                <td>
                                                    <div class="table-cell-stack">
                                                        <span class="list-title">{{ $serviceRequest->title }}</span>
                                                        <span class="request-snippet">{{ \Illuminate\Support\Str::limit($serviceRequest->description, 88) }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="table-cell-stack">
                                                        <span class="list-title">{{ $serviceRequest->user->name }}</span>
                                                        <span class="list-meta">{{ $serviceRequest->user->email }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $serviceRequest->categoryName() }}</td>
                                                <td>
                                                    <span class="priority-badge {{ $serviceRequest->is_urgent ? 'priority-urgent' : 'priority-standard' }}">
                                                        {{ $serviceRequest->is_urgent ? 'Urgent' : 'Standard' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                                        {{ $serviceRequest->statusLabel() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="table-cell-stack">
                                                        <span>{{ $serviceRequest->updated_at->diffForHumans() }}</span>
                                                        <span class="list-meta">Created {{ $serviceRequest->created_at->format('M d, Y h:i A') }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="{{ route('staff.requests.show', $serviceRequest) }}" class="text-link">Review</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{ $serviceRequests->links('pagination.galaxy') }}
                        @endif
                    </article>
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
