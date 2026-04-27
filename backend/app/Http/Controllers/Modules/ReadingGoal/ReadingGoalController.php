<?php

namespace App\Http\Controllers\Modules\ReadingGoal;

use App\Http\Controllers\Controller;
use App\Interfaces\Modules\ReadingGoal\ReadingGoalServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReadingGoalController extends Controller
{
    protected ReadingGoalServiceInterface $goalService;

    public function __construct(ReadingGoalServiceInterface $goalService)
    {
        $this->goalService = $goalService;
    }

    public function index(Request $request): JsonResponse
    {
        $goals = $this->goalService->getUserGoalsWithProgress($request->user()->id);
        return response()->json($goals);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:daily,weekly,monthly,yearly',
            'target_value' => 'required|integer|min:1',
            'target_unit' => 'required|in:pages,minutes,books',
        ]);

        $goal = $this->goalService->createGoal($request->user()->id, $validated);

        return response()->json($goal, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'target_value' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        $goal = $this->goalService->updateGoal($id, $validated);

        return response()->json($goal);
    }
}