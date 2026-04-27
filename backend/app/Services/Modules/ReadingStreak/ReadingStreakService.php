<?php

namespace App\Services\Modules\ReadingStreak;

use App\Interfaces\Modules\ReadingStreak\ReadingStreakRepositoryInterface;
use App\Interfaces\Modules\ReadingStreak\ReadingStreakServiceInterface;
use Carbon\Carbon;

class ReadingStreakService implements ReadingStreakServiceInterface
{
    protected ReadingStreakRepositoryInterface $streakRepository;

    public function __construct(ReadingStreakRepositoryInterface $streakRepository)
    {
        $this->streakRepository = $streakRepository;
    }

    public function getUserStreak(int $userId): array
    {
        $streak = $this->streakRepository->getUserStreak($userId);
        
        if (!$streak) {
            return [
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_read_date' => null,
            ];
        }

        // Decay logic
        if ($streak->last_read_date) {
            $lastRead = Carbon::parse($streak->last_read_date)->startOfDay();
            $yesterday = Carbon::yesterday();

            if ($lastRead->lessThan($yesterday)) {
                // Streak broken
                $streak = $this->streakRepository->createOrUpdate($userId, [
                    'current_streak' => 0,
                ]);
            }
        }

        return [
            'current_streak' => $streak->current_streak,
            'longest_streak' => $streak->longest_streak,
            'last_read_date' => $streak->last_read_date ? $streak->last_read_date->toDateString() : null,
        ];
    }

    public function recordCheckIn(int $userId): array
    {
        $streak = $this->streakRepository->getUserStreak($userId);
        $today = Carbon::today();

        if (!$streak) {
            $streak = $this->streakRepository->createOrUpdate($userId, [
                'current_streak' => 1,
                'longest_streak' => 1,
                'last_read_date' => $today,
            ]);
        } else {
            $lastRead = $streak->last_read_date ? Carbon::parse($streak->last_read_date)->startOfDay() : null;

            if (!$lastRead || $lastRead->lessThan(Carbon::yesterday())) {
                // Reset streak if gap > 1 day
                $newCurrent = 1;
            } elseif ($lastRead->equalTo(Carbon::yesterday())) {
                // Increment streak
                $newCurrent = $streak->current_streak + 1;
            } else {
                // Already checked in today
                $newCurrent = $streak->current_streak;
            }

            $newLongest = max($streak->longest_streak, $newCurrent);

            $streak = $this->streakRepository->createOrUpdate($userId, [
                'current_streak' => $newCurrent,
                'longest_streak' => $newLongest,
                'last_read_date' => $today,
            ]);
        }

        return [
            'current_streak' => $streak->current_streak,
            'longest_streak' => $streak->longest_streak,
            'last_read_date' => $streak->last_read_date->toDateString(),
        ];
    }
}