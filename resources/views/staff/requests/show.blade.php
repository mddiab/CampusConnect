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
                <h1>{{ $serviceRequest->title }}</h1>
                <p>
                    Review the details, update the status, and reply in the request conversation for {{ $serviceRequest->departmentName() }}.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <span class="stat-kicker">Department</span>
                        <strong class="placeholder-value">{{ $serviceRequest->departmentName() }}</strong>
                        <span>Assigned team</span>
                    </div>

                    <div class="stat-box">
                        <span class="stat-kicker">Category</span>
                        <strong class="placeholder-value">{{ $serviceRequest->categoryName() }}</strong>
                        <span>Request type</span>
                    </div>

                    <div class="stat-box">
                        <span class="stat-kicker">Status</span>
                        <strong class="placeholder-value">
                            <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                {{ $serviceRequest->statusLabel() }}
                            </span>
                        </strong>
                        <span>Current state</span>
                    </div>
                </div>
            </section>

            <section class="panel-grid">
                <article class="panel">
                    <div class="panel-header">
                        <h2>Details</h2>
                        <a href="{{ route('staff.dashboard') }}" class="text-link">Back to Queue</a>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <tbody>
                                <tr>
                                    <th>Submitted By</th>
                                    <td>{{ $serviceRequest->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Submitted On</th>
                                    <td>{{ $serviceRequest->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated</th>
                                    <td>{{ $serviceRequest->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $serviceRequest->description }}</td>
                                </tr>
                                <tr>
                                    <th>Current Notes</th>
                                    <td>{{ $serviceRequest->staff_notes ?: 'No staff notes have been saved yet.' }}</td>
                                </tr>
                                <tr>
                                    <th>Resolved On</th>
                                    <td>{{ $serviceRequest->resolved_at?->format('M d, Y h:i A') ?? 'This request is not completed yet.' }}</td>
                                </tr>
                                <tr>
                                    <th>Attachment</th>
                                    <td>
                                        @if ($serviceRequest->attachment_path)
                                            <a href="{{ route('staff.requests.attachment', $serviceRequest) }}" class="text-link">
                                                Download {{ $serviceRequest->attachment_original_name ?? 'attachment file' }}
                                            </a>
                                        @else
                                            No attachment was uploaded with this request.
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="panel">
                    <h2>Update</h2>
                    <p class="section-note">
                        Save the latest status and notes for the student.
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
            </section>

            <section class="panel" style="margin-top: 22px;">
                <div class="panel-header">
                    <h2>Request Conversation</h2>
                    <span>{{ $serviceRequest->messages->count() }} replies</span>
                </div>

                <p class="section-note">
                    Post updates here when you need to ask the student for clarification or give progress information outside the status field.
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
                        <span class="field-help">Use the status selector above for workflow changes and this conversation for back-and-forth updates.</span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button button-primary">Post Reply</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
@endsection
