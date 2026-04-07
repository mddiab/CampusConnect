@extends('layouts.app')

@php
    $statusClasses = [
        'pending' => 'status-pending',
        'in_progress' => 'status-in-progress',
        'completed' => 'status-completed',
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
                    This page shows the full information for the selected student request, including its department,
                    category, current status, description, and any attachment submitted with it.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <strong class="placeholder-value">{{ $serviceRequest->department }}</strong>
                        <span>Department selected when the request was submitted.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">{{ $serviceRequest->category }}</strong>
                        <span>Category chosen by the student for this request.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">
                            <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                {{ $serviceRequest->statusLabel() }}
                            </span>
                        </strong>
                        <span>Current request status based on department handling.</span>
                    </div>

                    <!-- FEATURE: Priority Levels - Display urgent indicator -->
                    <div class="stat-box">
                        <strong class="placeholder-value">
                            @if ($serviceRequest->is_urgent)
                                <span style="color: #dc3545; font-size: 1.3em;">🚨 URGENT</span>
                            @else
                                <span style="color: #999;">Normal Priority</span>
                            @endif
                        </strong>
                        <span>Priority level of this request.</span>
                    </div>
                </div>
            </section>

            <section class="panel-grid">
                <article class="panel">
                    <div class="panel-header">
                        <h2>Request Details</h2>
                        <a href="{{ route('student.dashboard') }}" class="text-link">Back to Dashboard</a>
                    </div>

                    <!-- FEATURE: Edit/Update Requests - Show Edit button only if pending -->
                    @if ($serviceRequest->status === 'pending')
                        <div class="action-buttons" style="margin-bottom: 20px;">
                            <a href="{{ route('student.requests.edit', $serviceRequest) }}" class="btn btn-primary">
                                Edit Request
                            </a>
                        </div>
                    @else
                        <div class="action-info" style="margin-bottom: 20px; padding: 12px; background-color: #f0f0f0; border-radius: 4px;">
                            <p class="text-muted small">
                                ℹ️ This request cannot be edited because it is already being processed by staff.
                            </p>
                        </div>
                    @endif

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
                                <!-- FEATURE: Priority Levels - Show urgent status in details -->
                                <tr>
                                    <th>Priority</th>
                                    <td>
                                        @if ($serviceRequest->is_urgent)
                                            <span style="color: #dc3545; font-weight: bold;">🚨 URGENT</span> - This request needs priority handling
                                        @else
                                            Normal Priority
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

            <!-- FEATURE: Staff Communication & Notes - Display staff responses to this request -->
            <section class="panel-grid">
                <article class="panel">
                    <div class="panel-header">
                        <h2>Staff Communication</h2>
                        <span class="badge">{{ $serviceRequest->notes()->count() }} Response(s)</span>
                    </div>

                    <!-- Display all staff notes on this request -->
                    @if ($serviceRequest->notes()->count() > 0)
                        <div class="notes-container">
                            @foreach ($serviceRequest->notes as $note)
                                <div class="note-item" style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #007bff; border-radius: 4px;">
                                    <!-- Staff member info and note timestamp -->
                                    <div class="note-header" style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <strong style="color: #333;">{{ $note->user->name }}</strong>
                                            <span style="color: #666; font-size: 0.9em;">
                                                ({{ $note->user->role === 'staff' ? 'Staff Member' : 'Administrator' }})
                                            </span>
                                        </div>
                                        <span class="note-date" style="color: #999; font-size: 0.85em;">
                                            {{ $note->created_at->format('M d, Y h:i A') }}
                                        </span>
                                    </div>

                                    <!-- The actual note content -->
                                    <div class="note-content" style="color: #333; line-height: 1.6;">
                                        {{ $note->content }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Show message when no staff responses yet -->
                        <div class="no-notes-info" style="padding: 20px; text-align: center; background-color: #f0f8ff; border-radius: 4px;">
                            <p style="color: #666; margin: 0;">
                                ℹ️ No staff responses yet. Your request is being reviewed by the {{ $serviceRequest->department }} department.
                            </p>
                        </div>
                    @endif
                </article>
            </section>
        </div>
    </main>
@endsection
