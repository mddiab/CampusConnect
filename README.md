# CampusConnect

CampusConnect is a Laravel 13 web application for handling campus service requests through a role-based interface. The current codebase supports three user roles:

- `student`: logs in, submits service requests, views request history, and downloads their own attachments
- `staff`: logs in and reaches a dashboard placeholder for future departmental request handling
- `admin`: logs in, manages users from the dashboard, reviews departments and service categories, and monitors recent service-request activity

This README replaces the default Laravel boilerplate with a summary of the project as it exists in this repository today.

## Project Status

The application is partially implemented.

- Authentication and role-based dashboard redirection are working.
- Student request submission and request-detail pages are working.
- Admin user management is implemented directly inside the admin dashboard.
- Department/category display exists on the admin dashboard, but CRUD actions for those records are still placeholders.
- The staff dashboard is currently a static placeholder view.
- The database already contains `departments`, `service_categories`, and `department_id` foreign keys, but the student request flow still uses string-based department/category values instead of relational records.

## Stack

- PHP `^8.3`
- Laravel `^13.0`
- Laravel Tinker
- Vite `^8`
- Tailwind CSS `^4` tooling installed, but the current UI is mostly inline Blade/CSS rather than utility-driven Tailwind components
- PHPUnit `^12.5`

## Current User Flow

1. A visitor lands on `/`, which serves the login page.
2. After authentication, the user is redirected according to `User::dashboardRoute()`:
   - student -> `student.dashboard`
   - staff -> `staff.dashboard`
   - admin -> `admin.dashboard`
3. Middleware aliases enforce access control:
   - `auth`
   - custom `role:<role>` middleware via `EnsureUserHasRole`
4. Students can create service requests with an optional attachment and then review the request details page.
5. Admins can search users, add users, edit users, delete users, filter departments, and filter recent service-request activity by status.

## Repository Structure

```text
app/
  Http/
    Controllers/
      AdminController.php
      DashboardController.php
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
  students/requests/
routes/
  web.php
tests/
  Feature/
```

## Routes

### Application Routes

| Method | URI | Name | Controller / Action | Middleware | Purpose |
| --- | --- | --- | --- | --- | --- |
| `GET` | `/` | `login` | `Auth\LoginController@create` | `web` | Shows login page or redirects authenticated users to their dashboard |
| `GET` | `/login` | none | closure redirect | `web` | Redirects to the named login route |
| `POST` | `/login` | `login.store` | `Auth\LoginController@store` | `web` | Validates credentials, logs the user in, redirects by role |
| `POST` | `/logout` | `logout` | `Auth\LoginController@destroy` | `web`, `auth` | Logs the user out and invalidates the session |
| `GET` | `/dashboard` | `dashboard` | `DashboardController@index` | `web`, `auth` | Redirects authenticated users to the correct role dashboard |
| `GET` | `/student/dashboard` | `student.dashboard` | `DashboardController@student` | `web`, `auth`, `role:student` | Student dashboard with request form, counts, and request history |
| `POST` | `/student/requests` | `student.requests.store` | `StudentRequestController@store` | `web`, `auth`, `role:student` | Creates a new service request and stores an optional attachment |
| `GET` | `/student/requests/{serviceRequest}` | `student.requests.show` | `StudentRequestController@show` | `web`, `auth`, `role:student` | Shows the full details of a student's own request |
| `GET` | `/student/requests/{serviceRequest}/attachment` | `student.requests.attachment` | `StudentRequestController@download` | `web`, `auth`, `role:student` | Downloads the attachment for a student's own request |
| `GET` | `/staff/dashboard` | `staff.dashboard` | `DashboardController@staff` | `web`, `auth`, `role:staff` | Staff dashboard placeholder |
| `GET` | `/admin/dashboard` | `admin.dashboard` | `AdminController@dashboard` | `web`, `auth`, `role:admin` | Admin dashboard with user management, departments, categories, and activity logs |
| `POST` | `/admin/users` | `admin.users.store` | `AdminController@storeUser` | `web`, `auth`, `role:admin` | Creates a new user |
| `PUT` | `/admin/users/{user}` | `admin.users.update` | `AdminController@updateUser` | `web`, `auth`, `role:admin` | Updates an existing user's name, email, and role |
| `DELETE` | `/admin/users/{user}` | `admin.users.destroy` | `AdminController@destroyUser` | `web`, `auth`, `role:admin` | Deletes a user |
| `GET` | `/admin/users` | `admin.users` | closure | `web`, `auth`, `role:admin` | Placeholder route returning `Users Page Coming Soon` |
| `GET` | `/admin/departments` | `admin.departments` | closure | `web`, `auth`, `role:admin` | Placeholder route returning `Departments Page Coming Soon` |
| `GET` | `/admin/categories` | `admin.categories` | closure | `web`, `auth`, `role:admin` | Placeholder route returning `Categories Page Coming Soon` |
| `GET` | `/admin/reports` | `admin.reports` | closure | `web`, `auth`, `role:admin` | Placeholder route returning `Reports Page Coming Soon` |

