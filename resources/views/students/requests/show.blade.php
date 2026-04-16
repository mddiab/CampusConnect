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

@section('title', 'Request Details')

@section('content')
    <main class="page">
        <div class="container">
            @if (session('status'))
                <div class="success-box">
                    {{ session('status') }}
                </div>
            @endif

            <section class="hero-card">
                <h1>{{ $serviceRequest->title }}</h1>
                <p>
                    This page shows the full information for the selected student request, including its assigned
                    department, category, current status, description, staff notes, conversation replies, and any attachment.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <strong class="placeholder-value">{{ $serviceRequest->departmentName() }}</strong>
                        <span>Department assigned to handle this request.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">{{ $serviceRequest->categoryName() }}</strong>
                        <span>Category selected when the request was submitted.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">
                            <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                {{ $serviceRequest->statusLabel() }}
                            </span>
                        </strong>
                        <span>Current request status based on department handling.</span>
                    </div>
                </div>
            </section>

            <section class="panel-grid">
                <article class="panel">
                    <div class="panel-header">
                        <h2>Request Details</h2>
                        <a href="{{ route('student.dashboard') }}" class="text-link">Back to Dashboard</a>
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
                                    <th>Staff Notes</th>
                                    <td>{{ $serviceRequest->staff_notes ?: 'No staff notes have been added yet.' }}</td>
                                </tr>
                                <tr>
                                    <th>Resolved On</th>
                                    <td>{{ $serviceRequest->resolved_at?->format('M d, Y h:i A') ?? 'This request is not completed yet.' }}</td>
                                </tr>
                                <tr>
                                    <th>Attachment</th>
                                    <td>
                                        @if ($serviceRequest->attachment_path)
                                            <a href="{{ route('student.requests.attachment', $serviceRequest) }}" class="text-link">
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
                    <h2>Status Meaning</h2>
                    <ul>
                        <li><strong>Pending:</strong> the request was submitted and is waiting for staff review.</li>
                        <li><strong>In Progress:</strong> the department has started working on the request.</li>
                        <li><strong>Completed:</strong> the department marked the request as finished.</li>
                    </ul>
                </article>
            </section>

            <section class="panel" style="margin-top: 22px;">
                <div class="panel-header">
                    <h2>Request Conversation</h2>
                    <span>{{ $serviceRequest->messages->count() }} replies</span>
                </div>

                <p class="section-note">
                    Use the conversation to ask follow-up questions or add extra details for the department handling your request.
                </p>

                @if ($serviceRequest->messages->isEmpty())
                    <div class="empty-state">
                        No replies have been posted yet. Once you or the assigned department adds a message, the conversation timeline will appear here.
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

                <form method="POST" action="{{ route('student.requests.messages.store', $serviceRequest) }}">
                    @csrf

                    <div class="form-group">
                        <label for="message">Add Reply</label>
                        <textarea
                            id="message"
                            name="message"
                            placeholder="Ask a follow-up question or share extra details that will help the department handle this request"
                            required
                        >{{ old('message') }}</textarea>
                        <span class="field-help">Replies appear in the timeline immediately and are visible to the department handling the request.</span>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button button-primary">Post Reply</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
@endsection
