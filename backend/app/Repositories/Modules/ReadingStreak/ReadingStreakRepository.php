<?php

namespace App\Repositories\Modules\ReadingStreak;

use App\Interfaces\Modules\ReadingStreak\ReadingStreakRepositoryInterface;
use App\Models\ReadingStreak;

class ReadingStreakRepository implements ReadingStreakRepositoryInterface
{
    public function getUserStreak(int $userId): ?ReadingStreak
    {
        return ReadingStreak::where('user_id', $userId)->first();
    }

    public function createOrUpdate(int $userId, array $data): ReadingStreak
    {
        return ReadingStreak::updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }
}