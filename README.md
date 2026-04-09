# CampusConnect

CampusConnect is a Laravel 13 campus service-request system with role-based dashboards for students, staff, and administrators.

The current implementation supports:

- student request submission with department-aware category selection
- relational request storage using `department_id` and `service_category_id`
- staff request queues scoped to the staff member's department
- staff status updates and staff notes
- admin user management with department assignment for staff accounts
- seeded reference data for departments, service categories, demo accounts, and demo requests

## Stack

- PHP `^8.3`
- Laravel `^13.0`
- Vite `^8`
- Tailwind tooling installed, though the current UI is mostly Blade with custom CSS
- PHPUnit `^12.5`

## Current Roles

### Student

- logs in through the shared login page
- is redirected to `student.dashboard`
- submits service requests
- sees only their own requests
- can open request detail pages and download their own attachments
- can see staff notes and completion timestamps on their requests

### Staff

- is redirected to `staff.dashboard`
- must belong to a department to manage requests
- sees only requests assigned to their department
- can open request review pages
- can update request status and staff notes
- can download attachments for requests in their department

### Admin

- is redirected to `admin.dashboard`
- can search users
- can create, update, and delete users
- can assign departments to staff accounts
- can review departments, service categories, and recent request activity

## Application Flow

1. A visitor lands on `/`.
2. `LoginController` authenticates the user.
3. `User::dashboardRoute()` redirects the user by role:
   - `student` -> `student.dashboard`
   - `staff` -> `staff.dashboard`
   - `admin` -> `admin.dashboard`
4. Students create requests by selecting a department and a category that belongs to that department.
5. Requests are stored with foreign keys to `departments` and `service_categories`.
6. Staff members manage only requests tied to their own department.

## Repository Structure

```text
app/
  Http/
    Controllers/
      AdminController.php
      DashboardController.php
      StaffRequestController.php
      StudentRequestController.php
      Auth/LoginController.php
    Middleware/
      EnsureUserHasRole.php
  Models/
    Department.php
    ServiceCategory.php
    ServiceRequest.php
    User.php
database/
  factories/
  migrations/
  seeders/
resources/views/
  auth/
  dashboards/
  layouts/
  staff/requests/
  students/requests/
routes/
  web.php
tests/
  Feature/
```

## Routes

### Authentication and Shared Routing

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET` | `/` | `login` | `LoginController@create` | `web` |
| `GET` | `/login` | none | closure redirect | `web` |
| `POST` | `/login` | `login.store` | `LoginController@store` | `web` |
| `POST` | `/logout` | `logout` | `LoginController@destroy` | `web`, `auth` |
| `GET` | `/dashboard` | `dashboard` | `DashboardController@index` | `web`, `auth` |

### Student Routes

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET` | `/student/dashboard` | `student.dashboard` | `DashboardController@student` | `web`, `auth`, `role:student` |
| `POST` | `/student/requests` | `student.requests.store` | `StudentRequestController@store` | `web`, `auth`, `role:student` |
| `GET` | `/student/requests/{serviceRequest}` | `student.requests.show` | `StudentRequestController@show` | `web`, `auth`, `role:student` |
| `GET` | `/student/requests/{serviceRequest}/attachment` | `student.requests.attachment` | `StudentRequestController@download` | `web`, `auth`, `role:student` |

