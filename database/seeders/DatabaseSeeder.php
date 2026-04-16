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
        $this->seedRequests(
            $students,
            $categories,
            User::query()
                ->where('role', User::ROLE_STAFF)
                ->get()
                ->keyBy('department_id')
                ->all(),
        );
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
     * @param array<int, User> $staffByDepartment
     */
    private function seedRequests(array $students, array $categories, array $staffByDepartment): void
    {
        ServiceRequest::query()
            ->whereIn('user_id', array_map(fn (User $student) => $student->id, $students))
            ->delete();

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

        $requestDefinitions = [
            ...$requestDefinitions,
            ...$this->generatedRequestDefinitions(array_keys($students)),
        ];

        foreach ($requestDefinitions as $definition) {
            $student = $students[$definition['student_email']];
            $category = $categories[$definition['category']];

            $serviceRequest = ServiceRequest::query()->updateOrCreate(
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

            $this->seedConversationMessages(
                $serviceRequest,
                $student,
                $staffByDepartment[$category->department_id] ?? null,
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

    /**
     * @param array<int, string> $studentEmails
     * @return array<int, array<string, string>>
     */
    private function generatedRequestDefinitions(array $studentEmails): array
    {
        $templates = [
            [
                'category' => 'it.support',
                'description_template' => 'The issue was reported in %s.',
                'titles' => [
                    'Wi-Fi weak in Science Hall 2',
                    'Projector not detecting HDMI in Lab 4',
                    'Classroom PC stuck on login in B-101',
                    'Smart board frozen in Room C-12',
                    'No sound from speakers in Lecture Hall 5',
                    'Printer queue stalled in the CAD Lab',
                    'Microphone cutting out in Auditorium East',
                    'Campus kiosk frozen near the registrar office',
                ],
                'contexts' => [
                    'Science Hall 2',
                    'Lab 4',
                    'Room B-101',
                    'Room C-12',
                    'Lecture Hall 5',
                    'the CAD Lab',
                    'Auditorium East',
                    'the kiosk near the registrar office',
                ],
                'needs' => [
                    'a wireless coverage check',
                    'an HDMI port and cable test',
                    'a login and profile reset',
                    'a panel restart and calibration check',
                    'an audio output check',
                    'a print service restart',
                    'a microphone and receiver check',
                    'a kiosk reboot and app health check',
                ],
            ],
            [
                'category' => 'it.inquiry',
                'description_template' => 'The request is related to %s.',
                'titles' => [
                    'Password reset email missing from portal',
                    'Course page not showing new announcements',
                    'Student email not syncing on phone',
                    'MFA code not arriving for campus login',
                    'LMS profile photo not updating',
                    'Account dashboard showing outdated timetable',
                    'Campus app notifications not coming through',
                    'Software license request still pending',
                ],
                'contexts' => [
                    'the student portal recovery flow',
                    'the course announcements feed',
                    'the campus email setup',
                    'the campus login verification step',
                    'the LMS profile settings page',
                    'the student dashboard schedule card',
                    'the mobile campus app notification settings',
                    'the academic software request queue',
                ],
                'needs' => [
                    'a check of the email delivery logs',
                    'a cache refresh and account sync check',
                    'help confirming the mail server settings',
                    'a review of the verification channel',
                    'a profile sync refresh',
                    'a schedule sync review',
                    'a push notification settings review',
                    'confirmation of the software request status',
                ],
            ],
            [
                'category' => 'maintenance.facility',
                'description_template' => 'The issue was reported in %s.',
                'titles' => [
                    'AC too warm in Lecture Hall 3',
                    'Ceiling light out in Stairwell B',
                    'Water leak under sink in Student Lounge',
                    'Broken chair in Engineering Room 210',
                    'Restroom hand dryer not working in Block A',
                    'Window blind detached in Seminar Room 6',
                    'Loose floor tile near Library Entrance',
                    'Door closer broken on Room D-14',
                ],
                'contexts' => [
                    'Lecture Hall 3',
                    'Stairwell B',
                    'the Student Lounge sink area',
                    'Engineering Room 210',
                    'the Block A restroom',
                    'Seminar Room 6',
                    'the Library entrance',
                    'Room D-14',
                ],
                'needs' => [
                    'an air conditioning inspection',
                    'a lighting replacement',
                    'a plumbing repair',
                    'a furniture safety check',
                    'an electrical maintenance check',
                    'a fixture reinstallation',
                    'a flooring repair',
                    'a door hardware repair',
                ],
            ],
            [
                'category' => 'maintenance.inquiry',
                'description_template' => 'The request is for %s.',
                'titles' => [
                    'Need extra chairs for review session',
                    'Need whiteboard cleaning in Room C-12',
                    'Need bin pickup after club fair',
                    'Need podium setup in Conference Hall',
                    'Need extension cords for workshop tables',
                    'Need fan check before evening class',
                    'Need marker refill in Study Room 4',
                    'Need restroom supply restock in Building E',
                ],
                'contexts' => [
                    'the Friday review session',
                    'Room C-12',
                    'the club fair area',
                    'Conference Hall',
                    'the workshop tables in Lab Annex',
                    'the evening class in Hall B',
                    'Study Room 4',
                    'Building E',
                ],
                'needs' => [
                    'temporary seating support',
                    'a cleaning visit',
                    'a housekeeping pickup',
                    'a room setup check',
                    'temporary power support',
                    'a ventilation check',
                    'classroom supply restocking',
                    'a custodial restock',
                ],
            ],
            [
                'category' => 'registrar.registration',
                'description_template' => 'The request is related to %s.',
                'titles' => [
                    'Waitlist still showing for BIO 220',
                    'Dropped course still on weekly timetable',
                    'Approved overload not visible in portal',
                    'Section swap request not reflecting',
                    'Lab component missing from enrolled subject',
                    'Registration hold not cleared after advising',
                    'Elective approval not linked to account',
                    'Incorrect year level shown in registration page',
                ],
                'contexts' => [
                    'the BIO 220 registration record',
                    'the weekly timetable view',
                    'the overload approval entry',
                    'the section change request',
                    'the enrolled subject components list',
                    'the advising clearance update',
                    'the elective approval record',
                    'the registration profile',
                ],
                'needs' => [
                    'verification that the record was updated',
                    'a timetable sync refresh',
                    'a check of the overload approval posting',
                    'confirmation that the swap was applied',
                    'a registration record correction',
                    'a clearance status refresh',
                    'a review of the elective mapping',
                    'a profile correction in the registration system',
                ],
            ],
            [
                'category' => 'registrar.document',
                'description_template' => 'The request is for %s.',
                'titles' => [
                    'Need enrollment certificate for internship',
                    'Need transcript copy for embassy file',
                    'Need good moral certificate for scholarship',
                    'Need registration form copy for visa renewal',
                    'Need graduation clearance summary for employer',
                    'Need certification of units for transfer application',
                    'Need sealed grades report for board review',
                    'Need dean\'s list confirmation for sponsorship',
                ],
                'contexts' => [
                    'the internship application packet',
                    'the embassy file',
                    'the scholarship renewal',
                    'the visa renewal packet',
                    'the employer onboarding file',
                    'the transfer application',
                    'the board review requirements',
                    'the sponsorship paperwork',
                ],
                'needs' => [
                    'confirmation of the release timeline',
                    'expedited processing guidance',
                    'the document preparation schedule',
                    'the pickup steps and turnaround time',
                    'confirmation of the required supporting records',
                    'the release requirements checklist',
                    'the expected release date',
                    'instructions for document claim',
                ],
            ],
            [
                'category' => 'finance.payment',
                'description_template' => 'The request is related to %s.',
                'titles' => [
                    'Payment page times out before confirmation',
                    'Online receipt not generating after payment',
                    'Card payment marked failed but amount deducted',
                    'Balance still unpaid after bank transfer',
                    'Installment button disabled on finance portal',
                    'QR payment screen not loading on mobile',
                    'Payment confirmation email not received',
                    'Duplicate charge showing for laboratory fee',
                ],
                'contexts' => [
                    'the final confirmation step on the payment page',
                    'the online receipt screen',
                    'the recent card payment attempt',
                    'the posted bank transfer for tuition',
                    'the installment plan page',
                    'the QR payment flow on mobile',
                    'the payment confirmation message',
                    'the laboratory fee ledger entry',
                ],
                'needs' => [
                    'an investigation into the timeout',
                    'help regenerating the receipt',
                    'a review of the payment gateway result',
                    'a posted payment reconciliation',
                    'a finance portal access check',
                    'a mobile payment flow review',
                    'confirmation that the payment was recorded',
                    'a check for duplicate billing',
                ],
            ],
            [
                'category' => 'finance.inquiry',
                'description_template' => 'The request is related to %s.',
                'titles' => [
                    'Scholarship credit missing from account summary',
                    'Installment due date unclear on ledger',
                    'Overpayment not reflected in balance',
                    'Refund request status not visible',
                    'Miscellaneous fee breakdown looks incorrect',
                    'Clearance hold still showing after payment',
                    'Balance summary not matching cashier receipt',
                    'Payment plan change not reflected in portal',
                ],
                'contexts' => [
                    'the current account summary',
                    'the student ledger due dates',
                    'the posted balance after recent payment',
                    'the refund request tracking view',
                    'the miscellaneous fee breakdown',
                    'the clearance status after settlement',
                    'the cashier receipt and online summary',
                    'the payment plan record',
                ],
                'needs' => [
                    'a scholarship posting review',
                    'clarification of the due schedule',
                    'an updated balance review',
                    'confirmation of the refund workflow',
                    'a fee breakdown explanation',
                    'a clearance status update',
                    'reconciliation of the two records',
                    'a portal update for the new payment plan',
                ],
            ],
            [
                'category' => 'library.document',
                'description_template' => 'The request is for %s.',
                'titles' => [
                    'Need scan of archived journal article',
                    'Need reserve copy of statistics textbook',
                    'Need thesis abstract copy for research proposal',
                    'Need chapter scan from reference book',
                    'Need citation page from bound journal issue',
                    'Need library endorsement for interlibrary loan',
                    'Need digital copy of conference proceeding',
                    'Need reserve access for missing shelf copy',
                ],
                'contexts' => [
                    'the archived journal issue needed for class',
                    'the statistics textbook used this week',
                    'the thesis reference needed for the proposal',
                    'the reference book chapter for tomorrow\'s class',
                    'the bound journal issue at the circulation desk',
                    'the interlibrary loan request',
                    'the conference proceeding needed for research',
                    'the missing shelf copy request',
                ],
                'needs' => [
                    'help locating or scanning the material',
                    'a short-term reserve copy',
                    'access to the abstract or catalog entry',
                    'a scan request before the deadline',
                    'a document retrieval from storage',
                    'confirmation that the request can be endorsed',
                    'a digital access check',
                    'temporary reserve access while the shelf copy is missing',
                ],
            ],
            [
                'category' => 'library.inquiry',
                'description_template' => 'The request is related to %s.',
                'titles' => [
                    'Renewal blocked with no active fines',
                    'Library gate not reading campus ID',
                    'Borrowed book missing from account history',
                    'Study room booking not appearing in portal',
                    'E-book access denied off campus',
                    'Hold request not moving to pickup shelf',
                    'Library email notice sent to old address',
                    'Fine waiver request still pending review',
                ],
                'contexts' => [
                    'the renewal screen for a current loan',
                    'the library entrance gate',
                    'the borrowing history page',
                    'the study room booking portal',
                    'the off-campus e-book login',
                    'the hold request tracking view',
                    'the library notification email settings',
                    'the fine waiver review queue',
                ],
                'needs' => [
                    'a check of the borrowing restrictions',
                    'an ID access sync review',
                    'a refresh of the loan history record',
                    'confirmation that the booking was saved',
                    'help restoring remote access',
                    'confirmation of the hold request status',
                    'an update to the library contact details',
                    'a review of the waiver status',
                ],
            ],
            [
                'category' => 'student-affairs.inquiry',
                'description_template' => 'The request is for %s.',
                'titles' => [
                    'Need event approval checklist for club launch',
                    'Need absence excuse guidance for medical leave',
                    'Need volunteer hours confirmation for scholarship',
                    'Need counseling appointment slot this week',
                    'Need conduct clearance update for internship',
                    'Need travel memo requirements for competition',
                    'Need orientation attendance correction',
                    'Need student organization adviser approval steps',
                ],
                'contexts' => [
                    'the upcoming club launch',
                    'the medical leave absence request',
                    'the scholarship volunteer hours record',
                    'this week\'s counseling schedule',
                    'the internship clearance process',
                    'the competition travel memo',
                    'the orientation attendance record',
                    'the organization adviser approval process',
                ],
                'needs' => [
                    'the remaining forms and approvals',
                    'guidance on the required documents',
                    'confirmation of the recorded hours',
                    'the earliest available appointment',
                    'a status update on the clearance',
                    'the list of required travel documents',
                    'a correction to the attendance record',
                    'the next approval steps',
                ],
            ],
        ];

        $timePhrases = [
            'before the next lecture block',
            'before the end of this week',
            'as soon as possible for an upcoming deadline',
            'before the next assessment window',
            'ahead of the department review meeting',
        ];

        $statusCycle = [
            ServiceRequest::STATUS_PENDING,
            ServiceRequest::STATUS_IN_PROGRESS,
            ServiceRequest::STATUS_COMPLETED,
            ServiceRequest::STATUS_PENDING,
            ServiceRequest::STATUS_IN_PROGRESS,
        ];

        $generated = [];

        for ($i = 1; $i <= 85; $i++) {
            $template = $templates[($i - 1) % count($templates)];
            $templateIndex = intdiv($i - 1, count($templates));
            $studentEmail = $studentEmails[($i - 1) % count($studentEmails)];
            $status = $statusCycle[($i - 1) % count($statusCycle)];
            $timePhrase = $timePhrases[($i - 1) % count($timePhrases)];
            $variantIndex = $templateIndex % count($template['titles']);

            $generated[] = [
                'student_email' => $studentEmail,
                'title' => $template['titles'][$variantIndex],
                'category' => $template['category'],
                'description' => sprintf($template['description_template'], $template['contexts'][$variantIndex]).' The student needs '.$template['needs'][$variantIndex].' '.$timePhrase.'.',
                'status' => $status,
                'staff_notes' => $this->staffNotesForStatus($status, $template['category']),
            ];
        }

        return $generated;
    }

    private function staffNotesForStatus(string $status, string $categoryKey): ?string
    {
        if ($status === ServiceRequest::STATUS_PENDING) {
            return null;
        }

        $departmentLabel = match (explode('.', $categoryKey)[0]) {
            'it' => 'IT',
            'maintenance' => 'Maintenance',
            'registrar' => 'Registrar',
            'finance' => 'Finance',
            'library' => 'Library',
            default => 'Student Affairs',
        };

        if ($status === ServiceRequest::STATUS_IN_PROGRESS) {
            return $departmentLabel.' staff has reviewed the request and is currently working on the next action.';
        }

        return $departmentLabel.' staff completed the request and recorded the final update for the student.';
    }

    private function seedConversationMessages(ServiceRequest $serviceRequest, User $student, ?User $staff): void
    {
        if ($staff === null || $serviceRequest->status === ServiceRequest::STATUS_PENDING) {
            return;
        }

        $messages = [
            [
                'user_id' => $student->id,
                'author_name' => $student->name,
                'author_role' => $student->role,
                'message' => 'Following up on this request. Please let me know if you need any extra details from me.',
            ],
            [
                'user_id' => $staff->id,
                'author_name' => $staff->name,
                'author_role' => $staff->role,
                'message' => $serviceRequest->status === ServiceRequest::STATUS_COMPLETED
                    ? 'The department has finished the request and recorded the final outcome above.'
                    : 'The department has reviewed the issue and is working on the next step now.',
            ],
        ];

        if ($serviceRequest->status === ServiceRequest::STATUS_COMPLETED) {
            $messages[] = [
                'user_id' => $student->id,
                'author_name' => $student->name,
                'author_role' => $student->role,
                'message' => 'Thank you for the update and the completed request.',
            ];
        }

        $serviceRequest->messages()->createMany($messages);
    }
}
