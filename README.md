# CampusConnect

CampusConnect is a Laravel 13 campus service-request system with role-based dashboards for students, staff, and administrators.

The current implementation supports:

- student request submission with department-aware category selection
- relational request storage using `department_id` and `service_category_id`
- student and staff request conversations with reply timelines on each request
- staff request queues scoped to the staff member's department
- staff status updates and staff notes
- admin user management with department assignment for staff accounts
- admin department and service-category CRUD
- admin reporting with filtered request views and CSV export
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
- can reply in the request conversation on their own requests
- can see staff notes and completion timestamps on their requests

### Staff

- is redirected to `staff.dashboard`
- must belong to a department to manage requests
- sees only requests assigned to their department
- can open request review pages
- can update request status and staff notes
- can reply in request conversations for requests in their department
- can download attachments for requests in their department

### Admin

- is redirected to `admin.dashboard`
- can search users
- can create, update, and delete users
- can assign departments to staff accounts
- can create, update, and delete departments
- can create, update, and delete service categories
- can review recent request activity
- can open filtered reports and export request data as CSV

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
| `POST` | `/student/requests/{serviceRequest}/messages` | `student.requests.messages.store` | `StudentRequestController@storeMessage` | `web`, `auth`, `role:student` |
| `GET` | `/student/requests/{serviceRequest}/attachment` | `student.requests.attachment` | `StudentRequestController@download` | `web`, `auth`, `role:student` |

### Staff Routes

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET` | `/staff/dashboard` | `staff.dashboard` | `StaffRequestController@index` | `web`, `auth`, `role:staff` |
| `GET` | `/staff/requests/{serviceRequest}` | `staff.requests.show` | `StaffRequestController@show` | `web`, `auth`, `role:staff` |
| `POST` | `/staff/requests/{serviceRequest}/messages` | `staff.requests.messages.store` | `StaffRequestController@storeMessage` | `web`, `auth`, `role:staff` |
| `PATCH` | `/staff/requests/{serviceRequest}` | `staff.requests.update` | `StaffRequestController@update` | `web`, `auth`, `role:staff` |
| `GET` | `/staff/requests/{serviceRequest}/attachment` | `staff.requests.attachment` | `StaffRequestController@download` | `web`, `auth`, `role:staff` |

### Admin Routes

| Method | URI | Name | Action | Middleware |
| --- | --- | --- | --- | --- |
| `GET` | `/admin/dashboard` | `admin.dashboard` | `AdminController@dashboard` | `web`, `auth`, `role:admin` |
| `GET` | `/admin/users` | `admin.users` | `AdminController@users` | `web`, `auth`, `role:admin` |
| `POST` | `/admin/users` | `admin.users.store` | `AdminController@storeUser` | `web`, `auth`, `role:admin` |
| `PUT` | `/admin/users/{user}` | `admin.users.update` | `AdminController@updateUser` | `web`, `auth`, `role:admin` |
| `DELETE` | `/admin/users/{user}` | `admin.users.destroy` | `AdminController@destroyUser` | `web`, `auth`, `role:admin` |
| `GET` | `/admin/departments` | `admin.departments` | `AdminController@departments` | `web`, `auth`, `role:admin` |
| `POST` | `/admin/departments` | `admin.departments.store` | `AdminController@storeDepartment` | `web`, `auth`, `role:admin` |
| `PUT` | `/admin/departments/{department}` | `admin.departments.update` | `AdminController@updateDepartment` | `web`, `auth`, `role:admin` |
| `DELETE` | `/admin/departments/{department}` | `admin.departments.destroy` | `AdminController@destroyDepartment` | `web`, `auth`, `role:admin` |
| `GET` | `/admin/categories` | `admin.categories` | `AdminController@categories` | `web`, `auth`, `role:admin` |
| `POST` | `/admin/categories` | `admin.categories.store` | `AdminController@storeCategory` | `web`, `auth`, `role:admin` |
| `PUT` | `/admin/categories/{serviceCategory}` | `admin.categories.update` | `AdminController@updateCategory` | `web`, `auth`, `role:admin` |
| `DELETE` | `/admin/categories/{serviceCategory}` | `admin.categories.destroy` | `AdminController@destroyCategory` | `web`, `auth`, `role:admin` |
| `GET` | `/admin/reports` | `admin.reports` | `AdminController@reports` | `web`, `auth`, `role:admin` |
| `GET` | `/admin/reports/export` | `admin.reports.export` | `AdminController@exportReports` | `web`, `auth`, `role:admin` |

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
- lets students add request-conversation replies to their own requests
- restricts request detail and attachment download to the owning student

### `StaffRequestController`

- loads the staff dashboard for the authenticated staff member's department
- filters the queue by status
- restricts request review/update/download to matching department staff
- lets staff add conversation replies only on requests in their own department
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
- redirects `/admin/users`, `/admin/departments`, and `/admin/categories` to the relevant live dashboard sections
- creates, updates, and deletes departments
- creates, updates, and deletes service categories
- prevents unsafe deletes when records are still in use
- renders the reports page and exports filtered request data as CSV

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
- `messages()`: `hasMany(ServiceRequestMessage::class)`

Status constants:

- `STATUS_PENDING`
- `STATUS_IN_PROGRESS`
- `STATUS_COMPLETED`

Important behavior:

- syncs `department_id` from the selected `service_category_id` during save
- clears `resolved_at` unless the request is completed
- sets `resolved_at` automatically when status changes to `completed`
- provides helper methods for status labels and staff authorization checks
- can append conversation replies with author snapshots

### `ServiceRequestMessage`

Important fields:

- `service_request_id`
- `user_id`
- `author_name`
- `author_role`
- `message`

Relationships:

- `serviceRequest()`: `belongsTo(ServiceRequest::class)`
- `user()`: `belongsTo(User::class)`

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
- `resources/views/admin/reports.blade.php`

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

### `service_request_messages`

| Column | Type | Null | Notes |
| --- | --- | --- | --- |
| `id` | bigint unsigned | no | primary key |
| `service_request_id` | bigint unsigned | no | FK -> `service_requests.id`, cascade delete |
| `user_id` | bigint unsigned | yes | FK -> `users.id`, `nullOnDelete()` |
| `author_name` | varchar | no | author snapshot for timeline history |
| `author_role` | varchar | no | author role snapshot |
| `message` | text | no | conversation body |
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

Seeded user totals:

- 10 students
- 6 staff members, one for each department
- 3 admins

Primary sample logins:

| Role | Email | Password | Department |
| --- | --- | --- | --- |
| Student | `student@campusconnect.test` | `password` | none |
| Staff | `staff@campusconnect.test` | `password` | Information Technology |
| Admin | `admin@campusconnect.test` | `password` | none |

Additional seeded accounts include:

- students: `student2@campusconnect.test` through `student10@campusconnect.test`
- staff: `staff.maintenance@campusconnect.test`, `staff.registrar@campusconnect.test`, `staff.finance@campusconnect.test`, `staff.library@campusconnect.test`, `staff.affairs@campusconnect.test`
- admins: `admin2@campusconnect.test`, `admin3@campusconnect.test`

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
- request conversation replies for students and staff
- staff attachment download
- admin department/category management
- admin report export
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

## Notes

The core workflow is now functional across all three roles. The remaining work, if desired, is mostly product polish rather than missing core behavior:

1. split the large Blade pages into smaller partials or components
2. add richer audit/reporting views beyond the current CSV export
3. add email or notification hooks when request statuses change
