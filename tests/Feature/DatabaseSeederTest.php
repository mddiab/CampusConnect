<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceRequest;
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

        $request = ServiceRequest::query()
            ->where('title', 'Internet connection problem in Building A classroom')
            ->first();

        $this->assertNotNull($request);
        $this->assertSame(ServiceRequest::STATUS_PENDING, $request->status);
        $this->assertSame($informationTechnology->id, $request->department_id);
        $this->assertNotNull($request->service_category_id);
    }
}
