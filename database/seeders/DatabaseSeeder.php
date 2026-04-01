<?php

namespace Database\Seeders;

use App\Models\ServiceRequest;
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
                'department' => 'Information Technology',
                'category' => 'Technical Support',
                'description' => 'The classroom internet disconnects repeatedly during lectures, making it difficult to access course materials and online submissions.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'title' => 'Air conditioning maintenance needed in Lecture Hall 3',
                'department' => 'Maintenance',
                'category' => 'Facility Maintenance',
                'description' => 'The air conditioning in Lecture Hall 3 is not cooling properly during afternoon classes and the room becomes uncomfortable for students.',
                'status' => ServiceRequest::STATUS_IN_PROGRESS,
            ],
            [
                'title' => 'Official transcript request for scholarship submission',
                'department' => 'Registrar',
                'category' => 'Document Request',
                'description' => 'An official transcript is needed for a scholarship application, and the student needs confirmation of the collection process and timing.',
                'status' => ServiceRequest::STATUS_COMPLETED,
            ],
            [
                'title' => 'Unable to access tuition payment portal',
                'department' => 'Finance',
                'category' => 'Payment',
                'description' => 'The payment portal shows an access error after login, preventing the student from reviewing fees and completing the current payment.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'title' => 'Library account issue for borrowed book renewal',
                'department' => 'Library',
                'category' => 'General Inquiry',
                'description' => 'The library system does not allow renewal of a borrowed book even though the due date has not passed, and clarification is needed.',
                'status' => ServiceRequest::STATUS_IN_PROGRESS,
            ],
        ])->each(function (array $requestData) use ($student): void {
            ServiceRequest::query()->updateOrCreate(
                [
                    'user_id' => $student->id,
                    'title' => $requestData['title'],
                ],
                [
                    'department' => $requestData['department'],
                    'category' => $requestData['category'],
                    'description' => $requestData['description'],
                    'status' => $requestData['status'],
                    'attachment_path' => null,
                    'attachment_original_name' => null,
                ],
            );
        });
    }
}
