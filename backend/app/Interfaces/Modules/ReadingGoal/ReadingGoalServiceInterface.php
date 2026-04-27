<?php

namespace App\Interfaces\Modules\ReadingGoal;

use App\Models\ReadingGoal;

interface ReadingGoalServiceInterface
{
    public function getUserGoalsWithProgress(int $userId): array;
    public function getActiveGoalWithProgress(int $userId, string $type): ?array;
    public function createGoal(int $userId, array $data): ReadingGoal;
    public function updateGoal(int $id, array $data): ReadingGoal;
}