@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<style>
    /* --- Admin Dashboard Specific Styles --- */
    
    /* Layout & Utilities */
    .text-center { text-align: center; }
    .mr-sm { margin-right: 8px; }
    .mr-xs { margin-right: 4px; }
    .inline-form { display: inline; margin: 0; }
    .w-full { width: 100%; }
    .flex-gap { display: flex; gap: 8px; }
    
    /* Toolbars & Forms */
    .toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .search-input {
        flex: 1;
        min-width: 220px;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
    }
    .filter-wrapper {
        margin-bottom: 20px;
        padding: 16px;
        background: var(--panel-soft);
        border: 1px solid var(--border);
        border-radius: 16px;
    }
    .custom-select { 
        max-width: 300px;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
    }

    /* Action Buttons (Edit & Delete) */
    .btn-sm {
        min-height: 34px;
        padding: 0 14px;
        font-size: 0.85rem;
        border-radius: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .btn-edit {
        background: #e8efff;
        color: var(--primary);
        border: 1px solid transparent;
    }
    .btn-edit:hover {
        background: #d1e0ff;
        color: var(--primary-dark);
    }

    .btn-delete {
        background: #fff1f2;
        color: var(--danger);
        border: 1px solid #f1c7cd;
    }
    .btn-delete:hover {
        background: #ffe4e6;
        color: #9f1239;
        border-color: #fca5a5;
    }

    .action-group {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    /* Categories List */
    .category-group-wrap {
        margin-top: 0;
        margin-bottom: 16px;
    }
    .category-list {
        list-style-type: none;
        padding-left: 0;
        margin: 0;
    }
    .category-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 8px;
        transition: box-shadow 0.2s;
    }
    .category-item:hover {
        box-shadow: 0 4px 12px rgba(34, 56, 112, 0.05);
    }
    .category-item-title {
        color: var(--text);
        font-weight: 700;
    }

    /* Logs & Reports */
    .reports-header-actions {
        display: flex;
        gap: 10px;
    }
    .status-filters-wrap {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        background: var(--panel-soft);
        padding: 12px;
        border-radius: 16px;
        border: 1px solid var(--border);
    }
    
    /* Filter Links (Converted from Buttons) */
    .status-filter-btn {
        min-height: 32px;
        padding: 0 16px;
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .status-filter-btn.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
    }

    .log-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .log-item {
        padding: 16px 0;
        border-bottom: 1px solid var(--border);
    }
    .log-item:last-child {
        border-bottom: none;
    }
    .log-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .log-title {
        color: var(--text);
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 4px;
        display: inline-block;
    }

    /* --- Modal & Form Styles --- */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }
    .modal.active {
        display: flex;
    }
    .modal-content {
        background: var(--panel);
        padding: 24px;
        border-radius: 16px;
        width: 90%;
        max-width: 500px;
        position: relative;
        box-shadow: 0 14px 30px rgba(34, 56, 112, 0.2);
    }
    .close-modal {
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--muted);
        background: none;
        border: none;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: var(--text);
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #fff;
        box-sizing: border-box;
    }
</style>

