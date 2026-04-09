<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_demo_accounts_and_reference_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $informationTechnology = Department::query()
            ->where('name', 'Information Technology')
            ->first();

        $this->assertNotNull($informationTechnology);

        $this->assertDatabaseHas('service_categories', [
            'name' => 'Technical Support',
            'department_id' => $informationTechnology->id,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'staff@campusconnect.test',
            'department_id' => $informationTechnology->id,
        ]);

        $this->assertSame(10, User::query()->where('role', User::ROLE_STUDENT)->count());
        $this->assertSame(6, User::query()->where('role', User::ROLE_STAFF)->count());
        $this->assertSame(3, User::query()->where('role', User::ROLE_ADMIN)->count());

        $this->assertDatabaseHas('users', [
            'email' => 'student10@campusconnect.test',
            'role' => User::ROLE_STUDENT,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin3@campusconnect.test',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'staff.finance@campusconnect.test',
            'role' => User::ROLE_STAFF,
        ]);

        $this->assertGreaterThanOrEqual(11, ServiceCategory::query()->count());
        $this->assertGreaterThanOrEqual(100, ServiceRequest::query()->count());
        $this->assertSame(
            10,
            ServiceRequest::query()
                ->select('user_id')
                ->distinct()
                ->count('user_id')
        );

        $request = ServiceRequest::query()
            ->where('title', 'Internet connection problem in Building A classroom')
            ->first();

        $this->assertNotNull($request);
        $this->assertSame(ServiceRequest::STATUS_PENDING, $request->status);
        $this->assertSame($informationTechnology->id, $request->department_id);
        $this->assertNotNull($request->service_category_id);
    }
}