### Staff Routes

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET` | `/staff/dashboard` | `staff.dashboard` | `StaffRequestController@index` | `web`, `auth`, `role:staff` |
| `GET` | `/staff/requests/{serviceRequest}` | `staff.requests.show` | `StaffRequestController@show` | `web`, `auth`, `role:staff` |
| `PATCH` | `/staff/requests/{serviceRequest}` | `staff.requests.update` | `StaffRequestController@update` | `web`, `auth`, `role:staff` |
| `GET` | `/staff/requests/{serviceRequest}/attachment` | `staff.requests.attachment` | `StaffRequestController@download` | `web`, `auth`, `role:staff` |

### Admin Routes

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET` | `/admin/dashboard` | `admin.dashboard` | `AdminController@dashboard` | `web`, `auth`, `role:admin` |
| `POST` | `/admin/users` | `admin.users.store` | `AdminController@storeUser` | `web`, `auth`, `role:admin` |
| `PUT` | `/admin/users/{user}` | `admin.users.update` | `AdminController@updateUser` | `web`, `auth`, `role:admin` |
| `DELETE` | `/admin/users/{user}` | `admin.users.destroy` | `AdminController@destroyUser` | `web`, `auth`, `role:admin` |
| `GET` | `/admin/users` | `admin.users` | placeholder closure | `web`, `auth`, `role:admin` |
| `GET` | `/admin/departments` | `admin.departments` | placeholder closure | `web`, `auth`, `role:admin` |
| `GET` | `/admin/categories` | `admin.categories` | placeholder closure | `web`, `auth`, `role:admin` |
| `GET` | `/admin/reports` | `admin.reports` | placeholder closure | `web`, `auth`, `role:admin` |

### Framework Routes

| Method | URI | Name | Purpose |
| --- | --- | --- | --- |
| `GET` | `/up` | none | Laravel health check |
| `GET` | `/storage/{path}` | `storage.local` | local filesystem serving |
| `PUT` | `/storage/{path}` | `storage.local.upload` | local filesystem upload |

## Controllers

### `Auth\LoginController`

- renders the login page
- authenticates credentials
- redirects authenticated users to the correct dashboard
- logs users out and invalidates the session

### `DashboardController`

- `index()` redirects authenticated users by role
- `student()` loads the student dashboard:
  - request counts
  - recent requests
  - department list with categories for the request form

### `StudentRequestController`

- validates request creation using relational fields:
  - `department_id`
  - `service_category_id`
  - `title`
  - `description`
  - optional attachment
- ensures the chosen category belongs to the selected department
- creates requests with `pending` status
- restricts request detail and attachment download to the owning student

### `StaffRequestController`

- loads the staff dashboard for the authenticated staff member's department
- filters the queue by status
- restricts request review/update/download to matching department staff
- updates:
  - `status`
  - `staff_notes`
- automatically records `resolved_at` when a request is completed

### `AdminController`

- builds the admin dashboard dataset
- loads users with their departments
- loads departments with categories and user counts
- loads recent service-request activity with relational department/category data
- creates and updates staff users with `department_id`

## Middleware

### `EnsureUserHasRole`

The `role` middleware alias is registered in `bootstrap/app.php`.

Behavior:

- unauthenticated users are redirected to `login`
- authenticated users who open the wrong dashboard are redirected to their own dashboard route

## Models

### `User`

Important fields:

- `name`
- `email`
- `password`
- `role`
- `department_id`

Relationships:

- `serviceRequests()`: `hasMany(ServiceRequest::class)`
- `department()`: `belongsTo(Department::class)`

Constants:

- `ROLE_STUDENT`
- `ROLE_STAFF`
- `ROLE_ADMIN`

### `Department`

Important fields:

- `name`

Relationships:

- `users()`: `hasMany(User::class)`
- `categories()`: `hasMany(ServiceCategory::class)`
- `serviceRequests()`: `hasMany(ServiceRequest::class)`

### `ServiceCategory`

Important fields:

- `name`
- `department_id`

Relationships:

- `department()`: `belongsTo(Department::class)`
- `serviceRequests()`: `hasMany(ServiceRequest::class)`

### `ServiceRequest`

Important fields:

- `user_id`
- `department_id`
- `service_category_id`
- `title`
- `description`
- `status`
- `staff_notes`
- `resolved_at`
- `attachment_path`
- `attachment_original_name`

Relationships:

- `user()`: `belongsTo(User::class)`
- `department()`: `belongsTo(Department::class)`
- `serviceCategory()`: `belongsTo(ServiceCategory::class)`

Status constants:

- `STATUS_PENDING`
- `STATUS_IN_PROGRESS`
- `STATUS_COMPLETED`

Important behavior:

- syncs `department_id` from the selected `service_category_id` during save
- clears `resolved_at` unless the request is completed
- sets `resolved_at` automatically when status changes to `completed`
- provides helper methods for status labels and staff authorization checks

