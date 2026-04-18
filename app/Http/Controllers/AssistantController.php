<?php

namespace App\Http\Controllers;

use App\Exceptions\AssistantException;
use App\Services\GeminiAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssistantController extends Controller
{
    public function store(Request $request, GeminiAssistant $assistant): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
            'history' => ['nullable', 'array', 'max:10'],
            'history.*.role' => ['required', 'string', Rule::in(['user', 'assistant', 'bot', 'model'])],
            'history.*.text' => ['required', 'string', 'max:1200'],
            'current_route' => ['nullable', 'string', 'max:120'],
        ]);

        if (! $assistant->isConfigured()) {
            return response()->json([
                'message' => 'Assistant chat is not configured yet. Add GEMINI_API_KEY to your .env file.',
            ], 503);
        }

        $history = $validated['history'] ?? [];
        $history[] = [
            'role' => 'user',
            'text' => $validated['message'],
        ];

        try {
            $reply = $assistant->reply($history, [
                'is_authenticated' => $request->user() !== null,
                'role' => $request->user()?->role ?? 'guest',
                'current_route' => $validated['current_route'] ?? null,
            ]);
        } catch (AssistantException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->status);
        }

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
