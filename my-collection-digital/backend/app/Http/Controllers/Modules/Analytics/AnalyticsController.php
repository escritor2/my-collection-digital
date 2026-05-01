<?php

namespace App\Http\Controllers\Modules\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Modules\Catalog\ExternalCatalogService;
use App\Services\Modules\Statistics\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function __construct(protected StatisticsService $statisticsService) {}

    public function heatmap(Request $request)
    {
        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:7', 'max:366'],
        ]);

        return response()->json([
            'data' => $this->statisticsService->getReadingHeatmap(Auth::id(), $validated['days'] ?? 120),
        ]);
    }

    public function speed(Request $request)
    {
        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:7', 'max:180'],
        ]);

        return response()->json([
            'data' => $this->statisticsService->getReadingSpeed(Auth::id(), $validated['days'] ?? 30),
        ]);
    }

    public function yearlyRecap(Request $request)
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        return response()->json([
            'data' => $this->statisticsService->getYearlyRecap(Auth::id(), $validated['year'] ?? (int) now()->format('Y')),
        ]);
    }

    public function learning(Request $request)
    {
        return response()->json([
            'data' => $this->statisticsService->getLearningStatistics(Auth::id()),
        ]);
    }

    public function techSuggestions(Request $request)
    {
        $queries = ['Clean Code', 'Pragmatic Programmer', 'Introduction to Algorithms', 'Design Patterns', 'Refactoring', 'Rust Programming', 'Vue.js', 'Laravel'];
        $query = $queries[array_rand($queries)];

        $catalog = app(ExternalCatalogService::class);

        return response()->json(
            $catalog->search($query, 6)
        );
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:7', 'max:366'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'format' => ['nullable', 'in:json,csv'],
        ]);

        $days = $validated['days'] ?? 120;
        $year = $validated['year'] ?? (int) now()->format('Y');
        $format = $validated['format'] ?? 'json';

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'user_id' => Auth::id(),
            'heatmap' => $this->statisticsService->getReadingHeatmap(Auth::id(), $days),
            'speed' => $this->statisticsService->getReadingSpeed(Auth::id(), min($days, 180)),
            'yearly' => $this->statisticsService->getYearlyRecap(Auth::id(), $year),
        ];

        if ($format === 'csv') {
            $lines = ['date,minutes,pages,sessions'];
            foreach ($payload['heatmap'] as $row) {
                $lines[] = implode(',', [
                    $row['date'],
                    (int) $row['minutes'],
                    (int) $row['pages'],
                    (int) $row['sessions'],
                ]);
            }

            return response(implode("\n", $lines), 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="analytics-heatmap.csv"',
            ]);
        }

        return response()->json(['data' => $payload]);
    }
}
