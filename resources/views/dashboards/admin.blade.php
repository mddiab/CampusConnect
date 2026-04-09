@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<style>
    .admin-toolbar,
    .admin-nav,
    .inline-actions,
    .modal-grid {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .admin-toolbar {
        margin-bottom: 16px;
    }

    .admin-nav {
        margin-top: 18px;
    }

    .search-input,
    .compact-select {
        min-height: 44px;
        padding: 0 14px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
        color: var(--text);
    }

    .search-input {
        flex: 1;
        min-width: 220px;
    }

    .compact-select {
        min-width: 220px;
    }

    .inline-form {
        display: inline;
        margin: 0;
    }

    .inline-actions {
        justify-content: flex-start;
    }

    .btn-sm {
        min-height: 36px;
        padding: 0 14px;
        font-size: 0.9rem;
        border-radius: 10px;
    }

    .btn-edit {
        background: #e8efff;
        color: var(--primary);
    }

    .btn-delete {
        background: #fff1f2;
        color: var(--danger);
        border: 1px solid #f1c7cd;
    }

    .section-stack-admin {
        display: grid;
        gap: 18px;
    }

    .section-id {
        scroll-margin-top: 90px;
    }

    .info-note {
        margin: 0 0 16px;
        color: var(--muted);
    }

    .pill-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        background: var(--panel-soft);
        border: 1px solid var(--border);
        color: var(--primary-dark);
        font-size: 0.92rem;
        font-weight: 700;
    }

    .category-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 10px;
    }

    .category-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
    }

    .category-item strong {
        display: block;
        color: var(--text);
    }

    .modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000;
        background: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        padding: 18px;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        width: min(520px, 100%);
        background: var(--panel);
        border-radius: 18px;
        border: 1px solid var(--border);
        box-shadow: 0 20px 40px rgba(34, 56, 112, 0.2);
        padding: 24px;
        position: relative;
    }

    .close-modal {
        position: absolute;
        top: 16px;
        right: 16px;
        min-height: 34px;
        width: 34px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid var(--border);
        color: var(--muted);
    }

    .modal-content h2 {
        margin: 0 0 18px;
        color: var(--primary);
    }

    .w-full {
        width: 100%;
    }

    @media (max-width: 720px) {
        .inline-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<main class="page">
    <div class="container">
        @if (session('success'))
            <div class="success-box">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="error-box">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <section class="hero-card">
            <h1>Admin Dashboard</h1>
            <p>
                Manage users, departments, service categories, and reporting from one place.
                The links below jump directly to each working management section.
            </p>

            <div class="stat-row">
                <div class="stat-box">
                    <strong>{{ $totalUsers }}</strong>
                    <span>Total user accounts in the system.</span>
                </div>
                <div class="stat-box">
                    <strong>{{ $totalDepartments }}</strong>
                    <span>Departments currently available for request routing.</span>
                </div>
                <div class="stat-box">
                    <strong>{{ $todayRequests }}</strong>
                    <span>Requests created today across all departments.</span>
                </div>
            </div>

            <div class="pill-row">
                <span class="pill">Admins: {{ $roles['admin'] }}</span>
                <span class="pill">Staff: {{ $roles['staff'] }}</span>
                <span class="pill">Students: {{ $roles['student'] }}</span>
            </div>

            <div class="admin-nav">
                <a href="#user-management" class="button button-plain">Users</a>
                <a href="#department-management" class="button button-plain">Departments</a>
                <a href="#category-management" class="button button-plain">Categories</a>
                <a href="#reports-overview" class="button button-plain">Recent Activity</a>
                <a href="{{ route('admin.reports') }}" class="button button-secondary">Full Reports</a>
            </div>
        </section>

        <section class="section-stack-admin">
            <article class="panel section-id" id="user-management">
                <div class="panel-header">
                    <h2><i class="fas fa-users"></i> User Management</h2>
                    <button type="button" class="button button-primary" onclick="openModal('addUserModal')">Add User</button>
                </div>

                <p class="info-note">
                    Staff accounts require a department assignment. Student and admin accounts do not.
                </p>

                <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-toolbar">
                    @if (request('dept')) <input type="hidden" name="dept" value="{{ request('dept') }}"> @endif
                    @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="search-input"
                        placeholder="Search users by name or email..."
                    >
                    <button type="submit" class="button button-secondary">Search</button>
                    <a href="{{ route('admin.users') }}" class="button button-plain">Reset</a>
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentUsers as $user)
                                <tr>
                                    <td><strong>{{ $user->name }}</strong></td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->department?->name ?? 'Not assigned' }}</td>
                                    <td><span class="role-badge">{{ ucfirst($user->role) }}</span></td>
                                    <td>
                                        <div class="inline-actions">
                                            <button type="button" class="button btn-sm btn-edit" onclick="openModal('editUserModal-{{ $user->id }}')">Edit</button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-form" onsubmit="return confirm('Delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="button btn-sm btn-delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">No users matched the current search.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="panel section-id" id="department-management">
                <div class="panel-header">
                    <h2><i class="fas fa-building"></i> Departments</h2>
                    <button type="button" class="button button-primary" onclick="openModal('addDepartmentModal')">Add Department</button>
                </div>

                <p class="info-note">
                    Departments can be renamed at any time. Deletion is blocked while users, categories, or requests still depend on them.
                </p>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Staff</th>
                                <th>Categories</th>
                                <th>Requests</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($departments as $department)
                                <tr>
                                    <td><strong>{{ $department->name }}</strong></td>
                                    <td>{{ $department->users_count }}</td>
                                    <td>{{ $department->categories_count }}</td>
                                    <td>{{ $department->service_requests_count }}</td>
                                    <td>
                                        <div class="inline-actions">
                                            <button type="button" class="button btn-sm btn-edit" onclick="openModal('editDepartmentModal-{{ $department->id }}')">Edit</button>
                                            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="inline-form" onsubmit="return confirm('Delete this department?');">
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

            <article class="panel section-id" id="category-management">
                <div class="panel-header">
                    <h2><i class="fas fa-folder-open"></i> Service Categories</h2>
                    <button type="button" class="button button-primary" onclick="openModal('addCategoryModal')">Add Category</button>
                </div>

                <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-toolbar">
                    @if (request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                    @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    <select name="dept" class="compact-select" onchange="this.form.submit()">
                        <option value="all">All Departments</option>
                        @foreach ($allDepartments as $department)
                            <option value="{{ $department->id }}" @selected(request('dept') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.categories') }}" class="button button-plain">Reset</a>
                </form>

                @foreach ($departments as $department)
                    <div class="demo-box" style="margin-top: 16px;">
                        <h3 style="margin: 0 0 14px; color: var(--primary-dark);">{{ $department->name }}</h3>
                        <ul class="category-list">
                            @forelse ($department->categories as $category)
                                <li class="category-item">
                                    <div>
                                        <strong>{{ $category->name }}</strong>
                                        <span class="muted-text">{{ $category->service_requests_count }} requests currently linked to this category.</span>
                                    </div>
                                    <div class="inline-actions">
                                        <button type="button" class="button btn-sm btn-edit" onclick="openModal('editCategoryModal-{{ $category->id }}')">Edit</button>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline-form" onsubmit="return confirm('Delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="button btn-sm btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </li>
                            @empty
                                <li class="empty-state">No categories for this department yet.</li>
                            @endforelse
                        </ul>
                    </div>
                @endforeach
            </article>

            <article class="panel section-id" id="reports-overview">
                <div class="panel-header">
                    <h2><i class="fas fa-chart-bar"></i> Recent Activity</h2>
                    <div class="inline-actions">
                        <a href="{{ route('admin.reports') }}" class="button button-plain btn-sm">Open Reports</a>
                        <a href="{{ route('admin.reports.export', request()->only(['status', 'dept'])) }}" class="button button-secondary btn-sm">Export CSV</a>
                    </div>
                </div>

                <div class="pill-row" style="margin-bottom: 18px;">
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="button {{ request('status', 'all') === 'all' ? 'button-primary' : 'button-plain' }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}" class="button {{ request('status') === 'pending' ? 'button-primary' : 'button-plain' }}">Pending</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'in_progress']) }}" class="button {{ request('status') === 'in_progress' ? 'button-primary' : 'button-plain' }}">In Progress</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}" class="button {{ request('status') === 'completed' ? 'button-primary' : 'button-plain' }}">Completed</a>
                </div>

                <ul class="category-list">
                    @forelse ($logs as $log)
                        <li class="category-item">
                            <div>
                                <strong>{{ $log->title }}</strong>
                                <div class="muted-text">
                                    {{ $log->user->name ?? 'Unknown' }} |
                                    {{ $log->departmentName() }} |
                                    {{ $log->categoryName() }} |
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <span class="status-badge status-{{ str_replace('_', '-', $log->status) }}">
                                {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                            </span>
                        </li>
                    @empty
                        <li class="empty-state">No recent activity matched the current filters.</li>
                    @endforelse
                </ul>
            </article>
        </section>
    </div>

    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <button type="button" class="close-modal" onclick="closeModal('addUserModal')">&times;</button>
            <h2>Add User</h2>
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
                    <label>Department</label>
                    <select name="department_id">
                        <option value="">Not assigned</option>
                        @foreach ($allDepartments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
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

    @foreach ($recentUsers as $user)
        <div id="editUserModal-{{ $user->id }}" class="modal">
            <div class="modal-content">
                <button type="button" class="close-modal" onclick="closeModal('editUserModal-{{ $user->id }}')">&times;</button>
                <h2>Edit User</h2>
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
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
                            <option value="admin" @selected($user->role === 'admin')>Admin</option>
                            <option value="staff" @selected($user->role === 'staff')>Staff</option>
                            <option value="student" @selected($user->role === 'student')>Student</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department_id">
                            <option value="">Not assigned</option>
                            @foreach ($allDepartments as $department)
                                <option value="{{ $department->id }}" @selected((string) $user->department_id === (string) $department->id)>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="button button-primary w-full">Save User</button>
                </form>
            </div>
        </div>
    @endforeach

    <div id="addDepartmentModal" class="modal">
        <div class="modal-content">
            <button type="button" class="close-modal" onclick="closeModal('addDepartmentModal')">&times;</button>
            <h2>Add Department</h2>
            <form method="POST" action="{{ route('admin.departments.store') }}">
                @csrf
                <div class="form-group">
                    <label>Department Name</label>
                    <input type="text" name="name" required>
                </div>
                <button type="submit" class="button button-primary w-full">Create Department</button>
            </form>
        </div>
    </div>

    @foreach ($departments as $department)
        <div id="editDepartmentModal-{{ $department->id }}" class="modal">
            <div class="modal-content">
                <button type="button" class="close-modal" onclick="closeModal('editDepartmentModal-{{ $department->id }}')">&times;</button>
                <h2>Edit Department</h2>
                <form method="POST" action="{{ route('admin.departments.update', $department) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Department Name</label>
                        <input type="text" name="name" value="{{ $department->name }}" required>
                    </div>
                    <button type="submit" class="button button-primary w-full">Save Department</button>
                </form>
            </div>
        </div>
    @endforeach

    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <button type="button" class="close-modal" onclick="closeModal('addCategoryModal')">&times;</button>
            <h2>Add Service Category</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id" required>
                        @foreach ($allDepartments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" required>
                </div>
                <button type="submit" class="button button-primary w-full">Create Category</button>
            </form>
        </div>
    </div>

    @foreach ($departments as $department)
        @foreach ($department->categories as $category)
            <div id="editCategoryModal-{{ $category->id }}" class="modal">
                <div class="modal-content">
                    <button type="button" class="close-modal" onclick="closeModal('editCategoryModal-{{ $category->id }}')">&times;</button>
                    <h2>Edit Service Category</h2>
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department_id" required>
                                @foreach ($allDepartments as $optionDepartment)
                                    <option value="{{ $optionDepartment->id }}" @selected((string) $category->department_id === (string) $optionDepartment->id)>
                                        {{ $optionDepartment->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Category Name</label>
                            <input type="text" name="name" value="{{ $category->name }}" required>
                        </div>
                        <button type="submit" class="button button-primary w-full">Save Category</button>
                    </form>
                </div>
            </div>
        @endforeach
    @endforeach
</main>

<script>
    function openModal(id) {
        document.getElementById(id)?.classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id)?.classList.remove('active');
    }

    window.addEventListener('click', (event) => {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    });
</script>
@endsection
