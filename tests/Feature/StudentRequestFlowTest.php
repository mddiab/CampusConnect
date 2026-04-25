<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_a_service_request(): void
    {
        Storage::fake('local');

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

        $response = $this
            ->actingAs($student)
            ->post(route('student.requests.store'), [
                'title' => 'Printer not working in the library',
                'department_id' => $department->id,
                'service_category_id' => $category->id,
                'description' => 'The printer near the main study area shows an error and does not print student documents.',
                'attachment' => UploadedFile::fake()->create('printer-issue.pdf', 120, 'application/pdf'),
            ]);

        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $response->assertRedirect(route('student.requests.show', $serviceRequest));

        $this->assertDatabaseHas('service_requests', [
            'user_id' => $student->id,
            'title' => 'Printer not working in the library',
            'department_id' => $department->id,
            'service_category_id' => $category->id,
            'status' => ServiceRequest::STATUS_PENDING,
        ]);

        Storage::disk('local')->assertExists($serviceRequest->attachment_path);
    }

    public function test_student_cannot_submit_a_category_for_the_wrong_department(): void
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

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $response = $this
            ->actingAs($student)
            ->from(route('student.dashboard'))
            ->post(route('student.requests.store'), [
                'title' => 'Wrong category pairing',
                'department_id' => $informationTechnology->id,
                'service_category_id' => $maintenanceCategory->id,
                'description' => 'This request intentionally uses a category from a different department.',
            ]);

        $response
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHasErrors('service_category_id');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_student_dashboard_shows_only_the_logged_in_students_requests(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $otherStudent = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'title' => 'My own request',
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $otherStudent->id,
            'title' => 'Another student request',
        ]);

        $response = $this
            ->actingAs($student)
            ->get(route('student.dashboard'));

        $response
            ->assertOk()
            ->assertSee('My own request')
            ->assertDontSee('Another student request');
    }

    public function test_student_dashboard_filters_by_department_category_and_sorts_urgent_first(): void
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

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $itCategory->id,
            'title' => 'Standard IT request',
            'is_urgent' => false,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $itCategory->id,
            'title' => 'Urgent IT request',
            'is_urgent' => true,
        ]);

        ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'service_category_id' => $maintenanceCategory->id,
            'title' => 'Maintenance request',
            'is_urgent' => true,
        ]);

        $response = $this
            ->actingAs($student)
            ->get(route('student.dashboard', [
                'department_id' => $informationTechnology->id,
                'service_category_id' => $itCategory->id,
                'sort' => 'urgent',
            ]));

        $response
            ->assertOk()
            ->assertViewHas('activeRequests', function ($requests): bool {
                return $requests->getCollection()->pluck('title')->values()->all() === [
                    'Urgent IT request',
                    'Standard IT request',
                ];
            });
    }

    public function test_student_cannot_view_another_students_request(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $otherStudent = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $otherStudent->id,
        ]);

        $response = $this
            ->actingAs($student)
            ->get(route('student.requests.show', $serviceRequest));

        $response->assertForbidden();
    }

    public function test_student_can_add_a_reply_to_their_request_conversation(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $student->id,
        ]);

        $response = $this
            ->actingAs($student)
            ->post(route('student.requests.messages.store', $serviceRequest), [
                'message' => 'I can share the classroom number if that helps your team find the issue.',
            ]);

        $response->assertRedirect(route('student.requests.show', $serviceRequest));

        $this->assertDatabaseHas('service_request_messages', [
            'service_request_id' => $serviceRequest->id,
            'user_id' => $student->id,
            'author_name' => $student->name,
            'author_role' => User::ROLE_STUDENT,
            'message' => 'I can share the classroom number if that helps your team find the issue.',
        ]);
    }

    public function test_completed_request_records_first_student_view_time(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'status' => ServiceRequest::STATUS_COMPLETED,
            'first_completed_view_at' => null,
            'archived_at' => null,
        ]);

        $this
            ->actingAs($student)
            ->get(route('student.requests.show', $serviceRequest))
            ->assertOk();

        $serviceRequest->refresh();

        $this->assertNotNull($serviceRequest->first_completed_view_at);
        $this->assertNull($serviceRequest->archived_at);
    }

    public function test_student_dashboard_archives_completed_requests_after_view_window(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'title' => 'Completed request ready for archive',
            'status' => ServiceRequest::STATUS_COMPLETED,
            'first_completed_view_at' => now()->subHours(25),
            'archived_at' => null,
        ]);

        $this
            ->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('Completed request ready for archive');

        $this->assertNotNull($serviceRequest->refresh()->archived_at);
    }

    public function test_archived_request_is_read_only_for_student_replies(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'status' => ServiceRequest::STATUS_COMPLETED,
            'first_completed_view_at' => now()->subDays(2),
            'archived_at' => now()->subDay(),
        ]);

        $this
            ->actingAs($student)
            ->post(route('student.requests.messages.store', $serviceRequest), [
                'message' => 'This archived request should stay read-only.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('service_request_messages', 0);
    }

    public function test_student_cannot_reply_to_another_students_request_conversation(): void
    {
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $otherStudent = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $otherStudent->id,
        ]);

        $response = $this
            ->actingAs($student)
            ->post(route('student.requests.messages.store', $serviceRequest), [
                'message' => 'This reply should not be accepted.',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('service_request_messages', 0);
    }

    public function test_student_can_download_their_own_attachment(): void
    {
        Storage::fake('local');

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
        ]);

        Storage::disk('local')->put('service-requests/test-document.pdf', 'file content');

        $serviceRequest = ServiceRequest::factory()->create([
            'user_id' => $student->id,
            'attachment_path' => 'service-requests/test-document.pdf',
            'attachment_original_name' => 'request-document.pdf',
        ]);

        $response = $this
            ->actingAs($student)
            ->get(route('student.requests.attachment', $serviceRequest));

        $response->assertOk();
    }
}
