<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupportChatController extends Controller
{
    /**
     * Proxy vers le service FastAPI du support bot (bot_server.py).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|string|in:user,assistant',
            'messages.*.content' => 'required|string|max:8000',
            'user_email' => 'nullable|string|email|max:255',
        ]);

        $botUrl = rtrim(config('services.support_bot.url', 'http://127.0.0.1:8001'), '/');

        $payload = [
            'messages' => $validated['messages'],
            'user_email' => $validated['user_email'] ?? $request->user()?->email,
        ];

        try {
            $response = Http::timeout(120)
                ->acceptJson()
                ->post("{$botUrl}/chat", $payload);
        } catch (\Throwable $e) {
            Log::error('Support bot unreachable', ['exception' => $e->getMessage()]);

            return response()->json([
                'detail' => 'Support bot service unavailable. Ensure the Python bot is running on '.$botUrl,
            ], 503);
        }

        if (! $response->successful()) {
            $detail = $response->json('detail');
            if (is_array($detail)) {
                $detail = json_encode($detail);
            }

            return response()->json([
                'detail' => $detail ?? $response->body() ?: 'Support bot error',
            ], $response->status());
        }

        return response()->json($response->json());
    }
}
