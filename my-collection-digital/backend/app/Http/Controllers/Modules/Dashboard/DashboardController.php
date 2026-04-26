<?php

namespace App\Http\Controllers\Modules\Dashboard;

use App\Http\Controllers\Controller;
use App\Interfaces\Modules\Statistics\StatisticsServiceInterface;
use Inertia\Inertia;
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
     * @return \Inertia\Response
     */
    public function index()
    {
        $statistics = $this->statisticsService->getUserReadingStatistics(Auth::id());

        return Inertia::render(
            'Dashboard',
            [
                'statistics' => $statistics,
            ]
        );
    }
}
