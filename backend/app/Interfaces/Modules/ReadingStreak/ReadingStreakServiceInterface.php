<?php

namespace App\Interfaces\Modules\ReadingStreak;

interface ReadingStreakServiceInterface
{
    public function getUserStreak(int $userId): array;
    public function recordCheckIn(int $userId): array;
}