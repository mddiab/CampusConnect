@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<main class="page">
<div class="container">

    <!-- HERO -->
    <section class="hero-card">
        <h1>Admin Dashboard</h1>
        <p>Manage all system components from one place.</p>

        <div class="stat-row">
            <div class="stat-box">
                <strong>{{ $totalUsers }}</strong>
                <span>Users</span>
            </div>
            <div class="stat-box">
                <strong>{{ $totalDepartments }}</strong>
                <span>Departments</span>
            </div>
            <div class="stat-box">
                <strong>{{ $todayRequests }}</strong>
                <span>Requests Today</span>
            </div>
        </div>
    </section>

    <!-- ADMIN CONTROLS -->
    <section class="section-stack">

        <article class="panel">
            <h2>⚙️ Administration Controls</h2>
        </article>

        <!-- ================= USERS ================= -->
        <article class="panel">
            <h2>👤 User Management</h2>

            <a href="#" class="btn">➕ Add User</a>

            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentUsers as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>
                            <button>Edit</button>
                            <form method="POST" action="#">
                                @csrf
                                @method('DELETE')
                                <button style="color:red;">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No users found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </article>

        <!-- ================= DEPARTMENTS ================= -->
        <article class="panel">
            <h2>🏢 Departments</h2>

            <form method="POST" action="#">
                @csrf
                <input type="text" name="name" placeholder="New Department" required>
                <button type="submit">Add</button>
            </form>

            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Staff</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($departments as $dept)
                    <tr>
                        <td>{{ $dept->name }}</td>
                        <td>{{ $dept->users_count }}</td>
                        <td>
                            <button>Edit</button>

                            <form method="POST" action="#">
                                @csrf
                                @method('DELETE')
                                <button style="color:red;">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </article>

        <!-- ================= CATEGORIES ================= -->
        <article class="panel">
            <h2>📂 Service Categories</h2>

            <form method="POST" action="#">
                @csrf
                <input type="text" name="name" placeholder="Category name" required>

                <select name="department_id">
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>

                <button type="submit">Add</button>
            </form>

            @foreach($departments as $dept)
                <h4>{{ $dept->name }}</h4>
                <ul>
                    @forelse($dept->categories as $cat)
                        <li>
                            {{ $cat->name }}
                            <button>Edit</button>

                            <form method="POST" action="#">
                                @csrf
                                @method('DELETE')
                                <button style="color:red;">Delete</button>
                            </form>
                        </li>
                    @empty
                        <li>No categories</li>
                    @endforelse
                </ul>
            @endforeach
        </article>

        <!-- ================= REPORTS ================= -->
        <article class="panel">
            <h2>📊 Reports & Monitoring</h2>

            <button>Export</button>
            <button>Refresh</button>

            <ul>
                @forelse($logs as $log)
                    <li>
                        <strong>{{ $log->title }}</strong>
                        ({{ ucfirst(str_replace('_', ' ', $log->status)) }})
                        <br>
                        <small>
                            {{ $log->user->name ?? 'Unknown' }} -
                            {{ $log->created_at->diffForHumans() }}
                        </small>
                    </li>
                @empty
                    <li>No activity</li>
                @endforelse
            </ul>
        </article>

    </section>
</div>
</main>
@endsection