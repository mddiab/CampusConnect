@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
    <main class="page">
        <div class="container">
            <section class="hero-card">
                <h1>Student Dashboard</h1>
                <p>
                    This page should show the logged-in student's request summary. Replace each section below with the
                    student's real requests, current statuses, department replies, and personal actions.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <strong class="placeholder-value">Show number here</strong>
                        <span>Total open requests submitted by this student.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">Show number here</strong>
                        <span>Requests waiting for a response from staff or a department.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">Show number here</strong>
                        <span>All requests this student has submitted in total.</span>
                    </div>
                </div>
            </section>

            <section class="page-grid">
                <article class="mini-card">
                    <h2>Quick Actions</h2>
                    <ul>
                        <li>Add the button or link for creating a new service request here.</li>
                        <li>Add the link for viewing the student's full request history here.</li>
                        <li>Add the link for checking recent status updates here.</li>
                    </ul>
                </article>

                <article class="mini-card">
                    <h2>Recent Updates</h2>
                    <ul>
                        <li>Show the latest status change for this student's most recent request here.</li>
                        <li>Show the latest department reply or note sent to the student here.</li>
                        <li>Show the most recently completed request or closed update here.</li>
                    </ul>
                </article>

                <article class="mini-card">
                    <h2>Student Request Form Placeholder</h2>
                    <p class="placeholder-copy">
                        Put the student's request form here. It should include fields like request title, department,
                        category, description, optional attachment, and submit button.
                    </p>
                </article>
            </section>
        </div>
    </main>
@endsection
