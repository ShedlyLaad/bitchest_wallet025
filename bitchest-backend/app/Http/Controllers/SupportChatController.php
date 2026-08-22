<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupportChatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // SAFE VALIDATION
            $validated = validator($request->all(), [
                'messages' => 'required|array|min:1',
                'messages.*.role' => 'required|string|in:user,assistant',
                'messages.*.content' => 'required|string|max:8000',
                'user_email' => 'nullable|string|email|max:255',
                'user_id' => 'nullable|integer|min:1',
                'session_id' => 'nullable|string|max:255',
            ])->validate();

            $botUrl = rtrim(config('services.support_bot.url', 'http://127.0.0.1:8001'), '/');
            $authUser = $request->user();

            $payload = [
                'messages' => $validated['messages'],
                'user_email' => $validated['user_email'] ?? $authUser?->email,
                'user_id' => $validated['user_id'] ?? $authUser?->id,
                // API routes are stateless by default; avoid request->session() to prevent 500.
                'session_id' => $validated['session_id'] ?? $request->header('X-Session-Id'),
            ];

            try {
                $response = Http::timeout(120)
                    ->acceptJson()
                    ->post("{$botUrl}/chat", $payload);
            } catch (\Throwable $e) {
                Log::error('Bot unreachable', [
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Support bot service unavailable',
                    'code' => 2001,
                ], 503);
            }

            // SAFE JSON PARSE
            $rawBody = $response->body();
            $botJson = json_decode($rawBody, true);

            if (!is_array($botJson)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid response from support bot',
                    'code' => 2004,
                ], 503);
            }

            // ERROR HANDLING
            if (!$response->successful()) {
                $message = $botJson['message'] ?? $botJson['detail'] ?? 'Support bot error';
                $code = $botJson['code'] ?? 2002;
                $status = $response->status();

                Log::warning('Support bot returned an error', [
                    'bot_status' => $response->status(),
                    'bot_code' => $code,
                    'bot_message' => $message,
                ]);

                if (str_contains(strtolower($message), 'groq_api_key')) {
                    $message = 'Support bot API key is not configured';
                    $code = 2003;
                    $status = 503;
                }

                if ($status >= 500) {
                    $status = 503;
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'code' => $code,
                ], $status);
            }

            // SUCCESS SAFE
            return response()->json($botJson);

        } catch (\Throwable $e) {
            Log::error('CRITICAL CHAT ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error (chat controller)',
                'code' => 9999,
            ], 500);
        }
    }
}