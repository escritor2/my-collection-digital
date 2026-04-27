<?php

namespace App\Repositories\Modules\ReadingGoal;

use App\Interfaces\Modules\ReadingGoal\ReadingGoalRepositoryInterface;
use App\Models\ReadingGoal;
use Illuminate\Support\Collection;

class ReadingGoalRepository implements ReadingGoalRepositoryInterface
{
    public function getUserGoals(int $userId): Collection
    {
        return ReadingGoal::where('user_id', $userId)->get();
    }

    public function getActiveUserGoal(int $userId, string $type): ?ReadingGoal
    {
        return ReadingGoal::where('user_id', $userId)
            ->where('type', $type)
            ->where('is_active', true)
            ->first();
    }

    public function create(array $data): ReadingGoal
    {
        return ReadingGoal::create($data);
    }

    public function update(int $id, array $data): ReadingGoal
    {
        $goal = ReadingGoal::findOrFail($id);
        $goal->update($data);
        return $goal;
    }
}