### Framework-Provided Routes

These appear in `php artisan route:list` but are not custom business routes:

| Method | URI | Name | Source |
| --- | --- | --- | --- |
| `GET` | `/up` | none | Laravel health check configured in `bootstrap/app.php` |
| `GET` | `/storage/{path}` | `storage.local` | Laravel filesystem local-serving route |
| `PUT` | `/storage/{path}` | `storage.local.upload` | Laravel filesystem local upload route |

## Controllers

### `Auth\LoginController`

- `create()`: shows the login screen unless the user is already authenticated
- `store()`: validates `email` and `password`, attempts login, regenerates the session, and redirects to the role-specific dashboard
- `destroy()`: logs out the current user and invalidates the session

### `DashboardController`

- `index()`: central redirect endpoint for authenticated users
- `student()`: loads the authenticated student's request collection and computed counts:
  - pending
  - in progress
  - completed
  - total
  - recent requests
  - static department/category option lists from `ServiceRequest`
- `staff()`: returns the staff placeholder view
- `admin()`: returns the admin view, but this method is currently unused because the route points to `AdminController@dashboard`

### `StudentRequestController`

- `store()`: validates input, optionally stores an uploaded file under `storage/app/private/service-requests` on the default disk, then creates the request with default status `pending`
- `show()`: only allows the owner of the request to open the detail page
- `download()`: only allows the owner of the request to download the stored attachment

Validated fields:

| Field | Rules |
| --- | --- |
| `title` | `required`, `string`, `max:255` |
| `department` | `required`, `string`, one of `ServiceRequest::departments()` |
| `category` | `required`, `string`, one of `ServiceRequest::categories()` |
| `description` | `required`, `string`, `max:5000` |
| `attachment` | nullable file, `jpg`, `jpeg`, `png`, `pdf`, `doc`, `docx`, max `2048 KB` |

### `AdminController`

- `dashboard()`: builds the entire admin page dataset
  - searches users by name or email
  - loads departments with categories and `users_count`
  - filters departments by `dept` query string
  - loads up to 10 latest service requests as activity logs
  - filters logs by request `status`
  - computes totals for users, departments, requests created today, and role counts
- `storeUser()`: validates and creates users with hashed passwords
- `updateUser()`: validates and updates name, email, and role
- `destroyUser()`: deletes a user

## Middleware

### `EnsureUserHasRole`

This middleware redirects unauthenticated users to the login route and redirects authenticated users away from dashboards that do not match their role. It is registered as the `role` alias in `bootstrap/app.php`.

Examples:

- a `staff` user visiting `student.dashboard` is redirected to `staff.dashboard`
- an `admin` user visiting `staff.dashboard` is redirected to `admin.dashboard`

## Models

### `User`

Extends Laravel's `Authenticatable` model and defines:

- role constants:
  - `ROLE_STUDENT`
  - `ROLE_STAFF`
  - `ROLE_ADMIN`
- `dashboardRoute()`: returns the correct dashboard route name
- `serviceRequests()`: `hasMany(ServiceRequest::class)`
- `department()`: `belongsTo(Department::class)`

Fillable attributes:

- `name`
- `email`
- `password`
- `role`

Hidden attributes:

- `password`
- `remember_token`

Cast behavior:

- `email_verified_at` -> `datetime`
- `password` -> `hashed`

### `ServiceRequest`

Represents student-submitted requests and defines:

- status constants:
  - `STATUS_PENDING`
  - `STATUS_IN_PROGRESS`
  - `STATUS_COMPLETED`
- `departments()`: returns the static list used in the student form
- `categories()`: returns the static list used in the student form
- `user()`: `belongsTo(User::class)`
- `statusLabel()`: converts internal status values into human-readable labels
- `isOpen()`: returns `true` for `pending` and `in_progress`

Fillable attributes:

- `user_id`
- `title`
- `department`
- `category`
- `description`
- `status`
- `attachment_path`
- `attachment_original_name`

Important note: the database also has a nullable `department_id` column on `service_requests`, but the current model fillable list and student request flow do not use it.

### `Department`

