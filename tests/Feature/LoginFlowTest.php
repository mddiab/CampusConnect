<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_login_redirects_to_student_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'password' => 'password',
            'role' => User::ROLE_STUDENT,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_staff_login_redirects_to_staff_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => 'password',
            'role' => User::ROLE_STAFF,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('staff.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_open_a_dashboard_for_a_different_role(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_STAFF,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('student.dashboard'));

        $response->assertRedirect(route('staff.dashboard'));
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        RateLimiter::clear('limited@example.com|127.0.0.1');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this
                ->from(route('login'))
                ->post(route('login.store'), [
                    'email' => 'limited@example.com',
                    'password' => 'wrong-password',
                ])
                ->assertRedirect(route('login'));
        }

        $this
            ->post(route('login.store'), [
                'email' => 'limited@example.com',
                'password' => 'wrong-password',
            ])
            ->assertTooManyRequests();
    }
}
