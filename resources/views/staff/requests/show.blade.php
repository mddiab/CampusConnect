@extends('layouts.app')

@php
    $statusClasses = [
        'pending' => 'status-pending',
        'in_progress' => 'status-in-progress',
        'completed' => 'status-completed',
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
                    Review the request details below, download the attachment if needed, and update the request status
                    for the {{ $serviceRequest->departmentName() }} department.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <strong class="placeholder-value">{{ $serviceRequest->departmentName() }}</strong>
                        <span>Department handling the request.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">{{ $serviceRequest->categoryName() }}</strong>
                        <span>Category assigned to the request.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">
                            <span class="status-badge {{ $statusClasses[$serviceRequest->status] ?? 'status-pending' }}">
                                {{ $serviceRequest->statusLabel() }}
                            </span>
                        </strong>
                        <span>Current workflow state for this request.</span>
                    </div>
                </div>
            </section>

            <section class="panel-grid">
                <article class="panel">
                    <div class="panel-header">
                        <h2>Request Details</h2>
                        <a href="{{ route('staff.dashboard') }}" class="text-link">Back to Staff Dashboard</a>
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
                    <h2>Update Request</h2>
                    <p class="section-note">
                        Save a status change and leave staff notes so the student can see the latest progress.
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
        </div>
    </main>
@endsection