- fillable: `name`
- relationships:
  - `users()`: `hasMany(User::class)`
  - `categories()`: `hasMany(ServiceCategory::class)`

### `ServiceCategory`

- fillable: `name`, `department_id`
- relationships:
  - `department()`: `belongsTo(Department::class)`

## Views

### Auth

- `resources/views/auth/login.blade.php`
  - branded login page
  - includes demo account credentials
  - offers "remember me"

### Dashboards

- `resources/views/dashboards/student.blade.php`
  - request metrics
  - request submission form
  - latest activity panel
  - request history table
- `resources/views/dashboards/staff.blade.php`
  - placeholder content only
- `resources/views/dashboards/admin.blade.php`
  - user management table with add/edit/delete modal workflow
  - department table
  - category-by-department display
  - recent request activity feed

### Student Request Details

- `resources/views/students/requests/show.blade.php`
  - full request detail view
  - status explanation
  - attachment download link when present

### Shared Layout

- `resources/views/layouts/app.blade.php`
  - contains global styling
  - authenticated header with current user name, role badge, and logout button

## Database Schema

The schema below reflects the migrations currently present in `database/migrations`.

### `users`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | no | auto increment | primary key |
| `name` | varchar | no | none | user display name |
| `email` | varchar | no | none | unique |
| `email_verified_at` | timestamp | yes | `null` | standard Laravel auth field |
| `password` | varchar | no | none | hashed by model cast |
| `remember_token` | varchar(100) | yes | `null` | standard Laravel auth field |
| `created_at` | timestamp | yes | `null` | managed by Eloquent |
| `updated_at` | timestamp | yes | `null` | managed by Eloquent |
| `role` | varchar | no | `student` | added by custom migration |
| `department_id` | bigint unsigned | yes | `null` | foreign key to `departments.id`, `nullOnDelete()` |

Relationships:

- one `Department` has many `User` records
- one `User` has many `ServiceRequest` records

### `password_reset_tokens`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `email` | varchar | no | none | primary key |
| `token` | varchar | no | none | password reset token |
| `created_at` | timestamp | yes | `null` | timestamp of token creation |

### `sessions`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `id` | varchar | no | none | primary key |
| `user_id` | bigint unsigned | yes | `null` | indexed |
| `ip_address` | varchar(45) | yes | `null` | stores IPv4/IPv6 string |
| `user_agent` | text | yes | `null` | browser agent |
| `payload` | longtext | no | none | serialized session payload |
| `last_activity` | integer | no | none | indexed Unix timestamp |

### `service_requests`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | no | auto increment | primary key |
| `user_id` | bigint unsigned | no | none | foreign key to `users.id`, cascade delete |
| `department_id` | bigint unsigned | yes | `null` | foreign key to `departments.id`, `nullOnDelete()` |
| `title` | varchar | no | none | request title |
| `department` | varchar | no | none | current code uses this string field |
| `category` | varchar | no | none | current code uses this string field |
| `description` | text | no | none | request body |
| `status` | varchar | no | `pending` | values expected by the app: `pending`, `in_progress`, `completed` |
| `attachment_path` | varchar | yes | `null` | relative path stored on default disk |
| `attachment_original_name` | varchar | yes | `null` | original uploaded filename |
| `created_at` | timestamp | yes | `null` | managed by Eloquent |
| `updated_at` | timestamp | yes | `null` | managed by Eloquent |

Relationships:

- belongs to one `User`
- has an optional foreign key to `Department`, but that relationship is not currently modeled in PHP

### `departments`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | no | auto increment | primary key |
| `name` | varchar | no | none | unique |
| `created_at` | timestamp | yes | `null` | managed by Eloquent |
| `updated_at` | timestamp | yes | `null` | managed by Eloquent |

Relationships:

- has many `User` records
- has many `ServiceCategory` records

### `service_categories`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | no | auto increment | primary key |
| `name` | varchar | no | none | category name |
| `department_id` | bigint unsigned | yes | `null` | foreign key to `departments.id`, `nullOnDelete()` |
| `created_at` | timestamp | yes | `null` | managed by Eloquent |
| `updated_at` | timestamp | yes | `null` | managed by Eloquent |

### `cache`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `key` | varchar | no | none | primary key |
| `value` | mediumtext | no | none | cached payload |
| `expiration` | bigint | no | none | indexed expiration timestamp |

### `cache_locks`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `key` | varchar | no | none | primary key |
| `owner` | varchar | no | none | lock owner |
| `expiration` | bigint | no | none | indexed expiration timestamp |

