<?php

namespace App\Services\Modules\ReadingGoal;

use App\Interfaces\Modules\ReadingGoal\ReadingGoalRepositoryInterface;
use App\Interfaces\Modules\ReadingGoal\ReadingGoalServiceInterface;
use App\Interfaces\Modules\ReadingSession\ReadingSessionRepositoryInterface;
use App\Interfaces\Modules\UserShelf\UserBookRepositoryInterface;
use App\Models\ReadingGoal;
use Carbon\Carbon;

class ReadingGoalService implements ReadingGoalServiceInterface
{
    protected ReadingGoalRepositoryInterface $goalRepository;
    protected ReadingSessionRepositoryInterface $sessionRepository;
    protected UserBookRepositoryInterface $userBookRepository;

    public function __construct(
        ReadingGoalRepositoryInterface $goalRepository,
        ReadingSessionRepositoryInterface $sessionRepository,
        UserBookRepositoryInterface $userBookRepository
    ) {
        $this->goalRepository = $goalRepository;
        $this->sessionRepository = $sessionRepository;
        $this->userBookRepository = $userBookRepository;
    }

    public function getUserGoalsWithProgress(int $userId): array
    {
        $goals = $this->goalRepository->getUserGoals($userId);
        $result = [];

        foreach ($goals as $goal) {
            $result[] = $this->calculateProgress($goal);
        }

        return $result;
    }

    public function getActiveGoalWithProgress(int $userId, string $type): ?array
    {
        $goal = $this->goalRepository->getActiveUserGoal($userId, $type);
        if (!$goal) {
            return null;
        }

        return $this->calculateProgress($goal);
    }

    public function createGoal(int $userId, array $data): ReadingGoal
    {
        $data['user_id'] = $userId;
        
        // Deactivate previous active goals of the same type
        $activeGoal = $this->goalRepository->getActiveUserGoal($userId, $data['type']);
        if ($activeGoal) {
            $this->goalRepository->update($activeGoal->id, ['is_active' => false]);
        }

        if (empty($data['period_start'])) {
            $data['period_start'] = $this->getPeriodStart($data['type']);
        }

        return $this->goalRepository->create($data);
    }

    public function updateGoal(int $id, array $data): ReadingGoal
    {
        return $this->goalRepository->update($id, $data);
    }

    protected function calculateProgress(ReadingGoal $goal): array
    {
        $start = $goal->period_start ? Carbon::parse($goal->period_start) : $this->getPeriodStart($goal->type);
        $end = $goal->period_end ? Carbon::parse($goal->period_end) : $this->getPeriodEnd($goal->type);

        $currentValue = 0;

        if ($goal->target_unit === 'books') {
            $userBooks = $this->userBookRepository->getAllUserBooks($goal->user_id);
            $currentValue = $userBooks->filter(function ($userBook) use ($start, $end) {
                return $userBook->status === 'lido' && 
                       $userBook->finished_at && 
                       $userBook->finished_at->between($start, $end);
            })->count();
        } else {
            $sessions = $this->sessionRepository->getAllUserReadingSessions($goal->user_id);
            $filteredSessions = $sessions->filter(function ($session) use ($start, $end) {
                return $session->ended_at && $session->ended_at->between($start, $end);
            });

            if ($goal->target_unit === 'pages') {
                $currentValue = $filteredSessions->sum('pages_read');
            } elseif ($goal->target_unit === 'minutes') {
                $currentValue = $filteredSessions->sum('duration_minutes');
            }
        }

        $percentage = $goal->target_value > 0 ? ($currentValue / $goal->target_value) * 100 : 0;

        return [
            'id' => $goal->id,
            'type' => $goal->type,
            'target_value' => $goal->target_value,
            'target_unit' => $goal->target_unit,
            'current_value' => $currentValue,
            'percentage' => round(min($percentage, 100)),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'is_active' => $goal->is_active,
        ];
    }

    protected function getPeriodStart(string $type): Carbon
    {
        $now = Carbon::now();
        return match ($type) {
            'daily' => $now->startOfDay(),
            'weekly' => $now->startOfWeek(),
            'monthly' => $now->startOfMonth(),
            'yearly' => $now->startOfYear(),
            default => $now->startOfDay(),
        };
    }

    protected function getPeriodEnd(string $type): Carbon
    {
        $now = Carbon::now();
        return match ($type) {
            'daily' => $now->endOfDay(),
            'weekly' => $now->endOfWeek(),
            'monthly' => $now->endOfMonth(),
            'yearly' => $now->endOfYear(),
            default => $now->endOfDay(),
        };
    }
}