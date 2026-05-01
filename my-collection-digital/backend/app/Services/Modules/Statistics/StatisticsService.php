<?php

namespace App\Services\Modules\Statistics;

use App\Interfaces\Modules\ReadingSession\ReadingSessionRepositoryInterface;
use App\Interfaces\Modules\Statistics\StatisticsServiceInterface;
use App\Interfaces\Modules\UserShelf\UserBookRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatisticsService implements StatisticsServiceInterface
{
    protected UserBookRepositoryInterface $userBookRepository;

    protected ReadingSessionRepositoryInterface $readingSessionRepository;

    public function __construct(
        UserBookRepositoryInterface $userBookRepository,
        ReadingSessionRepositoryInterface $readingSessionRepository
    ) {
        $this->userBookRepository = $userBookRepository;
        $this->readingSessionRepository = $readingSessionRepository;
    }

    /**
     * Get reading statistics for a specific user.
     */
    public function getUserReadingStatistics(int $userId): array
    {
        $userBooks = $this->userBookRepository->getAllUserBooks($userId);
        $readingSessions = $this->readingSessionRepository->getAllUserReadingSessions($userId);

        $totalBooksRead = $userBooks->where(
            fn ($userBook) => $userBook->status === 'lido' ||
            ($userBook->book && $userBook->progress_pages >= $userBook->book->page_count)
        )->count();

        $totalPagesRead = $userBooks->sum(
            fn ($userBook) => $userBook->progress_pages
        );

        $totalReadingTimeMinutes = $readingSessions->sum(
            fn ($session) => $session->duration_minutes
        );

        // Média mensal de leitura (simplificada para o último ano)
        $monthlyAverage = $this->calculateMonthlyAverage($readingSessions);

        return [
            'total_books_read' => $totalBooksRead,
            'total_pages_read' => $totalPagesRead,
            'total_reading_time_minutes' => $totalReadingTimeMinutes,
            'monthly_average_pages' => $monthlyAverage['pages'],
            'monthly_average_time_minutes' => $monthlyAverage['time_minutes'],
        ];
    }

    public function getReadingHeatmap(int $userId, int $days = 120): array
    {
        $since = Carbon::now()->subDays($days - 1)->startOfDay();
        $sessions = $this->readingSessionRepository->getAllUserReadingSessions($userId)
            ->filter(fn ($s) => $s->ended_at && $s->ended_at->greaterThanOrEqualTo($since));

        $byDay = [];
        foreach ($sessions as $s) {
            $day = $s->ended_at->toDateString();
            $byDay[$day] = $byDay[$day] ?? ['date' => $day, 'minutes' => 0, 'pages' => 0, 'sessions' => 0];
            $byDay[$day]['minutes'] += (int) ($s->duration_minutes ?? 0);
            $byDay[$day]['pages'] += (int) ($s->pages_read ?? 0);
            $byDay[$day]['sessions'] += 1;
        }

        // fill missing days
        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $d = $since->copy()->addDays($i)->toDateString();
            $out[] = $byDay[$d] ?? ['date' => $d, 'minutes' => 0, 'pages' => 0, 'sessions' => 0];
        }

        return $out;
    }

    public function getReadingSpeed(int $userId, int $days = 30): array
    {
        $since = Carbon::now()->subDays($days)->startOfDay();
        $sessions = $this->readingSessionRepository->getAllUserReadingSessions($userId)
            ->filter(fn ($s) => $s->ended_at && $s->ended_at->greaterThanOrEqualTo($since));

        $totalPages = $sessions->sum(fn ($s) => (int) ($s->pages_read ?? 0));
        $totalMinutes = $sessions->sum(fn ($s) => (int) ($s->duration_minutes ?? 0));

        $pagesPerHour = $totalMinutes > 0 ? round(($totalPages / $totalMinutes) * 60, 2) : 0.0;

        return [
            'days' => $days,
            'total_pages' => $totalPages,
            'total_minutes' => $totalMinutes,
            'pages_per_hour' => $pagesPerHour,
        ];
    }

    public function getYearlyRecap(int $userId, int $year): array
    {
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();

        $sessions = $this->readingSessionRepository->getAllUserReadingSessions($userId)
            ->filter(fn ($s) => $s->ended_at && $s->ended_at->betweenIncluded($start, $end));

        $pages = $sessions->sum(fn ($s) => (int) ($s->pages_read ?? 0));
        $minutes = $sessions->sum(fn ($s) => (int) ($s->duration_minutes ?? 0));

        $booksFinished = $this->userBookRepository
            ->getAllUserBooks($userId)
            ->filter(fn ($ub) => $ub->finished_at && $ub->finished_at->betweenIncluded($start, $end))
            ->count();

        $streak = $this->calculateBestStreakFromSessions($sessions);

        return [
            'year' => $year,
            'books_finished' => $booksFinished,
            'pages_read' => $pages,
            'reading_time_minutes' => $minutes,
            'best_streak_days' => $streak,
        ];
    }

    public function getLearningStatistics(int $userId): array
    {
        $userBooks = $this->userBookRepository->getAllUserBooks($userId);

        $techCategories = ['Computers', 'Programming', 'Technology', 'Software Development', 'Science', 'Mathematics'];

        $techBooks = $userBooks->filter(function ($ub) use ($techCategories) {
            $categories = $ub->book?->categories;
            if (is_string($categories)) {
                $categories = json_decode($categories, true);
            }
            if (! is_array($categories)) {
                $categories = [];
            }

            return ! empty(array_intersect($categories, $techCategories)) ||
                   ($ub->tags && $ub->tags->whereIn('name', ['Programação', 'Tecnologia', 'Dev', 'Learning'])->count() > 0);
        });

        $totalTechBooks = $techBooks->count();
        $finishedTechBooks = $techBooks->where('status', 'lido')->count();
        $totalPagesTech = $techBooks->sum('progress_pages');
        $totalMinutesTech = $this->readingSessionRepository->getAllUserReadingSessions($userId)
            ->whereIn('user_book_id', $techBooks->pluck('id'))
            ->sum('duration_minutes');

        return [
            'total_tech_books' => $totalTechBooks,
            'finished_tech_books' => $finishedTechBooks,
            'total_pages_tech' => $totalPagesTech,
            'total_minutes_tech' => $totalMinutesTech,
            'books' => $techBooks->map(fn ($ub) => [
                'id' => $ub->id,
                'title' => $ub->book->title,
                'author' => $ub->book->author,
                'progress' => ($ub->book && $ub->book->page_count && $ub->book->page_count > 0)
                    ? round(($ub->progress_pages / $ub->book->page_count) * 100, 1)
                    : 0,
                'pages_read' => $ub->progress_pages,
                'total_pages' => $ub->book?->page_count,
            ])->values(),
        ];
    }

    /**
     * Calculate monthly average reading statistics.
     */
    protected function calculateMonthlyAverage(Collection $readingSessions): array
    {
        $sessionsLastYear = $readingSessions->filter(function ($session) {
            return $session->ended_at && $session->ended_at->greaterThanOrEqualTo(Carbon::now()->subYear());
        });

        if ($sessionsLastYear->isEmpty()) {
            return ['pages' => 0, 'time_minutes' => 0];
        }

        $pagesPerMonth = [];
        $timePerMonth = [];

        foreach ($sessionsLastYear as $session) {
            $monthYear = $session->ended_at->format('Y-m');
            $pagesPerMonth[$monthYear] = ($pagesPerMonth[$monthYear] ?? 0) + $session->pages_read;
            $timePerMonth[$monthYear] = ($timePerMonth[$monthYear] ?? 0) + $session->duration_minutes;
        }

        $averagePages = count($pagesPerMonth) > 0 ? array_sum($pagesPerMonth) / count($pagesPerMonth) : 0;
        $averageTime = count($timePerMonth) > 0 ? array_sum($timePerMonth) / count($timePerMonth) : 0;

        return [
            'pages' => round($averagePages),
            'time_minutes' => round($averageTime),
        ];
    }

    protected function calculateBestStreakFromSessions(Collection $sessions): int
    {
        $days = $sessions
            ->filter(fn ($s) => $s->ended_at)
            ->map(fn ($s) => $s->ended_at->toDateString())
            ->unique()
            ->sort()
            ->values();

        $best = 0;
        $current = 0;
        $prev = null;

        foreach ($days as $day) {
            if ($prev === null) {
                $current = 1;
            } else {
                $diff = Carbon::parse($prev)->diffInDays(Carbon::parse($day));
                $current = ($diff === 1) ? ($current + 1) : 1;
            }
            $best = max($best, $current);
            $prev = $day;
        }

        return $best;
    }
}
