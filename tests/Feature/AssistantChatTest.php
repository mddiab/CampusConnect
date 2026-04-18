<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_chat_with_gemini_when_configured(): void
    {
        config()->set('services.gemini.api_key', 'test-key');
        config()->set('services.gemini.model', 'gemini-2.5-flash-lite');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Use the login page and sign in with your campus credentials.'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $response = $this->postJson(route('assistant.chat'), [
            'message' => 'How do I sign in?',
            'history' => [
                [
                    'role' => 'bot',
                    'text' => 'Ask me about signing in, roles, or where to find something.',
                ],
            ],
            'current_route' => 'login',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'reply' => 'Use the login page and sign in with your campus credentials.',
            ]);

        Http::assertSent(function (HttpRequest $request): bool {
            $payload = $request->data();

            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent'
                && $request->hasHeader('x-goog-api-key', 'test-key')
                && str_contains($payload['system_instruction']['parts'][0]['text'] ?? '', 'CampusConnect Assistant')
                && ($payload['contents'][0]['role'] ?? null) === 'model'
                && ($payload['contents'][1]['role'] ?? null) === 'user'
                && ($payload['contents'][1]['parts'][0]['text'] ?? null) === 'How do I sign in?';
        });
    }

    public function test_assistant_returns_service_unavailable_when_key_is_missing(): void
    {
        config()->set('services.gemini.api_key', null);

        $response = $this->postJson(route('assistant.chat'), [
            'message' => 'Help me',
        ]);

        $response
            ->assertStatus(503)
            ->assertJson([
                'message' => 'Assistant chat is not configured yet. Add GEMINI_API_KEY to your .env file.',
            ]);
    }

    public function test_authenticated_role_context_is_sent_to_gemini(): void
    {
        config()->set('services.gemini.api_key', 'test-key');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Reports are available from the admin dashboard.'],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $this
            ->actingAs($admin)
            ->postJson(route('assistant.chat'), [
                'message' => 'Where are reports?',
                'current_route' => 'admin.dashboard',
            ])
            ->assertOk();

        Http::assertSent(function (HttpRequest $request): bool {
            $instruction = $request->data()['system_instruction']['parts'][0]['text'] ?? '';

            return str_contains($instruction, 'Role: admin')
                && str_contains($instruction, 'Current route: admin.dashboard');
        });
    }
}
