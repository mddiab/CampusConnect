<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceRequest;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_dashboard_shows_only_requests_for_their_department(): void
    {
        $informationTechnology = Department::query()
            ->where('name', 'Information Technology')
            ->firstOrFail();

        $maintenance = Department::query()
            ->where('name', 'Maintenance')
            ->firstOrFail();

        $itCategory = ServiceCategory::query()
            ->where('name', 'Technical Support')
            ->where('department_id', $informationTechnology->id)
            ->firstOrFail();

        $maintenanceCategory = ServiceCategory::query()
            ->where('name', 'Facility Maintenance')
            ->where('department_id', $maintenance->id)
            ->firstOrFail();

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'department_id' => $informationTechnology->id,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $itCategory->id,
            'title' => 'Network outage in lab',
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $maintenanceCategory->id,
            'title' => 'Broken classroom projector mount',
        ]);

        $response = $this
            ->actingAs($staff)
            ->get(route('staff.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Network outage in lab')
            ->assertDontSee('Broken classroom projector mount');
    }

    public function test_staff_can_update_request_status_and_notes_for_their_department(): void
    {
        $department = Department::query()
            ->where('name', 'Information Technology')
            ->firstOrFail();

        $category = ServiceCategory::query()
            ->where('name', 'Technical Support')
            ->where('department_id', $department->id)
            ->firstOrFail();

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'department_id' => $department->id,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $category->id,
            'status' => ServiceRequest::STATUS_PENDING,
        ]);

        $response = $this
            ->actingAs($staff)
            ->patch(route('staff.requests.update', $serviceRequest), [
                'status' => ServiceRequest::STATUS_IN_PROGRESS,
                'staff_notes' => 'The network team is tracing the failed switch connection.',
            ]);

        $response->assertRedirect(route('staff.requests.show', $serviceRequest));

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => ServiceRequest::STATUS_IN_PROGRESS,
            'staff_notes' => 'The network team is tracing the failed switch connection.',
        ]);
    }

    public function test_staff_cannot_update_request_for_another_department(): void
    {
        $informationTechnology = Department::query()
            ->where('name', 'Information Technology')
            ->firstOrFail();

        $maintenance = Department::query()
            ->where('name', 'Maintenance')
            ->firstOrFail();

        $maintenanceCategory = ServiceCategory::query()
            ->where('name', 'Facility Maintenance')
            ->where('department_id', $maintenance->id)
            ->firstOrFail();

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'department_id' => $informationTechnology->id,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $maintenanceCategory->id,
        ]);

        $response = $this
            ->actingAs($staff)
            ->patch(route('staff.requests.update', $serviceRequest), [
                'status' => ServiceRequest::STATUS_COMPLETED,
                'staff_notes' => 'This should not be allowed.',
            ]);

        $response->assertForbidden();
    }

    public function test_staff_can_download_attachment_for_department_request(): void
    {
        Storage::fake('local');

        $department = Department::query()
            ->where('name', 'Information Technology')
            ->firstOrFail();

        $category = ServiceCategory::query()
            ->where('name', 'Technical Support')
            ->where('department_id', $department->id)
            ->firstOrFail();

        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
            'department_id' => $department->id,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        Storage::disk('local')->put('service-requests/staff-download.pdf', 'file content');

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $category->id,
            'attachment_path' => 'service-requests/staff-download.pdf',
            'attachment_original_name' => 'staff-download.pdf',
        ]);

        $response = $this
            ->actingAs($staff)
            ->get(route('staff.requests.attachment', $serviceRequest));

        $response->assertOk();
    }
}
