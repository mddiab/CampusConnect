<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceRequest;
use App\Models\ServiceCategory;
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
