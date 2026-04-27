<?php

namespace App\Interfaces\Modules\ReadingGoal;

use App\Models\ReadingGoal;
use Illuminate\Support\Collection;

interface ReadingGoalRepositoryInterface
{
    public function getUserGoals(int $userId): Collection;
    public function getActiveUserGoal(int $userId, string $type): ?ReadingGoal;
    public function create(array $data): ReadingGoal;
    public function update(int $id, array $data): ReadingGoal;
}