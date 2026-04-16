<?php

namespace Database\Factories;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequestMessage>
 */
class ServiceRequestMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'user_id' => User::factory(),
            'author_name' => 'Student Reply',
            'author_role' => User::ROLE_STUDENT,
            'message' => fake()->paragraph(),
        ];
    }
}
