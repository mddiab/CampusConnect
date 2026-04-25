<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
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
                'is_urgent' => true,
                'staff_notes' => 'The network team is tracing the failed switch connection.',
            ]);

        $response->assertRedirect(route('staff.requests.show', $serviceRequest));

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => ServiceRequest::STATUS_IN_PROGRESS,
            'is_urgent' => true,
            'staff_notes' => 'The network team is tracing the failed switch connection.',
        ]);
    }

    public function test_staff_can_change_priority_for_their_department_ticket(): void
    {
        $department = Department::query()
            ->where('name', 'Maintenance')
            ->firstOrFail();

        $category = ServiceCategory::query()
            ->where('name', 'Facility Maintenance')
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
            'is_urgent' => false,
        ]);

        $response = $this
            ->actingAs($staff)
            ->patch(route('staff.requests.update', $serviceRequest), [
                'status' => $serviceRequest->status,
                'is_urgent' => true,
                'staff_notes' => 'Priority escalated after the student reported a worsening classroom issue.',
            ]);

        $response->assertRedirect(route('staff.requests.show', $serviceRequest));

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'is_urgent' => true,
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
                'is_urgent' => false,
                'staff_notes' => 'This should not be allowed.',
            ]);

        $response->assertForbidden();
    }

    public function test_staff_cannot_open_archived_department_request(): void
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
            'status' => ServiceRequest::STATUS_COMPLETED,
            'first_completed_view_at' => now()->subDays(2),
            'archived_at' => now()->subDay(),
        ]);

        $this
            ->actingAs($staff)
            ->get(route('staff.requests.show', $serviceRequest))
            ->assertForbidden();
    }

    public function test_staff_can_add_a_reply_to_request_conversation_for_their_department(): void
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
        ]);

        $response = $this
            ->actingAs($staff)
            ->post(route('staff.requests.messages.store', $serviceRequest), [
                'message' => 'We have scheduled a technician to inspect the classroom network this afternoon.',
            ]);

        $response->assertRedirect(route('staff.requests.show', $serviceRequest));

        $this->assertDatabaseHas('service_request_messages', [
            'service_request_id' => $serviceRequest->id,
            'user_id' => $staff->id,
            'author_name' => $staff->name,
            'author_role' => User::ROLE_STAFF,
            'message' => 'We have scheduled a technician to inspect the classroom network this afternoon.',
        ]);
    }

    public function test_staff_cannot_reply_to_request_conversation_for_another_department(): void
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
            ->post(route('staff.requests.messages.store', $serviceRequest), [
                'message' => 'This reply should not be accepted.',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('service_request_messages', 0);
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

    public function test_staff_dashboard_can_filter_urgent_requests_for_their_department(): void
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

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $category->id,
            'title' => 'Urgent network outage',
            'is_urgent' => true,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $category->id,
            'title' => 'Routine software install',
            'is_urgent' => false,
        ]);

        $response = $this
            ->actingAs($staff)
            ->get(route('staff.dashboard', ['priority' => 'urgent']));

        $response
            ->assertOk()
            ->assertSee('Urgent network outage')
            ->assertDontSee('Routine software install');
    }
}
