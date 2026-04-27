<?php

namespace App\Interfaces\Modules\Statistics;

interface StatisticsServiceInterface
{
    public function getUserReadingStatistics(int $userId): array;
}
