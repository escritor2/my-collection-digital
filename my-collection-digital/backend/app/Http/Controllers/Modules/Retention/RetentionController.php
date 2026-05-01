<?php

namespace App\Http\Controllers\Modules\Retention;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ImportedHighlight;
use App\Models\ReadingNotification;
use App\Models\ReadingSession;
use App\Models\UserBook;
use App\Models\UserRetentionSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetentionController extends Controller
{
    public function settings()
    {
        $settings = UserRetentionSetting::query()->firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'daily_goal_pages' => 20,
                'weekly_goal_pages' => 140,
                'reminders_enabled' => true,
                'reminder_hour' => 20,
                'timezone' => 'America/Sao_Paulo',
            ]
        );

        return response()->json(['data' => $settings]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'daily_goal_pages' => ['required', 'integer', 'min:1', 'max:3000'],
            'weekly_goal_pages' => ['required', 'integer', 'min:1', 'max:12000'],
            'reminders_enabled' => ['required', 'boolean'],
            'reminder_hour' => ['required', 'integer', 'min:0', 'max:23'],
            'timezone' => ['required', 'string', 'max:80'],
        ]);

        $settings = UserRetentionSetting::query()->updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json(['data' => $settings]);
    }

    public function importKindle(Request $request)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:10'],
        ]);

        $items = preg_split('/==========\s*/', $validated['content']) ?: [];
        $created = 0;

        foreach ($items as $item) {
            $chunk = trim($item);
            if ($chunk === '') {
                continue;
            }

            $lines = preg_split('/\R/', $chunk) ?: [];
            if (count($lines) < 2) {
                continue;
            }

            $titleAuthor = trim((string) ($lines[0] ?? ''));
            $meta = trim((string) ($lines[1] ?? ''));
            $quote = trim(implode("\n", array_slice($lines, 3)));

            $title = $titleAuthor;
            $author = null;
            if (preg_match('/^(.*)\s+\((.*)\)$/', $titleAuthor, $m)) {
                $title = trim($m[1]);
                $author = trim($m[2]);
            }

            $location = null;
            if (preg_match('/location\s+([0-9\-]+)/i', $meta, $m)) {
                $location = $m[1];
            }

            ImportedHighlight::query()->create([
                'user_id' => Auth::id(),
                'source' => 'kindle',
                'book_title' => $title,
                'author' => $author,
                'quote' => $quote !== '' ? $quote : null,
                'location' => $location,
            ]);
            $created++;
        }

        return response()->json(['data' => ['imported' => $created]]);
    }

    public function importCsv(Request $request)
    {
        $validated = $request->validate([
            'csv_content' => ['required', 'string', 'min:5'],
            'source' => ['nullable', 'string', 'in:goodreads,csv'],
        ]);

        $source = $validated['source'] ?? 'csv';
        $lines = preg_split('/\R/', trim($validated['csv_content'])) ?: [];
        if (count($lines) < 2) {
            return response()->json(['data' => ['imported' => 0]]);
        }

        $headers = str_getcsv(array_shift($lines));
        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);

        $imported = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $cols = str_getcsv($line);
            $row = [];
            foreach ($headers as $idx => $h) {
                $row[$h] = $cols[$idx] ?? null;
            }

            $title = trim((string) ($row['title'] ?? $row['book title'] ?? ''));
            $author = trim((string) ($row['author'] ?? $row['author l-f'] ?? ''));
            $isbn = trim((string) ($row['isbn'] ?? ''));
            $pageCount = (int) ($row['number of pages'] ?? $row['pages'] ?? 0);

            if ($title === '' || $author === '') {
                continue;
            }

            $book = Book::query()->firstOrCreate(
                ['title' => $title, 'author' => $author],
                [
                    'description' => null,
                    'isbn' => $isbn !== '' ? $isbn : null,
                    'page_count' => $pageCount > 0 ? $pageCount : null,
                    'created_by' => Auth::id(),
                ]
            );

            UserBook::query()->firstOrCreate(
                ['user_id' => Auth::id(), 'book_id' => $book->id],
                ['status' => 'quero_ler']
            );

            ImportedHighlight::query()->create([
                'user_id' => Auth::id(),
                'source' => $source,
                'book_title' => $title,
                'author' => $author,
                'quote' => null,
                'note' => null,
            ]);

            $imported++;
        }

        return response()->json(['data' => ['imported' => $imported]]);
    }

    public function exportData()
    {
        $userId = Auth::id();

        $data = [
            'exported_at' => now()->toIso8601String(),
            'user' => Auth::user(),
            'shelf' => UserBook::query()->where('user_id', $userId)->with('book')->get(),
            'reading_sessions' => ReadingSession::query()->where('user_id', $userId)->get(),
            'imported_highlights' => ImportedHighlight::query()->where('user_id', $userId)->get(),
            'retention_settings' => UserRetentionSetting::query()->where('user_id', $userId)->first(),
        ];

        return response()->json(['data' => $data]);
    }

    public function weeklySummary()
    {
        $userId = Auth::id();
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        $sessions = ReadingSession::query()
            ->where('user_id', $userId)
            ->whereBetween('ended_at', [$start, $end])
            ->get();

        $pages = $sessions->sum('pages_read');
        $minutes = $sessions->sum('duration_minutes');

        return response()->json([
            'data' => [
                'week_start' => $start->toDateString(),
                'week_end' => $end->toDateString(),
                'pages_read' => $pages,
                'reading_minutes' => $minutes,
                'sessions_count' => $sessions->count(),
            ],
        ]);
    }

    public function goalRisk()
    {
        $userId = Auth::id();
        $settings = UserRetentionSetting::query()->firstOrCreate(['user_id' => $userId]);

        $start = Carbon::now()->startOfWeek();
        $now = Carbon::now();

        $pagesThisWeek = ReadingSession::query()
            ->where('user_id', $userId)
            ->whereBetween('ended_at', [$start, $now])
            ->sum('pages_read');

        $dayProgress = max(1, $now->dayOfWeekIso); // 1..7
        $expectedByNow = (int) round(($settings->weekly_goal_pages * $dayProgress) / 7);
        $ratio = $expectedByNow > 0 ? ($pagesThisWeek / $expectedByNow) : 1.0;

        $riskLevel = 'none';
        if ($ratio < 0.5) {
            $riskLevel = 'high';
        } elseif ($ratio < 0.8) {
            $riskLevel = 'medium';
        } elseif ($ratio < 1.0) {
            $riskLevel = 'low';
        }

        return response()->json([
            'data' => [
                'pages_this_week' => (int) $pagesThisWeek,
                'expected_by_now' => $expectedByNow,
                'weekly_goal_pages' => (int) $settings->weekly_goal_pages,
                'risk_level' => $riskLevel,
                'at_risk' => in_array($riskLevel, ['high', 'medium'], true),
            ],
        ]);
    }

    public function checkNotifications()
    {
        $risk = $this->goalRisk()->getData(true)['data'] ?? null;
        if (! $risk) {
            return response()->json(['data' => ['created' => 0]]);
        }

        $settings = UserRetentionSetting::query()->where('user_id', Auth::id())->first();
        if (! $settings || ! $settings->reminders_enabled) {
            return response()->json(['data' => ['created' => 0]]);
        }

        $created = 0;
        if (! empty($risk['at_risk'])) {
            ReadingNotification::query()->create([
                'user_id' => Auth::id(),
                'type' => 'goal_at_risk',
                'payload' => $risk,
                'sent_at' => now(),
            ]);
            $created++;
        }

        $summary = $this->weeklySummary()->getData(true)['data'] ?? null;
        ReadingNotification::query()->create([
            'user_id' => Auth::id(),
            'type' => 'weekly_summary',
            'payload' => $summary,
            'sent_at' => now(),
        ]);
        $created++;

        return response()->json(['data' => ['created' => $created]]);
    }
}
