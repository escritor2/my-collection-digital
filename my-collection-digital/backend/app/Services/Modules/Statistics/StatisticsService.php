<?php

namespace App\Services\Modules\Statistics;

use App\Interfaces\Modules\ReadingSession\ReadingSessionRepositoryInterface;
use App\Interfaces\Modules\Statistics\StatisticsServiceInterface;
use App\Interfaces\Modules\UserShelf\UserBookRepositoryInterface;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class StatisticsService implements StatisticsServiceInterface
{
    protected UserBookRepositoryInterface $userBookRepository;
    protected ReadingSessionRepositoryInterface $readingSessionRepository;

    public function __construct(
        UserBookRepositoryInterface $userBookRepository,
        ReadingSessionRepositoryInterface $readingSessionRepository
    )
    {
        $this->userBookRepository = $userBookRepository;
        $this->readingSessionRepository = $readingSessionRepository;
    }

    /**
     * Get reading statistics for a specific user.
     *
     * @param  int  $userId
     * @return array
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

    /**
     * Calculate monthly average reading statistics.
     *
     * @param  \Illuminate\Support\Collection  $readingSessions
     * @return array
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
}
