<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class CampusStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->structure() as $departmentName => $categories) {
            $department = Department::query()->firstOrCreate([
                'name' => $departmentName,
            ]);

            foreach ($categories as $categoryName) {
                $department->categories()->firstOrCreate([
                    'name' => $categoryName,
                ]);
            }
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function structure(): array
    {
        return [
            'Information Technology' => [
                'Technical Support',
                'General Inquiry',
            ],
            'Maintenance' => [
                'Facility Maintenance',
                'General Inquiry',
            ],
            'Registrar' => [
                'Registration',
                'Document Request',
                'General Inquiry',
            ],
            'Finance' => [
                'Payment',
                'General Inquiry',
            ],
            'Library' => [
                'Document Request',
                'General Inquiry',
            ],
            'Student Affairs' => [
                'General Inquiry',
            ],
        ];
    }
}
