<?php

namespace Database\Factories;

use App\Models\ServiceRequest;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'department_id' => null,
            'service_category_id' => ServiceCategory::factory(),
            'description' => fake()->paragraph(),
            'status' => ServiceRequest::STATUS_PENDING,
            'staff_notes' => null,
            'resolved_at' => null,
            'attachment_path' => null,
            'attachment_original_name' => null,
        ];
    }
}