<main class="page">
<div class="container">

    <section class="hero-card">
        <h1>Admin Dashboard</h1>
        <p>Manage all system components from one place.</p>

        <div class="stat-row">
            <div class="stat-box">
                <strong>{{ $totalUsers }}</strong>
                <span class="muted-text">Users</span>
            </div>
            <div class="stat-box">
                <strong>{{ $totalDepartments }}</strong>
                <span class="muted-text">Departments</span>
            </div>
            <div class="stat-box">
                <strong>{{ $todayRequests }}</strong>
                <span class="muted-text">Requests Today</span>
            </div>
        </div>
    </section>

    <section class="section-stack">

        <article class="panel">
            <h2><i class="fas fa-users"></i> User Management</h2>

            <form method="GET" action="{{ route('admin.dashboard') }}" class="toolbar">
                @if(request('dept')) <input type="hidden" name="dept" value="{{ request('dept') }}"> @endif
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif

                <button type="button" class="button button-primary" onclick="openModal('addUserModal')">
                    <i class="fas fa-plus mr-sm"></i> Add User
                </button>
                <input type="text" name="search" value="{{ request('search') }}" class="search-input" placeholder="Search users by name or email…">
                <button type="submit" class="button button-secondary">Search</button>
            </form>

            <div class="table-wrap">
                <table id="user-table">
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
                        <tr class="user-row">
                            <td class="user-name"><strong>{{ $user->name }}</strong></td>
                            <td class="user-email">{{ $user->email }}</td>
                            <td><span class="role-badge">{{ ucfirst($user->role) }}</span></td>
                            <td>
                                <div class="action-group">
                                    <button type="button" class="button btn-sm btn-edit" onclick="openModal('editUserModal-{{ $user->id }}')">Edit</button>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button btn-sm btn-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div id="editUserModal-{{ $user->id }}" class="modal">
                            <div class="modal-content">
                                <button type="button" class="close-modal" onclick="closeModal('editUserModal-{{ $user->id }}')">&times;</button>
                                <h2>Edit User</h2>
                                <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" value="{{ $user->name }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" value="{{ $user->email }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Role</label>
                                        <select name="role" required>
                                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="staff" {{ $user->role == 'staff' ? 'selected' : '' }}>Staff</option>
                                            <option value="student" {{ $user->role == 'student' ? 'selected' : '' }}>Student</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="button button-primary w-full">Update User</button>
                                </form>
                            </div>
                        </div>

                    @empty
                        <tr class="no-users-row">
                            <td colspan="4" class="empty-state text-center">No users found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="panel">
            <h2><i class="fas fa-building"></i> Departments</h2>

            <div class="table-wrap">
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
                            <td><strong>{{ $dept->name }}</strong></td>
                            <td>{{ $dept->users_count }}</td>
                            <td>
                                <div class="action-group">
                                    <button type="button" class="button btn-sm btn-edit">Edit</button>
                                    <form method="POST" action="#" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button btn-sm btn-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <article class="panel">
            <h2><i class="fas fa-folder-open"></i> Service Categories</h2>

            <form method="GET" action="{{ route('admin.dashboard') }}" class="filter-wrapper">
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif

                <label for="category-dept-select">Select Department</label>
                <div class="flex-gap">
                    <select name="dept" id="category-dept-select" class="custom-select" onchange="this.form.submit()">
                        <option value="all">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(request('dept') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @foreach($departments as $dept)
                <div class="category-group demo-box category-group-wrap">
                    <h3>{{ $dept->name }}</h3>
                    <ul class="category-list">
                        @forelse($dept->categories as $cat)
                            <li class="category-item">
                                <span class="category-item-title">{{ $cat->name }}</span>
                                <div class="action-group">
                                    <button type="button" class="button btn-sm btn-edit">Edit</button>
                                    <form method="POST" action="#" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button btn-sm btn-delete">Delete</button>
                                    </form>
                                </div>
                            </li>
                        @empty
                            <li class="empty-state text-center">No categories</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </article>

        <article class="panel">
            <div class="panel-header">
                <h2><i class="fas fa-chart-bar"></i> Reports & Monitoring</h2>
                <div class="reports-header-actions">
                    <a href="{{ route('admin.dashboard') }}" class="button button-plain btn-sm" style="text-decoration: none; display: inline-flex; align-items: center;">Refresh</a>
                    <button class="button button-secondary btn-sm">Export</button>
                </div>
            </div>

            <div class="status-filters-wrap">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="button status-filter-btn {{ request('status', 'all') === 'all' ? 'active' : 'button-plain' }}">All</a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="button status-filter-btn {{ request('status') === 'pending' ? 'active' : 'button-plain' }}">Pending</a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'in_progress']) }}" class="button status-filter-btn {{ request('status') === 'in_progress' ? 'active' : 'button-plain' }}">In Progress</a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}" class="button status-filter-btn {{ request('status') === 'completed' ? 'active' : 'button-plain' }}">Completed</a>
            </div>

            <ul class="log-list">
                @forelse($logs as $log)
                    <li class="log-item">
                        <div class="log-content">
                            <div>
                                <span class="log-title">{{ $log->title }}</span>
                                <br>
                                <small class="muted-text">
                                    <i class="fas fa-user mr-xs"></i> {{ $log->user->name ?? 'Unknown' }} &nbsp;|&nbsp; 
                                    <i class="fas fa-clock mr-xs"></i> {{ $log->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <span class="status-badge status-{{ str_replace('_', '-', $log->status) }}">
                                {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="empty-state text-center">No activity</li>
                @endforelse
            </ul>
        </article>

    </section>
</div>

<div id="addUserModal" class="modal">
    <div class="modal-content">
        <button type="button" class="close-modal" onclick="closeModal('addUserModal')">&times;</button>
        <h2>Add New User</h2>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="student">Student</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required minlength="8">
            </div>
            <button type="submit" class="button button-primary w-full">Create User</button>
        </form>
    </div>
</div>

</main>

<script>
    // --- 1. Modal Logic ---
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    }
</script>
@endsection