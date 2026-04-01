@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <main class="page">
        <div class="container">
            <section class="hero-card">
                <h1>Admin Dashboard</h1>
                <p>
                    This page should show the administrator's system overview. Replace the placeholders below with
                    real totals, management tools, department settings, and platform activity summaries.
                </p>

                <div class="stat-row">
                    <div class="stat-box">
                        <strong class="placeholder-value">Show number here</strong>
                        <span>Total number of registered users in the system.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">Show number here</strong>
                        <span>Total departments or offices currently using CampusConnect.</span>
                    </div>

                    <div class="stat-box">
                        <strong class="placeholder-value">Show number here</strong>
                        <span>Total active requests across the platform today.</span>
                    </div>
                </div>
            </section>

            <section class="section-stack">
                <article class="panel">
                    <h2>Administration Controls</h2>
                    <ul>
                        <li>Add the user management link or section here.</li>
                        <li>Add the department and service category setup tools here.</li>
                        <li>Add the platform-wide monitoring and reporting tools here.</li>
                    </ul>
                </article>

                <article class="panel">
                    <h2>System Snapshot</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th>Current Focus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>User Management</td>
                                <td>Show account totals, role distribution, and recent user changes here.</td>
                            </tr>
                            <tr>
                                <td>Departments</td>
                                <td>Show department list, assigned staff, and available service categories here.</td>
                            </tr>
                            <tr>
                                <td>Activity Logs</td>
                                <td>Show recent admin actions, major request updates, and audit log entries here.</td>
                            </tr>
                        </tbody>
                    </table>
                </article>
            </section>
        </div>
    </main>
@endsection
