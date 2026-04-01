<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
