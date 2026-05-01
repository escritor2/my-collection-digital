<?php

namespace App\Http\Controllers\Modules\Dashboard;

use App\Http\Controllers\Controller;
use App\Interfaces\Modules\Statistics\StatisticsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected StatisticsServiceInterface $statisticsService;

    public function __construct(StatisticsServiceInterface $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Display the user's dashboard with reading statistics.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $statistics = $this->statisticsService->getUserReadingStatistics(Auth::id());

        return response()->json([
            'data' => $statistics,
        ]);
    }
}
