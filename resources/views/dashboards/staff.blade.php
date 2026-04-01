@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
    <main class="page">
        <div class="container">
            <section class="hero-card">
                <h1>Staff Dashboard</h1>
                <p>
                    This page should show the staff member's department work area. Replace the placeholder sections
                    below with real request counts, assigned work, request queues, and update actions.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <strong class="placeholder-value">Show number here</strong>
                        <span>Total open requests assigned to this staff member or department.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">Show number here</strong>
                        <span>Requests that need a response or status update today.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">Show number here</strong>
                        <span>Requests completed by this department during the current week.</span>
                    </div>
                </div>
            </section>

            <section class="page-grid">
                <article class="mini-card">
                    <h2>Staff Actions</h2>
                    <ul>
                        <li>Add the link or button for reviewing newly submitted requests here.</li>
                        <li>Add the action for assigning a request to a staff member here.</li>
                        <li>Add the action for updating request status and notes here.</li>
                    </ul>
                </article>

                <article class="mini-card">
                    <h2>Work Queue</h2>
                    <ul>
                        <li>Show requests waiting for initial review here.</li>
                        <li>Show requests currently in progress here.</li>
                        <li>Show requests waiting for requester feedback or additional documents here.</li>
                    </ul>
                </article>

                <article class="mini-card">
                    <h2>Request Management Table Placeholder</h2>
                    <p class="placeholder-copy">
                        Put the staff request table here. It should list request ID, requester name, subject,
                        department, status, priority, submitted date, and action buttons.
                    </p>
                </article>
            </section>
        </div>
    </main>
@endsection
