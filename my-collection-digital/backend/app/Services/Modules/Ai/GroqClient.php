<?php

namespace App\Services\Modules\Ai;

use Illuminate\Support\Facades\Http;

class GroqClient
{
    public function chat(array $messages, array $options = []): array
    {
        $apiKey = config('services.groq.api_key');
        $baseUrl = rtrim((string) config('services.groq.base_url'), '/');
        $model = (string) (config('services.groq.model') ?? 'llama-3.1-70b-versatile');

        if (! $apiKey) {
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'GROQ_API_KEY not configured',
            ];
        }

        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.4,
            'max_tokens' => $options['max_tokens'] ?? 900,
        ], $options['extra'] ?? []);

        $res = Http::withToken($apiKey)
            ->withoutVerifying()
            ->acceptJson()
            ->timeout(45)
            ->post("{$baseUrl}/chat/completions", $payload);

        if (! $res->successful()) {
            return [
                'ok' => false,
                'status' => $res->status(),
                'error' => $res->json() ?? $res->body(),
            ];
        }

        return [
            'ok' => true,
            'data' => $res->json(),
        ];
    }
}