### `jobs`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | no | auto increment | primary key |
| `queue` | varchar | no | none | indexed |
| `payload` | longtext | no | none | serialized job payload |
| `attempts` | tinyint unsigned | no | none | number of attempts |
| `reserved_at` | integer unsigned | yes | `null` | reservation timestamp |
| `available_at` | integer unsigned | no | none | availability timestamp |
| `created_at` | integer unsigned | no | none | creation timestamp |

### `job_batches`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `id` | varchar | no | none | primary key |
| `name` | varchar | no | none | batch name |
| `total_jobs` | integer | no | none | total jobs in batch |
| `pending_jobs` | integer | no | none | jobs not yet processed |
| `failed_jobs` | integer | no | none | count of failed jobs |
| `failed_job_ids` | longtext | no | none | serialized IDs |
| `options` | mediumtext | yes | `null` | serialized batch options |
| `cancelled_at` | integer | yes | `null` | cancellation timestamp |
| `created_at` | integer | no | none | creation timestamp |
| `finished_at` | integer | yes | `null` | completion timestamp |

### `failed_jobs`

| Column | Type | Null | Default | Notes |
| --- | --- | --- | --- | --- |
| `id` | bigint unsigned | no | auto increment | primary key |
| `uuid` | varchar | no | none | unique |
| `connection` | text | no | none | queue connection |
| `queue` | text | no | none | queue name |
| `payload` | longtext | no | none | serialized payload |
| `exception` | longtext | no | none | failure stack trace |
| `failed_at` | timestamp | no | current timestamp | failure time |

## Seed Data

`DatabaseSeeder` creates three login accounts and several demo student requests.

### Demo Users

| Role | Email | Password |
| --- | --- | --- |
| Student | `student@campusconnect.test` | `password` |
| Staff | `staff@campusconnect.test` | `password` |
| Admin | `admin@campusconnect.test` | `password` |

### Seeded Requests

The seeder creates or updates five request examples for the demo student, covering these departments and categories:

- Information Technology / Technical Support
- Maintenance / Facility Maintenance
- Registrar / Document Request
- Finance / Payment
- Library / General Inquiry

Important limitation: the seeder does not populate `departments` or `service_categories`, so the admin dashboard's department/category sections will remain empty unless those tables are seeded manually.

## Static Domain Values Used by the Student Form

These are hard-coded in `ServiceRequest` rather than loaded from `departments` and `service_categories`.

### Departments

- Information Technology
- Maintenance
- Registrar
- Finance
- Library
- Student Affairs

### Categories

- Technical Support
- Facility Maintenance
- Registration
- Payment
- Document Request
- General Inquiry

## Testing

Feature tests currently cover:

- role-based login redirects
- dashboard access restrictions by role
- student request creation
- student dashboard scoping
- request ownership enforcement
- attachment download
- admin dashboard rendering

Main test files:

- `tests/Feature/LoginFlowTest.php`
- `tests/Feature/StudentRequestFlowTest.php`
- `tests/Feature/AdminDashboardTest.php`

Run tests with:

```bash
composer test
```

## Local Setup

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Prepare environment

```bash
copy .env.example .env
php artisan key:generate
```

### 3. Configure the database

Set the database connection in `.env`, then run:

```bash
php artisan migrate
php artisan db:seed
```

### 4. Start the application

Option A:

```bash
composer dev
```

Option B:

```bash
php artisan serve
npm run dev
```

## Known Gaps and Inconsistencies

These points are worth knowing before extending the project:

1. `service_requests.department_id` exists in the schema but is not assigned in `StudentRequestController@store`.
2. `service_requests` still uses string columns `department` and `category` as the active source of truth.
3. `ServiceRequest` has no Eloquent relationship to `Department` or `ServiceCategory`.
4. The student dashboard uses static lists from `ServiceRequest::departments()` and `ServiceRequest::categories()` instead of database-backed department/category records.
5. The admin dashboard displays departments and categories, but no controller methods or routes exist yet for department/category CRUD.
6. The `AdminController` filters department lists and category lists, but the default seeder does not create any department/category records.
7. `DashboardController@admin()` exists but is unused.
8. The staff dashboard is presentational only and does not yet load or update service requests.
9. Admin placeholder routes (`/admin/users`, `/admin/departments`, `/admin/categories`, `/admin/reports`) return static text rather than separate feature pages.

## Suggested Next Refactor

The most valuable cleanup would be to unify request classification around relational data:

1. seed `departments` and `service_categories`
2. replace string-based `department` and `category` request fields with foreign keys
3. add missing Eloquent relationships on `ServiceRequest`
4. update the student form to load categories dynamically by department
5. build the actual staff workflow for reviewing and updating request status

