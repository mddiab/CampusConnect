<?php

namespace Database\Seeders;

use App\Models\ServiceRequest;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CampusStructureSeeder::class);

        $informationTechnology = ServiceCategory::query()
            ->where('name', 'Technical Support')
            ->whereHas('department', fn ($query) => $query->where('name', 'Information Technology'))
            ->firstOrFail();

        $maintenance = ServiceCategory::query()
            ->where('name', 'Facility Maintenance')
            ->whereHas('department', fn ($query) => $query->where('name', 'Maintenance'))
            ->firstOrFail();

        $registrar = ServiceCategory::query()
            ->where('name', 'Document Request')
            ->whereHas('department', fn ($query) => $query->where('name', 'Registrar'))
            ->firstOrFail();

        $finance = ServiceCategory::query()
            ->where('name', 'Payment')
            ->whereHas('department', fn ($query) => $query->where('name', 'Finance'))
            ->firstOrFail();

        $library = ServiceCategory::query()
            ->where('name', 'General Inquiry')
            ->whereHas('department', fn ($query) => $query->where('name', 'Library'))
            ->firstOrFail();

        $student = User::query()->updateOrCreate(
            ['email' => 'student@campusconnect.test'],
            [
                'name' => 'Student User',
                'password' => 'password',
                'role' => User::ROLE_STUDENT,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@campusconnect.test'],
            [
                'name' => 'Staff User',
                'password' => 'password',
                'role' => User::ROLE_STAFF,
                'department_id' => $informationTechnology->department_id,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@campusconnect.test'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ],
        );

        collect([
            [
                'title' => 'Internet connection problem in Building A classroom',
                'service_category_id' => $informationTechnology->id,
                'description' => 'The classroom internet disconnects repeatedly during lectures, making it difficult to access course materials and online submissions.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'title' => 'Air conditioning maintenance needed in Lecture Hall 3',
                'service_category_id' => $maintenance->id,
                'description' => 'The air conditioning in Lecture Hall 3 is not cooling properly during afternoon classes and the room becomes uncomfortable for students.',
                'status' => ServiceRequest::STATUS_IN_PROGRESS,
                'staff_notes' => 'Maintenance team scheduled an inspection for tomorrow morning.',
            ],
            [
                'title' => 'Official transcript request for scholarship submission',
                'service_category_id' => $registrar->id,
                'description' => 'An official transcript is needed for a scholarship application, and the student needs confirmation of the collection process and timing.',
                'status' => ServiceRequest::STATUS_COMPLETED,
                'staff_notes' => 'The transcript request was approved and prepared for pickup.',
            ],
            [
                'title' => 'Unable to access tuition payment portal',
                'service_category_id' => $finance->id,
                'description' => 'The payment portal shows an access error after login, preventing the student from reviewing fees and completing the current payment.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'title' => 'Library account issue for borrowed book renewal',
                'service_category_id' => $library->id,
                'description' => 'The library system does not allow renewal of a borrowed book even though the due date has not passed, and clarification is needed.',
                'status' => ServiceRequest::STATUS_IN_PROGRESS,
                'staff_notes' => 'Library desk is checking the loan record and will update the student by email.',
            ],
        ])->each(function (array $requestData) use ($student): void {
            ServiceRequest::query()->updateOrCreate(
                [
                    'user_id' => $student->id,
                    'title' => $requestData['title'],
                ],
                [
                    'department_id' => ServiceCategory::query()
                        ->findOrFail($requestData['service_category_id'])
                        ->department_id,
                    'service_category_id' => $requestData['service_category_id'],
                    'description' => $requestData['description'],
                    'status' => $requestData['status'],
                    'staff_notes' => $requestData['staff_notes'] ?? null,
                    'resolved_at' => $requestData['status'] === ServiceRequest::STATUS_COMPLETED ? now() : null,
                    'attachment_path' => null,
                    'attachment_original_name' => null,
                ],
            );
        });
    }
}
