<?php

namespace App\Http\Controllers\Modules\Dashboard;

use App\Http\Controllers\Controller;
use App\Interfaces\Modules\Statistics\StatisticsServiceInterface;
use App\Interfaces\Modules\ReadingGoal\ReadingGoalServiceInterface;
use App\Interfaces\Modules\ReadingStreak\ReadingStreakServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    protected StatisticsServiceInterface $statisticsService;
    protected ReadingGoalServiceInterface $goalService;
    protected ReadingStreakServiceInterface $streakService;

    public function __construct(
        StatisticsServiceInterface $statisticsService,
        ReadingGoalServiceInterface $goalService,
        ReadingStreakServiceInterface $streakService
    ) {
        $this->statisticsService = $statisticsService;
        $this->goalService = $goalService;
        $this->streakService = $streakService;
    }

    /**
     * Display the user's dashboard with reading statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        $statistics = $this->statisticsService->getUserReadingStatistics($userId);
        
        // We will default to showing the daily active goal on the dashboard, if none, maybe weekly? 
        // Let's just fetch the active daily goal for now, or whatever the user prefers.
        // Usually dashboards show the daily goal. Let's fetch daily.
        $activeGoal = $this->goalService->getActiveGoalWithProgress($userId, 'daily');
        if (!$activeGoal) {
            $activeGoal = $this->goalService->getActiveGoalWithProgress($userId, 'weekly');
        }
        if (!$activeGoal) {
            $activeGoal = $this->goalService->getActiveGoalWithProgress($userId, 'monthly');
        }

        $streak = $this->streakService->getUserStreak($userId);

        return response()->json([
            'statistics' => $statistics,
            'active_goal' => $activeGoal,
            'streak' => $streak,
        ]);
    }
}
