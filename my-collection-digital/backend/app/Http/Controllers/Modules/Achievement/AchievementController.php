<?php

namespace App\Http\Controllers\Modules\Achievement;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AchievementController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $achievements = DB::table('achievements')
            ->select('achievements.*', 'user_achievements.earned_at')
            ->leftJoin('user_achievements', function ($join) use ($user) {
                $join->on('achievements.id', '=', 'user_achievements.achievement_id')
                    ->where('user_achievements.user_id', '=', $user->id);
            })
            ->get()
            ->map(function ($achievement) {
                $achievement->is_earned = ! is_null($achievement->earned_at);

                return $achievement;
            });

        return response()->json([
            'data' => $achievements,
        ]);
    }
}
