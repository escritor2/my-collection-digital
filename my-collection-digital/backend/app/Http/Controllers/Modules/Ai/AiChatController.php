<?php

namespace App\Http\Controllers\Modules\Ai;

use App\Http\Controllers\Controller;
use App\Models\ReaderPosition;
use App\Models\UserBook;
use App\Services\Modules\Ai\GroqClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function __construct(private readonly GroqClient $groq) {}

    private function requireUserBook(int $userBookId): UserBook
    {
        return UserBook::with('book')
            ->where('id', $userBookId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function chat(Request $request, int $userBookId)
    {
        $userBook = $this->requireUserBook($userBookId);

        $validated = $request->validate([
            'message' => ['nullable', 'string'],
            'mode' => ['nullable', 'string'],
            'context' => ['nullable', 'array'],
            'context.text' => ['nullable', 'string'],
            'context.selected_text' => ['nullable', 'string'],
            'client_position' => ['nullable', 'array'],
            'client_position.format' => ['nullable', 'in:pdf,epub'],
            'client_position.locator' => ['nullable', 'array'],
            'client_position.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'history' => ['nullable', 'array'],
            'history.*.role' => ['required_with:history', 'in:system,user,assistant'],
            'history.*.content' => ['required_with:history', 'string'],
        ]);

        $savedPosition = ReaderPosition::where('user_id', Auth::id())
            ->where('user_book_id', $userBook->id)
            ->first();

        $pct = $validated['client_position']['percentage'] ?? ($savedPosition?->percentage ? (float) $savedPosition->percentage : null);
        $format = $validated['client_position']['format'] ?? ($savedPosition?->format ?? null);
        $locator = $validated['client_position']['locator'] ?? ($savedPosition?->locator ?? null);

        $mode = $validated['mode'] ?? 'chat';
        $userMessage = $validated['message'] ?? '';
        $contextText = (string) ($validated['context']['text'] ?? '');
        $selectedText = (string) ($validated['context']['selected_text'] ?? '');
        $contextPacket = trim($contextText."\n".$selectedText);

        if ($contextPacket === '') {
            return response()->json([
                'data' => [
                    'reply' => 'Para manter o anti-spoiler, preciso do trecho atual da leitura. Tente abrir o capítulo/página e enviar novamente.',
                    'usage' => null,
                    'model' => 'safe-fallback',
                    'meta' => ['fallback' => true, 'cached' => false],
                ],
            ]);
        }

        $system = $this->buildSystemPrompt(
            title: (string) $userBook->book?->title,
            author: (string) $userBook->book?->author,
            mode: $mode,
            percentage: $pct,
            format: $format,
            locator: $locator
        );

        $messages = [
            ['role' => 'system', 'content' => $system],
        ];

        if ($contextText !== '' || $selectedText !== '') {
            $messages[] = [
                'role' => 'user',
                'content' => trim(
                    "Trecho atual (não invente nada fora disso):\n".
                    ($contextText !== '' ? $contextText : '').
                    ($selectedText !== '' ? "\n\nTrecho selecionado:\n{$selectedText}" : '')
                ),
            ];
        }

        foreach (($validated['history'] ?? []) as $h) {
            $messages[] = ['role' => $h['role'], 'content' => $h['content']];
        }

        if ($mode !== 'chat' && $userMessage === '') {
            $userMessage = match ($mode) {
                'summary' => 'Resuma o que foi lido até aqui, apenas com base no trecho fornecido e sem spoilers.',
                'explain' => 'Explique de forma simples o trecho fornecido (sem spoilers).',
                'questions' => 'Gere 5 perguntas para checar compreensão do trecho fornecido (sem spoilers).',
                'quiz' => 'Inicie um Quiz interativo de 3 perguntas sobre o trecho atual.',
                'flashcards' => 'Gere 5 flashcards (Pergunta e Resposta).',
                'tags' => 'Gere tags curtas (máx 8).',
                'recommendations' => 'Sugira recomendações SEM spoilers.',
                default => 'Ajude com base no trecho fornecido, sem spoilers.',
            };
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $cacheTtl = max(0, (int) config('services.groq.cache_ttl_seconds', 600));
        $cacheKey = 'ai_chat:'.sha1(json_encode([
            'user_id' => Auth::id(),
            'book_id' => $userBook->book_id,
            'mode' => $mode,
            'message' => $userMessage,
            'context_text' => Str::limit($contextText, 8000, ''),
            'selected_text' => Str::limit($selectedText, 2000, ''),
            'position' => ['pct' => $pct, 'format' => $format, 'locator' => $locator],
        ]));

        if ($cacheTtl > 0) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && isset($cached['reply'])) {
                return response()->json([
                    'data' => [
                        'reply' => $cached['reply'],
                        'usage' => $cached['usage'] ?? null,
                        'model' => $cached['model'] ?? null,
                        'meta' => ['cached' => true, 'fallback' => false],
                    ],
                ]);
            }
        }

        try {
            $result = $this->groq->chat($messages);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Chat Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $result = ['ok' => false, 'error' => $e->getMessage()];
        }

        if (! ($result['ok'] ?? false)) {
            \Illuminate\Support\Facades\Log::error('AI Chat Failed', [
                'result' => $result,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'data' => [
                    'reply' => $this->buildFallbackReply($mode, $contextText, $selectedText),
                    'usage' => null,
                    'model' => 'safe-fallback',
                    'meta' => ['fallback' => true, 'cached' => false, 'error' => $result['error'] ?? 'Unknown error'],
                ],
            ]);
        }

        $data = $result['data'];
        $content = $data['choices'][0]['message']['content'] ?? '';

        if ($content !== '') {
            $user = Auth::user();
            $user->xp += 50;

            $newLevel = floor($user->xp / 1000) + 1;
            if ($newLevel > $user->level) {
                $user->level = $newLevel;
            }

            $user->save();

            if ($cacheTtl > 0) {
                Cache::put($cacheKey, [
                    'reply' => $content,
                    'usage' => $data['usage'] ?? null,
                    'model' => $data['model'] ?? null,
                ], now()->addSeconds($cacheTtl));
            }
        }

        return response()->json([
            'data' => [
                'reply' => $content,
                'usage' => $data['usage'] ?? null,
                'model' => $data['model'] ?? null,
                'meta' => ['cached' => false, 'fallback' => false],
            ],
        ]);
    }

    private function buildFallbackReply(string $mode, string $contextText, string $selectedText): string
    {
        $base = trim($selectedText !== '' ? $selectedText : $contextText);
        $excerpt = Str::limit(preg_replace('/\s+/', ' ', $base) ?? '', 450, '...');

        return match ($mode) {
            'summary' => "A IA está indisponível. Resumo:\n- {$excerpt}",
            'questions', 'quiz' => "Perguntas:\n1) Ideia principal?\n2) Termos-chave?\n3) Dúvidas?",
            'flashcards' => "Flashcard:\nFrente: Conceito principal?\nVerso: ...",
            'tags' => '#leitura #estudo #contexto',
            'recommendations' => 'Sugestões indisponíveis no momento.',
            default => 'A IA está indisponível.',
        };
    }

    private function buildSystemPrompt(
        string $title,
        string $author,
        string $mode,
        ?float $percentage,
        ?string $format,
        $locator
    ): string {
        $progress = $percentage !== null ? number_format($percentage, 2).'%' : 'desconhecido';
        $locStr = is_array($locator) ? json_encode($locator) : '';

        return <<<PROMPT
Você é um assistente com anti-spoiler rígido.

Livro: "{$title}" — {$author}
Progresso: {$progress}
Modo: {$mode}

- Use apenas o trecho fornecido
- Não invente nada fora dele
- RESPONDA SEMPRE EM PORTUGUÊS (Brasil).
PROMPT;
    }
}
