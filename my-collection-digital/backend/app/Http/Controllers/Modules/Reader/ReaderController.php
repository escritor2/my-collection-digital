<?php

namespace App\Http\Controllers\Modules\Reader;

use App\Http\Controllers\Controller;
use App\Models\ReaderAnnotation;
use App\Models\ReaderPosition;
use App\Models\UserBook;
use App\Services\Modules\Achievement\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReaderController extends Controller
{
    protected $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    private function requireUserBook(int $userBookId): UserBook
    {
        return UserBook::with('book')
            ->where('id', $userBookId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function show(int $userBookId)
    {
        $userBook = $this->requireUserBook($userBookId);

        $position = ReaderPosition::where('user_id', Auth::id())
            ->where('user_book_id', $userBook->id)
            ->first();

        $annotations = ReaderAnnotation::where('user_id', Auth::id())
            ->where('user_book_id', $userBook->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $media = $userBook->getFirstMedia('source');

        return response()->json([
            'data' => [
                'user_book' => $userBook,
                'file' => $media ? [
                    'name' => $media->name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'download_url' => url("/api/user-shelf/{$userBook->id}/file"),
                ] : null,
                'position' => $position,
                'annotations' => $annotations,
            ],
        ]);
    }

    public function upsertPosition(Request $request, int $userBookId)
    {
        $userBook = $this->requireUserBook($userBookId);

        $validated = $request->validate([
            'format' => ['required', Rule::in(['pdf', 'epub'])],
            'locator' => ['required', 'array'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pages_read_increment' => ['nullable', 'integer', 'min:0'],
        ]);

        $position = ReaderPosition::updateOrCreate(
            ['user_id' => Auth::id(), 'user_book_id' => $userBook->id],
            $validated
        );

        // Gamification logic
        $user = Auth::user();
        $xpGain = 0;

        if (isset($validated['pages_read_increment']) && $validated['pages_read_increment'] > 0) {
            $xpGain += $validated['pages_read_increment'] * 10; // 10 XP per page
        }

        if ($xpGain > 0) {
            $user->xp += $xpGain;

            // Level up logic (simple: every 1000 XP)
            $newLevel = floor($user->xp / 1000) + 1;
            if ($newLevel > $user->level) {
                $user->level = $newLevel;
                // Possible badge award logic here
            }

            // Streak logic
            $today = now()->startOfDay();
            if (! $user->last_reading_at || $user->last_reading_at->startOfDay()->lt($today)) {
                if ($user->last_reading_at && $user->last_reading_at->startOfDay()->diffInDays($today) === 1) {
                    $user->streak_days += 1;
                } else {
                    $user->streak_days = 1;
                }
            }

            $user->last_reading_at = now();
            $user->save();
        }

        $newAchievements = $this->achievementService->checkAchievements($user);

        return response()->json([
            'data' => $position,
            'gamification' => [
                'xp_gained' => $xpGain,
                'total_xp' => $user->xp,
                'level' => $user->level,
                'streak' => $user->streak_days,
                'new_achievements' => $newAchievements,
            ],
        ]);
    }

    public function createAnnotation(Request $request, int $userBookId)
    {
        $userBook = $this->requireUserBook($userBookId);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['bookmark', 'highlight', 'note'])],
            'locator' => ['required', 'array'],
            'selected_text' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:32'],
        ]);

        $annotation = ReaderAnnotation::create([
            'user_id' => Auth::id(),
            'user_book_id' => $userBook->id,
            ...$validated,
        ]);

        return response()->json(['data' => $annotation], 201);
    }

    public function updateAnnotation(Request $request, int $userBookId, int $annotationId)
    {
        $this->requireUserBook($userBookId);

        $annotation = ReaderAnnotation::where('id', $annotationId)
            ->where('user_id', Auth::id())
            ->where('user_book_id', $userBookId)
            ->firstOrFail();

        $validated = $request->validate([
            'note' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:32'],
        ]);

        $annotation->update($validated);

        return response()->json(['data' => $annotation->fresh()]);
    }

    public function deleteAnnotation(int $userBookId, int $annotationId)
    {
        $this->requireUserBook($userBookId);

        $annotation = ReaderAnnotation::where('id', $annotationId)
            ->where('user_id', Auth::id())
            ->where('user_book_id', $userBookId)
            ->firstOrFail();

        $annotation->delete();

        return response()->json(['message' => 'Annotation deleted']);
    }
}
