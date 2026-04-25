<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_department(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.departments.store'), [
                'name' => 'Health Services',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('departments', [
            'name' => 'Health Services',
        ]);
    }

    public function test_admin_can_create_a_service_category(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $department = Department::query()
            ->where('name', 'Finance')
            ->firstOrFail();

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'department_id' => $department->id,
                'name' => 'Refund Request',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('service_categories', [
            'department_id' => $department->id,
            'name' => 'Refund Request',
        ]);
    }

    public function test_admin_cannot_delete_a_category_that_is_still_used_by_requests(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $department = Department::query()
            ->where('name', 'Information Technology')
            ->firstOrFail();

        $category = ServiceCategory::query()
            ->where('name', 'Technical Support')
            ->where('department_id', $department->id)
            ->firstOrFail();

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $category->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect();

        $this->assertDatabaseHas('service_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_cannot_move_a_category_that_is_still_used_by_requests(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $informationTechnology = Department::query()
            ->where('name', 'Information Technology')
            ->firstOrFail();

        $maintenance = Department::query()
            ->where('name', 'Maintenance')
            ->firstOrFail();

        $category = ServiceCategory::query()
            ->where('name', 'Technical Support')
            ->where('department_id', $informationTechnology->id)
            ->firstOrFail();

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $category->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->put(route('admin.categories.update', $category), [
                'name' => $category->name,
                'department_id' => $maintenance->id,
            ]);

        $response
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors('department_id');

        $this->assertSame($informationTechnology->id, $category->refresh()->department_id);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this
            ->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_cannot_delete_user_with_request_history(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
        ]);

        $this
            ->actingAs($admin)
            ->delete(route('admin.users.destroy', $student))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
        ]);
    }

    public function test_admin_reports_page_loads_and_can_export_csv(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $department = Department::query()
            ->where('name', 'Finance')
            ->firstOrFail();

        $category = ServiceCategory::query()
            ->where('name', 'Payment')
            ->where('department_id', $department->id)
            ->firstOrFail();

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $category->id,
            'title' => 'Payment gateway timeout issue',
        ]);

        $pageResponse = $this
            ->actingAs($admin)
            ->get(route('admin.reports'));

        $pageResponse
            ->assertOk()
            ->assertSee('Reports')
            ->assertSee('Payment gateway timeout issue');

        $exportResponse = $this
            ->actingAs($admin)
            ->get(route('admin.reports.export'));

        $exportResponse->assertOk();
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString($serviceRequest->title, $exportResponse->streamedContent());
    }

    public function test_admin_cannot_create_more_than_three_staff_accounts_for_a_department(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $department = Department::query()
            ->where('name', 'Finance')
            ->firstOrFail();

        User::factory()->count(3)->create([
            'role' => User::ROLE_STAFF,
            'department_id' => $department->id,
            'password' => Hash::make('password'),
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->post(route('admin.users.store'), [
                'name' => 'Finance Overflow',
                'email' => 'finance-overflow@example.com',
                'role' => User::ROLE_STAFF,
                'department_id' => $department->id,
                'password' => 'password123',
            ]);

        $response
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasErrors('department_id');

        $this->assertDatabaseMissing('users', [
            'email' => 'finance-overflow@example.com',
        ]);
    }
}
