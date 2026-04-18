<?php

namespace App\Services;

use App\Exceptions\AssistantException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Collection;

class GeminiAssistant
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {
    }

    public function isConfigured(): bool
    {
        return filled(config('services.gemini.api_key'));
    }

    /**
     * @param  array<int, array{role:string, text:string}>  $history
     * @param  array<string, mixed>  $context
     */
    public function reply(array $history, array $context = []): string
    {
        if (! $this->isConfigured()) {
            throw new AssistantException(
                'Assistant chat is not configured yet. Add GEMINI_API_KEY to your .env file.',
                503,
            );
        }

        $request = $this->http
            ->withHeaders([
                'x-goog-api-key' => (string) config('services.gemini.api_key'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(20);

        if (filled(config('services.gemini.ca_bundle'))) {
            $request = $request->withOptions([
                'verify' => (string) config('services.gemini.ca_bundle'),
            ]);
        }

        $response = $request->post($this->endpoint(), $this->payload($history, $context));

        if ($response->status() === 429) {
            throw new AssistantException(
                'The assistant is busy right now. Please wait a minute and try again.',
                429,
            );
        }

        if ($response->failed()) {
            throw new AssistantException(
                'The assistant could not reply right now. Please try again shortly.',
            );
        }

        $text = $this->extractText($response->json());

        if ($text === '') {
            throw new AssistantException(
                'The assistant did not return a usable reply. Please try again.',
            );
        }

        return $text;
    }

    private function endpoint(): string
    {
        return sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            (string) config('services.gemini.model', 'gemini-2.5-flash-lite'),
        );
    }

    /**
     * @param  array<int, array{role:string, text:string}>  $history
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function payload(array $history, array $context): array
    {
        return [
            'system_instruction' => [
                'parts' => [
                    [
                        'text' => $this->systemInstruction($context),
                    ],
                ],
            ],
            'contents' => $this->normalizeHistory($history),
            'generationConfig' => [
                'temperature' => 0.8,
                'topP' => 0.9,
                'maxOutputTokens' => 280,
                'thinkingConfig' => [
                    'thinkingBudget' => 0,
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function systemInstruction(array $context): string
    {
        $role = $context['role'] ?? 'guest';
        $currentRoute = $context['current_route'] ?? 'unknown';
        $authState = ($context['is_authenticated'] ?? false) ? 'authenticated' : 'guest';

        return <<<TEXT
You are CampusConnect Assistant, a friendly support assistant for a university service portal called CampusConnect.

Primary job:
- Answer FAQ and navigation questions in a natural, helpful way.
- Sound like a real assistant, not a policy document.
- Keep replies concise and practical, usually 2 to 5 sentences.
- Use short steps only when steps are the clearest answer.

Hard boundaries:
- You do not have live access to tickets, accounts, or private records.
- Do not claim to view or change data.
- If someone asks for personal, ticket-specific, or account-specific information, explain that you only handle general help and FAQs.
- Do not invent features or pages that are not listed below.

CampusConnect facts:
- The login page signs students, staff, and admins into the correct dashboard automatically.
- Students can submit service requests, review their request history, edit their own requests, send messages on their own requests, and download their own attachments.
- Staff work from the staff dashboard and department queue.
- Each department can have up to 3 staff accounts.
- Staff only see and manage requests assigned to their own department.
- Staff can update request status, priority, and staff notes, reply to students, and download attachments for requests in their department.
- Admins use the admin dashboard.
- Admins manage users, departments, service categories, recent activity, and reports.
- Admins can export reports.

Navigation names you can reference:
- Login page
- Student dashboard
- Staff dashboard
- Department queue
- Admin dashboard
- User management
- Department management
- Service categories
- Reports

Current visitor context:
- Authentication state: {$authState}
- Role: {$role}
- Current route: {$currentRoute}

Answer naturally, stay grounded in the facts above, and prefer direct helpful wording.
TEXT;
    }

    /**
     * @param  array<int, array{role:string, text:string}>  $history
     * @return array<int, array<string, mixed>>
     */
    private function normalizeHistory(array $history): array
    {
        return collect($history)
            ->map(function (array $message): ?array {
                $text = trim((string) ($message['text'] ?? ''));

                if ($text === '') {
                    return null;
                }

                return [
                    'role' => $this->providerRole((string) ($message['role'] ?? 'user')),
                    'parts' => [
                        [
                            'text' => $text,
                        ],
                    ],
                ];
            })
            ->filter()
            ->take(-10)
            ->values()
            ->all();
    }

    private function providerRole(string $role): string
    {
        return in_array($role, ['assistant', 'bot', 'model'], true)
            ? 'model'
            : 'user';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractText(array $payload): string
    {
        return collect($payload['candidates'] ?? [])
            ->flatMap(function (array $candidate): Collection {
                return collect($candidate['content']['parts'] ?? []);
            })
            ->pluck('text')
            ->filter(fn ($text) => is_string($text) && trim($text) !== '')
            ->map(fn (string $text) => trim($text))
            ->implode("\n\n");
    }
}
