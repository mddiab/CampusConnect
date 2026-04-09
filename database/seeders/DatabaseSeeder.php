<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(CampusStructureSeeder::class);

        $categories = $this->categoryMap();
        $students = $this->seedStudents();
        $this->seedStaff($categories);
        $this->seedAdmins();
        $this->seedRequests($students, $categories);
    }

    /**
     * @return array<string, ServiceCategory>
     */
    private function categoryMap(): array
    {
        return [
            'it.support' => $this->findCategory('Information Technology', 'Technical Support'),
            'it.inquiry' => $this->findCategory('Information Technology', 'General Inquiry'),
            'maintenance.facility' => $this->findCategory('Maintenance', 'Facility Maintenance'),
            'maintenance.inquiry' => $this->findCategory('Maintenance', 'General Inquiry'),
            'registrar.registration' => $this->findCategory('Registrar', 'Registration'),
            'registrar.document' => $this->findCategory('Registrar', 'Document Request'),
            'finance.payment' => $this->findCategory('Finance', 'Payment'),
            'finance.inquiry' => $this->findCategory('Finance', 'General Inquiry'),
            'library.document' => $this->findCategory('Library', 'Document Request'),
            'library.inquiry' => $this->findCategory('Library', 'General Inquiry'),
            'student-affairs.inquiry' => $this->findCategory('Student Affairs', 'General Inquiry'),
        ];
    }

    /**
     * @return array<string, User>
     */
    private function seedStudents(): array
    {
        $studentDefinitions = [
            ['email' => 'student@campusconnect.test', 'name' => 'Student One'],
            ['email' => 'student2@campusconnect.test', 'name' => 'Student Two'],
            ['email' => 'student3@campusconnect.test', 'name' => 'Student Three'],
            ['email' => 'student4@campusconnect.test', 'name' => 'Student Four'],
            ['email' => 'student5@campusconnect.test', 'name' => 'Student Five'],
            ['email' => 'student6@campusconnect.test', 'name' => 'Student Six'],
            ['email' => 'student7@campusconnect.test', 'name' => 'Student Seven'],
            ['email' => 'student8@campusconnect.test', 'name' => 'Student Eight'],
            ['email' => 'student9@campusconnect.test', 'name' => 'Student Nine'],
            ['email' => 'student10@campusconnect.test', 'name' => 'Student Ten'],
        ];

        $students = [];

        foreach ($studentDefinitions as $definition) {
            $students[$definition['email']] = User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => 'password',
                    'role' => User::ROLE_STUDENT,
                    'department_id' => null,
                    'email_verified_at' => now(),
                ],
            );
        }

        return $students;
    }

    /**
     * @param array<string, ServiceCategory> $categories
     */
    private function seedStaff(array $categories): void
    {
        $staffDefinitions = [
            ['email' => 'staff@campusconnect.test', 'name' => 'IT Staff', 'category' => 'it.support'],
            ['email' => 'staff.maintenance@campusconnect.test', 'name' => 'Maintenance Staff', 'category' => 'maintenance.facility'],
            ['email' => 'staff.registrar@campusconnect.test', 'name' => 'Registrar Staff', 'category' => 'registrar.document'],
            ['email' => 'staff.finance@campusconnect.test', 'name' => 'Finance Staff', 'category' => 'finance.payment'],
            ['email' => 'staff.library@campusconnect.test', 'name' => 'Library Staff', 'category' => 'library.document'],
            ['email' => 'staff.affairs@campusconnect.test', 'name' => 'Student Affairs Staff', 'category' => 'student-affairs.inquiry'],
        ];

        foreach ($staffDefinitions as $definition) {
            User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => 'password',
                    'role' => User::ROLE_STAFF,
                    'department_id' => $categories[$definition['category']]->department_id,
                    'email_verified_at' => now(),
                ],
            );
        }
    }

    private function seedAdmins(): void
    {
        $adminDefinitions = [
            ['email' => 'admin@campusconnect.test', 'name' => 'Admin One'],
            ['email' => 'admin2@campusconnect.test', 'name' => 'Admin Two'],
            ['email' => 'admin3@campusconnect.test', 'name' => 'Admin Three'],
        ];

        foreach ($adminDefinitions as $definition) {
            User::query()->updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'password' => 'password',
                    'role' => User::ROLE_ADMIN,
                    'department_id' => null,
                    'email_verified_at' => now(),
                ],
            );
        }
    }

    /**
     * @param array<string, User> $students
     * @param array<string, ServiceCategory> $categories
     */
    private function seedRequests(array $students, array $categories): void
    {
        $requestDefinitions = [
            [
                'student_email' => 'student@campusconnect.test',
                'title' => 'Internet connection problem in Building A classroom',
                'category' => 'it.support',
                'description' => 'The classroom internet disconnects repeatedly during lectures, making it difficult to access course materials and online submissions.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'student_email' => 'student2@campusconnect.test',
                'title' => 'Projector in Lab 2 is not displaying HDMI output',
                'category' => 'it.support',
                'description' => 'The projector powers on but does not show any laptop signal when lecturers connect through HDMI.',
                'status' => ServiceRequest::STATUS_IN_PROGRESS,
                'staff_notes' => 'IT staff is testing the projector cable and adapter set.',
            ],
            [
                'student_email' => 'student3@campusconnect.test',
                'title' => 'Air conditioning maintenance needed in Lecture Hall 3',
                'category' => 'maintenance.facility',
                'description' => 'The air conditioning in Lecture Hall 3 is not cooling properly during afternoon classes.',
                'status' => ServiceRequest::STATUS_IN_PROGRESS,
                'staff_notes' => 'Maintenance inspection has been scheduled for tomorrow morning.',
            ],
            [
                'student_email' => 'student4@campusconnect.test',
                'title' => 'Broken chair in engineering classroom',
                'category' => 'maintenance.facility',
                'description' => 'One of the classroom chairs has a damaged leg and is unsafe to use.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'student_email' => 'student5@campusconnect.test',
                'title' => 'Official transcript request for scholarship submission',
                'category' => 'registrar.document',
                'description' => 'An official transcript is needed for a scholarship application this week.',
                'status' => ServiceRequest::STATUS_COMPLETED,
                'staff_notes' => 'The transcript request was approved and prepared for pickup.',
            ],
            [
                'student_email' => 'student6@campusconnect.test',
                'title' => 'Add/drop registration portal still shows a previous course',
                'category' => 'registrar.registration',
                'description' => 'After dropping a course, the registration portal still lists it as active in the schedule view.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'student_email' => 'student7@campusconnect.test',
                'title' => 'Unable to access tuition payment portal',
                'category' => 'finance.payment',
                'description' => 'The payment portal shows an access error after login and does not load the balance page.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'student_email' => 'student8@campusconnect.test',
                'title' => 'Receipt for previous semester payment needed',
                'category' => 'finance.inquiry',
                'description' => 'A payment receipt is required for reimbursement paperwork and needs to include the previous semester total.',
                'status' => ServiceRequest::STATUS_COMPLETED,
                'staff_notes' => 'Receipt generated and sent to the student email address.',
            ],
            [
                'student_email' => 'student9@campusconnect.test',
                'title' => 'Library account issue for borrowed book renewal',
                'category' => 'library.inquiry',
                'description' => 'The library system does not allow renewal of a borrowed book even though the due date has not passed.',
                'status' => ServiceRequest::STATUS_IN_PROGRESS,
                'staff_notes' => 'Library desk is checking the loan record and will update the student by email.',
            ],
            [
                'student_email' => 'student10@campusconnect.test',
                'title' => 'Request for reserve copy of a missing textbook',
                'category' => 'library.document',
                'description' => 'The required textbook is missing from the shelf and a reserve desk copy is needed for exam preparation.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'student_email' => 'student@campusconnect.test',
                'title' => 'Need advice appointment for student club registration',
                'category' => 'student-affairs.inquiry',
                'description' => 'The student club application requires clarification on the approval process and next steps.',
                'status' => ServiceRequest::STATUS_COMPLETED,
                'staff_notes' => 'Student Affairs shared the appointment slot and registration checklist.',
            ],
            [
                'student_email' => 'student2@campusconnect.test',
                'title' => 'Campus ID card not activating library gate access',
                'category' => 'library.inquiry',
                'description' => 'The campus ID card is valid for classes but does not open the library gate entrance.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'student_email' => 'student3@campusconnect.test',
                'title' => 'Need confirmation on fee installment deadlines',
                'category' => 'finance.inquiry',
                'description' => 'Clarification is needed on the remaining fee installment deadlines for this semester.',
                'status' => ServiceRequest::STATUS_IN_PROGRESS,
                'staff_notes' => 'Finance team is preparing the installment schedule summary.',
            ],
            [
                'student_email' => 'student4@campusconnect.test',
                'title' => 'Student portal password reset email never arrives',
                'category' => 'it.inquiry',
                'description' => 'The student portal requests a password reset but the reset email never appears in the inbox.',
                'status' => ServiceRequest::STATUS_PENDING,
            ],
            [
                'student_email' => 'student5@campusconnect.test',
                'title' => 'Requesting replacement classroom whiteboard markers',
                'category' => 'maintenance.inquiry',
                'description' => 'The markers in Room C-12 are dry and need replacement before the afternoon lab.',
                'status' => ServiceRequest::STATUS_COMPLETED,
                'staff_notes' => 'Fresh marker set delivered to the room cabinet.',
            ],
        ];

        foreach ($requestDefinitions as $definition) {
            $student = $students[$definition['student_email']];
            $category = $categories[$definition['category']];

            ServiceRequest::query()->updateOrCreate(
                [
                    'user_id' => $student->id,
                    'title' => $definition['title'],
                ],
                [
                    'department_id' => $category->department_id,
                    'service_category_id' => $category->id,
                    'description' => $definition['description'],
                    'status' => $definition['status'],
                    'staff_notes' => $definition['staff_notes'] ?? null,
                    'resolved_at' => $definition['status'] === ServiceRequest::STATUS_COMPLETED ? now() : null,
                    'attachment_path' => null,
                    'attachment_original_name' => null,
                ],
            );
        }
    }

    private function findCategory(string $departmentName, string $categoryName): ServiceCategory
    {
        return ServiceCategory::query()
            ->where('name', $categoryName)
            ->whereHas('department', fn ($query) => $query->where('name', $departmentName))
            ->firstOrFail();
    }
}
