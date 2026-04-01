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
        </div>
    </main>
@endsection
