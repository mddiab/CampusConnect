<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_login_page_does_not_include_the_support_assistant(): void
    {
        $response = $this->get(route('login'));

        $response
            ->assertOk()
            ->assertDontSee('CampusConnect Assistant')
            ->assertDontSee('Message CampusConnect')
            ->assertDontSee('data-assistant', false);
    }

    public function test_authenticated_staff_dashboard_includes_the_support_assistant(): void
    {
        $staff = User::factory()->create([
            'role' => User::ROLE_STAFF,
        ]);

        $response = $this
            ->actingAs($staff)
            ->get(route('staff.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Support Assistant')
            ->assertSee('CampusConnect Assistant');
    }
}
