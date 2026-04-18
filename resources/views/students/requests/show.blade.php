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
                        <strong>
                            <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                {{ $serviceRequest->statusLabel() }}
                            </span>
                        </strong>
                        <span>Current request status based on department handling.</span>
                    </div>

                    @if ($serviceRequest->is_urgent)
                        <div class="stat-box">
                            <strong class="placeholder-value" style="color: #dc2626;">🚨 Urgent</strong>
                            <span>This request has been marked as requiring priority attention.</span>
                        </div>
                    @endif
                </div>

                @if ($serviceRequest->isArchived())
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded mt-4">
                        <strong>📦 Archived Request:</strong> This request was archived on {{ $serviceRequest->archived_at->format('M d, Y') }}.
                        You can view it here, but cannot add new messages or make changes.
                    </div>
                @endif
            </section>

            <section style="margin-top: 22px;">
                <article class="panel">
                    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h2>Request Details</h2>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            @if ($serviceRequest->canBeEditedBy(auth()->user()))
                                <a href="{{ route('student.requests.edit', $serviceRequest) }}" class="button button-primary" style="padding: 8px 16px; min-height: 40px; font-size: 14px;">
                                    ✏️ Edit Request
                                </a>
                            @elseif ($serviceRequest->status !== 'pending')
                                <span class="text-muted" style="padding: 8px 16px; color: #999; font-size: 14px;">
                                    Cannot edit ({{ $serviceRequest->statusLabel() }})
                                </span>
                            @endif
                            <a href="{{ route('student.dashboard') }}" class="button button-secondary" style="padding: 8px 16px; min-height: 40px; font-size: 14px;">
                                Back to Dashboard
                            </a>
                        </div>
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
                                    <th>Priority Level</th>
                                    <td>
                                        @if ($serviceRequest->is_urgent)
                                            <span style="color: #dc2626; font-weight: bold;">🚨 Urgent</span>
                                        @else
                                            <span style="color: #059669;">Normal</span>
                                        @endif
                                    </td>
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

                @unless ($serviceRequest->isArchived())
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
                @endunless

                @if ($serviceRequest->isArchived())
                    <div class="bg-gray-50 border border-gray-200 rounded p-4 text-center" style="margin-top: 16px;">
                        <p class="text-gray-600">
                            🔒 This archived request is read-only. You cannot add new messages to archived requests.
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </main>
@endsection