## Views

### Auth

- `resources/views/auth/login.blade.php`

### Student

- `resources/views/dashboards/student.blade.php`
- `resources/views/students/requests/show.blade.php`

### Staff

- `resources/views/dashboards/staff.blade.php`
- `resources/views/staff/requests/show.blade.php`

### Admin

- `resources/views/dashboards/admin.blade.php`

### Shared

- `resources/views/layouts/app.blade.php`

## Database Schema

### `users`

| Column | Type | Null | Notes |
| --- | --- | --- | --- |
| `id` | bigint unsigned | no | primary key |
| `name` | varchar | no | |
| `email` | varchar | no | unique |
| `email_verified_at` | timestamp | yes | |
| `password` | varchar | no | |
| `remember_token` | varchar | yes | |
| `role` | varchar | no | default `student` |
| `department_id` | bigint unsigned | yes | FK -> `departments.id`, `nullOnDelete()` |
| `created_at` | timestamp | yes | |
| `updated_at` | timestamp | yes | |

### `service_requests`

| Column | Type | Null | Notes |
| --- | --- | --- | --- |
| `id` | bigint unsigned | no | primary key |
| `user_id` | bigint unsigned | no | FK -> `users.id`, cascade delete |
| `department_id` | bigint unsigned | yes | FK -> `departments.id`, `nullOnDelete()` |
| `service_category_id` | bigint unsigned | yes | FK -> `service_categories.id`, `nullOnDelete()` |
| `title` | varchar | no | |
| `description` | text | no | |
| `status` | varchar | no | default `pending` |
| `staff_notes` | text | yes | department response / progress notes |
| `resolved_at` | timestamp | yes | set when completed |
| `attachment_path` | varchar | yes | private storage path |
| `attachment_original_name` | varchar | yes | uploaded filename |
| `created_at` | timestamp | yes | |
| `updated_at` | timestamp | yes | |

### `departments`

| Column | Type | Null | Notes |
| --- | --- | --- | --- |
| `id` | bigint unsigned | no | primary key |
| `name` | varchar | no | unique |
| `created_at` | timestamp | yes | |
| `updated_at` | timestamp | yes | |

### `service_categories`

| Column | Type | Null | Notes |
| --- | --- | --- | --- |
| `id` | bigint unsigned | no | primary key |
| `name` | varchar | no | |
| `department_id` | bigint unsigned | yes | FK -> `departments.id`, `nullOnDelete()` |
| `created_at` | timestamp | yes | |
| `updated_at` | timestamp | yes | |

### Framework Tables

The repo also includes Laravel's default operational tables:

- `password_reset_tokens`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

## Seed Data

`DatabaseSeeder` now seeds both lookup data and demo app data.

### Seeded Departments and Categories

- Information Technology
  - Technical Support
  - General Inquiry
- Maintenance
  - Facility Maintenance
  - General Inquiry
- Registrar
  - Registration
  - Document Request
  - General Inquiry
- Finance
  - Payment
  - General Inquiry
- Library
  - Document Request
  - General Inquiry
- Student Affairs
  - General Inquiry

### Demo Accounts

| Role | Email | Password | Department |
| --- | --- | --- | --- |
| Student | `student@campusconnect.test` | `password` | none |
| Staff | `staff@campusconnect.test` | `password` | Information Technology |
| Admin | `admin@campusconnect.test` | `password` | none |

### Demo Requests

The seeder creates example student requests across multiple departments and statuses, including staff notes on the requests already in progress or completed.

## Testing

Feature coverage includes:

- login redirects by role
- dashboard access restrictions
- student request creation and validation
- request ownership rules
- staff department scoping
- staff request updates
- staff attachment download
- seeder integrity
- admin dashboard rendering

Run the suite with:

```bash
composer test
```

## Local Setup

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
composer dev
```

## Remaining Gaps

The main remaining incomplete areas are:

1. department and service-category CRUD in the admin dashboard are still placeholder actions
2. admin placeholder routes still return simple placeholder text instead of dedicated pages
3. the UI is Blade-driven and functional, but not yet decomposed into reusable components
