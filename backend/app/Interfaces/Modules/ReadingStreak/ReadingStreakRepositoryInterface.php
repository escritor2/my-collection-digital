<?php

namespace App\Interfaces\Modules\ReadingStreak;

use App\Models\ReadingStreak;

interface ReadingStreakRepositoryInterface
{
    public function getUserStreak(int $userId): ?ReadingStreak;
    public function createOrUpdate(int $userId, array $data): ReadingStreak;
}