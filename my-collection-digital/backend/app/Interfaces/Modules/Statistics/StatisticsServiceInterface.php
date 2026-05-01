<?php

namespace App\Interfaces\Modules\Statistics;

interface StatisticsServiceInterface
{
    public function getUserReadingStatistics(int $userId): array;

    public function getReadingHeatmap(int $userId, int $days = 120): array;

    public function getReadingSpeed(int $userId, int $days = 30): array;

    public function getYearlyRecap(int $userId, int $year): array;

    public function getLearningStatistics(int $userId): array;
}
