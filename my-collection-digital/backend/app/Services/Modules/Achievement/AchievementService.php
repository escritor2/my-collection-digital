<?php

namespace App\Services\Modules\Achievement;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function checkAchievements(User $user)
    {
        $achievements = DB::table('achievements')->get();
        $userAchievements = DB::table('user_achievements')
            ->where('user_id', $user->id)
            ->pluck('achievement_id')
            ->toArray();

        $newAchievements = [];

        foreach ($achievements as $achievement) {
            if (in_array($achievement->id, $userAchievements)) {
                continue;
            }

            $criteria = json_decode($achievement->criteria, true);
            if ($this->meetsCriteria($user, $criteria)) {
                $this->awardAchievement($user, $achievement);
                $newAchievements[] = $achievement;
            }
        }

        return $newAchievements;
    }

    private function meetsCriteria(User $user, array $criteria)
    {
        switch ($criteria['type']) {
            case 'pages_read':
                $totalRead = DB::table('reader_positions')
                    ->join('user_books', 'reader_positions.user_book_id', '=', 'user_books.id')
                    ->where('user_books.user_id', $user->id)
                    ->sum('progress_pages');

                return $totalRead >= $criteria['value'];

            case 'finished_tech_books':
                $finishedTech = DB::table('user_books')
                    ->join('books', 'user_books.book_id', '=', 'books.id')
                    ->where('user_books.user_id', $user->id)
                    ->where('user_books.progress_pages', '>=', DB::raw('books.page_count'))
                    ->where('books.is_programming', true) // Assuming there is an is_programming flag
                    ->count();

                return $finishedTech >= $criteria['value'];

            case 'streak_days':
                return $user->streak_days >= $criteria['value'];

            case 'ai_queries':
                // Assuming we track AI queries somewhere, e.g., in a logs table or user column
                // For now, let's assume there's a column or we just return false if not implemented
                return ($user->ai_queries_count ?? 0) >= $criteria['value'];

            default:
                return false;
        }
    }

    private function awardAchievement(User $user, $achievement)
    {
        DB::table('user_achievements')->insert([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Award XP
        $user->increment('xp', $achievement->xp_reward);

        // Check for level up
        $newLevel = floor($user->xp / 1000) + 1;
        if ($newLevel > $user->level) {
            $user->update(['level' => $newLevel]);
        }
    }
}
