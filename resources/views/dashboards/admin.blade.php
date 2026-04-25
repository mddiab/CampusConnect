@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<main class="page admin-dashboard-page" id="adminDashboardPage">
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
                    <div class="stat-icon"><i class="fas fa-users-line"></i></div>
                    <strong>{{ $totalUsers }}</strong>
                    <span>Total user accounts in the system.</span>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-building-columns"></i></div>
                    <strong>{{ $totalDepartments }}</strong>
                    <span>Departments currently available for request routing.</span>
                </div>
                <div class="stat-box">
                    <div class="stat-icon"><i class="fas fa-fire-flame-curved"></i></div>
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
                <a href="#reports-overview" class="button button-plain"><i class="fas fa-bolt-lightning"></i> Recent Activity</a>
                <a href="#user-management" class="button button-plain"><i class="fas fa-user-gear"></i> Users</a>
                <a href="#department-management" class="button button-plain"><i class="fas fa-landmark"></i> Departments</a>
                <a href="#category-management" class="button button-plain"><i class="fas fa-layer-group"></i> Categories</a>
                <a href="{{ route('admin.reports') }}" class="button button-secondary"><i class="fas fa-chart-line"></i> Full Reports</a>
            </div>
        </section>

        <section class="section-stack-admin">
            <article class="panel section-id" id="reports-overview">
                <div class="panel-header">
                    <h2><i class="fas fa-chart-bar"></i> Recent Activity</h2>
                    <div class="inline-actions">
                        <a href="{{ route('admin.reports') }}" class="button button-plain btn-sm"><i class="fas fa-folder-open"></i> Open Reports</a>
                        <a href="{{ route('admin.reports.export', request()->only(['status', 'dept'])) }}" class="button button-secondary btn-sm"><i class="fas fa-file-export"></i> Export CSV</a>
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
                        <li class="empty-state">
                            <i class="fas fa-clipboard-list empty-state-ghost"></i>
                            No recent activity matched the current filters.
                        </li>
                    @endforelse
                </ul>
            </article>

            <article class="panel section-id" id="user-management">
                <div class="panel-header">
                    <h2><i class="fas fa-users"></i> User Management</h2>
                    <button type="button" class="button button-primary" data-modal-open="addUserModal">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>

                <p class="info-note">
                    Staff accounts require a department assignment, and each department is capped at 3 staff logins. Student and admin accounts do not require a department.
                </p>

                <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-toolbar" data-preserve-scroll>
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
                    <a href="{{ route('admin.users') }}" class="button button-plain" data-preserve-scroll>Reset</a>
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
                                            <button type="button" class="button btn-sm btn-edit" data-modal-open="editUserModal-{{ $user->id }}">
                                                <i class="fas fa-pen-to-square"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-form" onsubmit="return confirm('Delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="button btn-sm btn-delete">
                                                    <i class="fas fa-trash-can"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="fas fa-user-slash empty-state-ghost"></i>
                                        No users matched the current search.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $recentUsers->links('pagination.galaxy') }}
            </article>

            <article class="panel section-id" id="department-management">
                <div class="panel-header">
                    <h2><i class="fas fa-building"></i> Departments</h2>
                    <button type="button" class="button button-primary" data-modal-open="addDepartmentModal">
                        <i class="fas fa-plus"></i> Add Department
                    </button>
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
                                    <td>{{ $department->staff_members_count }}</td>
                                    <td>{{ $department->categories_count }}</td>
                                    <td>{{ $department->service_requests_count }}</td>
                                    <td>
                                        <div class="inline-actions">
                                            <button type="button" class="button btn-sm btn-edit" data-modal-open="editDepartmentModal-{{ $department->id }}">
                                                <i class="fas fa-pen-to-square"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="inline-form" onsubmit="return confirm('Delete this department?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="button btn-sm btn-delete">
                                                    <i class="fas fa-trash-can"></i> Delete
                                                </button>
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
                @php
                    $visibleCategoryCount = $departments->sum(fn ($department) => $department->categories->count());
                    $visibleCategoryRequestCount = $departments->sum(fn ($department) => $department->categories->sum('service_requests_count'));
                @endphp

                <div class="panel-header">
                    <h2><i class="fas fa-folder-open"></i> Service Categories</h2>
                    <button type="button" class="button button-primary" data-modal-open="addCategoryModal">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>

                <p class="info-note">
                    Categories are grouped under each department so routing stays predictable. Use the filter to focus on one department when you need to edit categories quickly.
                </p>

                <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-toolbar" data-preserve-scroll>
                    @if (request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                    @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    <select name="dept" class="compact-select" data-auto-submit>
                        <option value="all">All Departments</option>
                        @foreach ($allDepartments as $department)
                            <option value="{{ $department->id }}" @selected(request('dept') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.categories') }}" class="button button-plain" data-preserve-scroll>Reset</a>
                </form>

                <div class="category-section">
                    <div class="category-overview">
                        <div class="category-overview-card">
                            <strong>{{ $departments->count() }}</strong>
                            <span>Departments currently shown in this category view.</span>
                        </div>
                        <div class="category-overview-card">
                            <strong>{{ $visibleCategoryCount }}</strong>
                            <span>Total categories available across the selected departments.</span>
                        </div>
                        <div class="category-overview-card">
                            <strong>{{ $visibleCategoryRequestCount }}</strong>
                            <span>Requests currently linked to those categories.</span>
                        </div>
                    </div>

                    <div class="category-board-grid">
                        @foreach ($departments as $department)
                            <section class="category-board">
                                <div class="category-board-header">
                                    <div>
                                        <h3 class="category-board-title">{{ $department->name }}</h3>
                                        <p class="category-board-copy">
                                            Keep routing clean by editing only the categories that belong to this department.
                                        </p>
                                    </div>
                                    <span class="category-board-count">{{ $department->categories->count() }} categories</span>
                                </div>

                                @if ($department->categories->isEmpty())
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open empty-state-ghost"></i>
                                        No categories for this department yet.
                                    </div>
                                @else
                                    <div class="category-card-list">
                                        @foreach ($department->categories as $category)
                                            <article class="category-card">
                                                <div class="category-card-top">
                                                    <div>
                                                        <h4 class="category-card-title">{{ $category->name }}</h4>
                                                        <p class="category-card-copy">
                                                            {{ $category->service_requests_count }} requests currently route through this category.
                                                        </p>
                                                    </div>
                                                    <span class="category-request-chip">{{ $category->service_requests_count }} linked</span>
                                                </div>

                                                <div class="category-card-actions">
                                                    <button type="button" class="button btn-sm btn-edit" data-modal-open="editCategoryModal-{{ $category->id }}">
                                                        <i class="fas fa-pen-to-square"></i> Edit
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline-form" onsubmit="return confirm('Delete this category?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="button btn-sm btn-delete">
                                                            <i class="fas fa-trash-can"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </section>
                        @endforeach
                    </div>
                </div>
            </article>
        </section>
    </div>

    <div id="addUserModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="addUserModalTitle">
        <div class="modal-content">
            <button type="button" class="close-modal" data-modal-close="addUserModal">&times;</button>
            <div class="modal-header">
                <p class="modal-kicker">Admin Tools</p>
                <h2 id="addUserModalTitle">Add User</h2>
                <p class="modal-copy">Create a new account, choose the correct role, and assign a department only when the user is staff.</p>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="modal-form">
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
                <button type="submit" class="button button-primary w-full modal-submit">Create User</button>
            </form>
        </div>
    </div>

    @foreach ($recentUsers as $user)
        <div id="editUserModal-{{ $user->id }}" class="modal" role="dialog" aria-modal="true" aria-labelledby="editUserModalTitle-{{ $user->id }}">
            <div class="modal-content">
                <button type="button" class="close-modal" data-modal-close="editUserModal-{{ $user->id }}">&times;</button>
                <div class="modal-header">
                    <p class="modal-kicker">Account Editor</p>
                    <h2 id="editUserModalTitle-{{ $user->id }}">Edit User</h2>
                    <p class="modal-copy">Update the account details for {{ $user->name }} and keep the department aligned with the selected role.</p>
                </div>
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="modal-form">
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
                    <button type="submit" class="button button-primary w-full modal-submit">Save User</button>
                </form>
            </div>
        </div>
    @endforeach

    <div id="addDepartmentModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="addDepartmentModalTitle">
        <div class="modal-content">
            <button type="button" class="close-modal" data-modal-close="addDepartmentModal">&times;</button>
            <div class="modal-header">
                <p class="modal-kicker">Campus Structure</p>
                <h2 id="addDepartmentModalTitle">Add Department</h2>
                <p class="modal-copy">Create a new department so requests, staff assignments, and service categories can be routed correctly.</p>
            </div>
            <form method="POST" action="{{ route('admin.departments.store') }}" class="modal-form">
                @csrf
                <div class="form-group">
                    <label>Department Name</label>
                    <input type="text" name="name" required>
                </div>
                <button type="submit" class="button button-primary w-full modal-submit">Create Department</button>
            </form>
        </div>
    </div>

    @foreach ($departments as $department)
        <div id="editDepartmentModal-{{ $department->id }}" class="modal" role="dialog" aria-modal="true" aria-labelledby="editDepartmentModalTitle-{{ $department->id }}">
            <div class="modal-content">
                <button type="button" class="close-modal" data-modal-close="editDepartmentModal-{{ $department->id }}">&times;</button>
                <div class="modal-header">
                    <p class="modal-kicker">Campus Structure</p>
                    <h2 id="editDepartmentModalTitle-{{ $department->id }}">Edit Department</h2>
                    <p class="modal-copy">Rename {{ $department->name }} without affecting its current staff assignments, categories, or request history.</p>
                </div>
                <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="modal-form">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label>Department Name</label>
                        <input type="text" name="name" value="{{ $department->name }}" required>
                    </div>
                    <button type="submit" class="button button-primary w-full modal-submit">Save Department</button>
                </form>
            </div>
        </div>
    @endforeach

    <div id="addCategoryModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="addCategoryModalTitle">
        <div class="modal-content">
            <button type="button" class="close-modal" data-modal-close="addCategoryModal">&times;</button>
            <div class="modal-header">
                <p class="modal-kicker">Request Routing</p>
                <h2 id="addCategoryModalTitle">Add Service Category</h2>
                <p class="modal-copy">Create a category inside the correct department so students can route new requests with more precision.</p>
            </div>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="modal-form">
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
                <button type="submit" class="button button-primary w-full modal-submit">Create Category</button>
            </form>
        </div>
    </div>

    @foreach ($departments as $department)
        @foreach ($department->categories as $category)
            <div id="editCategoryModal-{{ $category->id }}" class="modal" role="dialog" aria-modal="true" aria-labelledby="editCategoryModalTitle-{{ $category->id }}">
                <div class="modal-content">
                    <button type="button" class="close-modal" data-modal-close="editCategoryModal-{{ $category->id }}">&times;</button>
                    <div class="modal-header">
                        <p class="modal-kicker">Request Routing</p>
                        <h2 id="editCategoryModalTitle-{{ $category->id }}">Edit Service Category</h2>
                        <p class="modal-copy">Adjust the name or department for {{ $category->name }} while keeping the category aligned with existing request data.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="modal-form">
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
                        <button type="submit" class="button button-primary w-full modal-submit">Save Category</button>
                    </form>
                </div>
            </div>
        @endforeach
    @endforeach
</main>
@endsection
