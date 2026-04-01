<?php

namespace Database\Factories;

use App\Models\ServiceRequest;
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
            'department' => fake()->randomElement(ServiceRequest::departments()),
            'category' => fake()->randomElement(ServiceRequest::categories()),
            'description' => fake()->paragraph(),
            'status' => ServiceRequest::STATUS_PENDING,
            'attachment_path' => null,
            'attachment_original_name' => null,
        ];
    }
}
