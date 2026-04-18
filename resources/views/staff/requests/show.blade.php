@extends('layouts.app')

@php
    $statusClasses = [
        'pending' => 'status-pending',
        'in_progress' => 'status-in-progress',
        'completed' => 'status-completed',
    ];

    $messageClasses = [
        'student' => 'timeline-item-student',
        'staff' => 'timeline-item-staff',
        'admin' => 'timeline-item-admin',
    ];
@endphp

@section('title', 'Review Request')

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
                <p class="eyebrow">Department Ticket</p>

                <div class="hero-row">
                    <div>
                        <h1>{{ $serviceRequest->title }}</h1>
                        <p>
                            Ticket #{{ $serviceRequest->id }} was submitted to {{ $serviceRequest->departmentName() }} by
                            <strong>{{ $serviceRequest->user->name }}</strong>. Only this department team can review the record,
                            change the workflow, and reply to the student.
                        </p>
                    </div>

                    <div class="hero-chip-row">
                        <a href="{{ route('staff.dashboard') }}" class="button button-secondary">Back to Queue</a>
                    </div>
                </div>

                <div class="ticket-summary-grid">
                    <article class="ticket-summary-card">
                        <span class="stat-kicker">Department</span>
                        <div class="ticket-summary-value">{{ $serviceRequest->departmentName() }}</div>
                        <span class="ticket-summary-meta">Assigned department</span>
                    </article>

                    <article class="ticket-summary-card">
                        <span class="stat-kicker">Category</span>
                        <div class="ticket-summary-value">{{ $serviceRequest->categoryName() }}</div>
                        <span class="ticket-summary-meta">Student-selected category</span>
                    </article>

                    <article class="ticket-summary-card">
                        <span class="stat-kicker">Status</span>
                        <div class="ticket-summary-value">
                            <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                {{ $serviceRequest->statusLabel() }}
                            </span>
                        </div>
                        <span class="ticket-summary-meta">Current workflow state</span>
                    </article>

                    <article class="ticket-summary-card">
                        <span class="stat-kicker">Priority</span>
                        <div class="ticket-summary-value">
                            <span class="priority-badge {{ $serviceRequest->is_urgent ? 'priority-urgent' : 'priority-standard' }}">
                                {{ $serviceRequest->is_urgent ? 'Urgent' : 'Standard' }}
                            </span>
                        </div>
                        <span class="ticket-summary-meta">Submitted request priority</span>
                    </article>
                </div>
            </section>

            <section class="panel-grid">
                <div class="panel-stack">
                    <article class="panel">
                        <div class="panel-header">
                            <h2>Request Record</h2>
                            <span>Updated {{ $serviceRequest->updated_at->diffForHumans() }}</span>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-card">
                                <span class="stat-kicker">Submitted By</span>
                                <strong>{{ $serviceRequest->user->name }}</strong>
                                <span>{{ $serviceRequest->user->email }}</span>
                            </div>

                            <div class="detail-card">
                                <span class="stat-kicker">Submitted On</span>
                                <strong>{{ $serviceRequest->created_at->format('M d, Y') }}</strong>
                                <span>{{ $serviceRequest->created_at->format('h:i A') }}</span>
                            </div>

                            <div class="detail-card">
                                <span class="stat-kicker">Last Updated</span>
                                <strong>{{ $serviceRequest->updated_at->format('M d, Y') }}</strong>
                                <span>{{ $serviceRequest->updated_at->format('h:i A') }}</span>
                            </div>

                            <div class="detail-card">
                                <span class="stat-kicker">Resolved On</span>
                                <strong>{{ $serviceRequest->resolved_at?->format('M d, Y') ?? 'Not completed yet' }}</strong>
                                <span>{{ $serviceRequest->resolved_at?->format('h:i A') ?? 'Completion timestamp appears here when closed.' }}</span>
                            </div>
                        </div>

                        <div class="panel-slab">
                            <span class="stat-kicker">Student Description</span>
                            <strong>Issue Summary</strong>
                            <span>{{ $serviceRequest->description }}</span>
                        </div>

                        <div class="panel-slab">
                            <span class="stat-kicker">Staff Notes</span>
                            <strong>Internal Handling Notes</strong>
                            <span>{{ $serviceRequest->staff_notes ?: 'No staff notes have been saved yet.' }}</span>
                        </div>

                        <div class="panel-slab">
                            <span class="stat-kicker">Attachment</span>
                            <strong>Submitted File</strong>
                            <span>
                                @if ($serviceRequest->attachment_path)
                                    <a href="{{ route('staff.requests.attachment', $serviceRequest) }}" class="text-link">
                                        Download {{ $serviceRequest->attachment_original_name ?? 'attachment file' }}
                                    </a>
                                @else
                                    No attachment was uploaded with this request.
                                @endif
                            </span>
                        </div>
                    </article>

                    <article class="panel">
                        <div class="panel-header">
                            <h2>Request Conversation</h2>
                            <span>{{ $serviceRequest->messages->count() }} replies</span>
                        </div>

                        <p class="section-note">
                            Use the conversation for student-facing updates, clarifications, and follow-up questions.
                        </p>

                        @if ($serviceRequest->messages->isEmpty())
                            <div class="empty-state">
                                No conversation replies have been posted yet. Add the first reply below to start the thread with the student.
                            </div>
                        @else
                            <div class="timeline">
                                @foreach ($serviceRequest->messages as $message)
                                    <article class="timeline-item {{ $messageClasses[$message->author_role] ?? 'timeline-item-student' }}">
                                        <div class="timeline-item-header">
                                            <div>
                                                <span class="timeline-author">{{ $message->author_name }}</span>
                                                <span class="timeline-role">{{ $message->roleLabel() }}</span>
                                            </div>
                                            <span class="timeline-time">{{ $message->created_at->format('M d, Y h:i A') }}</span>
                                        </div>

                                        <p class="timeline-message">{{ $message->message }}</p>
                                    </article>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('staff.requests.messages.store', $serviceRequest) }}">
                            @csrf

                            <div class="form-group">
                                <label for="message">Add Reply</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    placeholder="Share a progress update, ask the student a question, or note the next action you need from them"
                                    required
                                >{{ old('message') }}</textarea>
                                <span class="field-help">Use workflow status for internal handling and the conversation thread for student-facing updates.</span>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="button button-primary">Post Reply</button>
                            </div>
                        </form>
                    </article>
                </div>

                <aside class="aside-stack">
                    <article class="panel">
                        <h2>Update Workflow</h2>
                        <p class="section-note">
                            Save the latest status, priority, and handling notes for the department team.
                        </p>

                        <form method="POST" action="{{ route('staff.requests.update', $serviceRequest) }}">
                            @csrf
                            @method('PATCH')

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected(old('status', $serviceRequest->status) === $status)>
                                            {{ ucwords(str_replace('_', ' ', $status)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="is_urgent">Priority</label>
                                <select id="is_urgent" name="is_urgent" required>
                                    <option value="0" @selected((string) old('is_urgent', (int) $serviceRequest->is_urgent) === '0')>Standard</option>
                                    <option value="1" @selected((string) old('is_urgent', (int) $serviceRequest->is_urgent) === '1')>Urgent</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="staff_notes">Staff Notes</label>
                                <textarea
                                    id="staff_notes"
                                    name="staff_notes"
                                    placeholder="Write the latest update, actions taken, or what the student should expect next"
                                >{{ old('staff_notes', $serviceRequest->staff_notes) }}</textarea>
                                <span class="field-help">Completed requests automatically record the completion timestamp.</span>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="button button-primary">Save Update</button>
                            </div>
                        </form>
                    </article>

                    <article class="mini-card">
                        <h2>Other Department Tickets</h2>
                        <p class="section-note">
                            Jump directly to other recent tickets assigned to {{ $serviceRequest->departmentName() }}.
                        </p>

                        @if ($relatedRequests->isEmpty())
                            <div class="empty-state">
                                No other recent tickets are available for this department.
                            </div>
                        @else
                            <ul class="compact-list">
                                @foreach ($relatedRequests as $relatedRequest)
                                    <li>
                                        <a href="{{ route('staff.requests.show', $relatedRequest) }}" class="list-title">{{ $relatedRequest->title }}</a>
                                        <span class="list-meta">
                                            {{ $relatedRequest->user->name }} | {{ $relatedRequest->categoryName() }} | {{ $relatedRequest->updated_at->diffForHumans() }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                </aside>
            </section>
        </div>
    </main>
@endsection
