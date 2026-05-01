<?php

use App\Http\Controllers\Modules\Achievement\AchievementController;
use App\Http\Controllers\Modules\Ai\AiChatController;
use App\Http\Controllers\Modules\Analytics\AnalyticsController;
use App\Http\Controllers\Modules\Book\BookController;
use App\Http\Controllers\Modules\Catalog\CatalogController;
use App\Http\Controllers\Modules\Collections\CollectionController;
use App\Http\Controllers\Modules\Dashboard\DashboardController;
use App\Http\Controllers\Modules\Reader\ReaderController;
use App\Http\Controllers\Modules\Reader\ReaderFileController;
use App\Http\Controllers\Modules\ReadingSession\ReadingSessionController;
use App\Http\Controllers\Modules\Retention\RetentionController;
use App\Http\Controllers\Modules\Search\GlobalSearchController;
use App\Http\Controllers\Modules\Social\SocialController;
use App\Http\Controllers\Modules\Tags\TagController;
use App\Http\Controllers\Modules\UserShelf\UserBookController;
use App\Http\Controllers\Modules\UserShelf\UserBookRelationsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::apiResource('reading-sessions', ReadingSessionController::class);

    Route::prefix('catalog')->group(function () {
        Route::get('search', [CatalogController::class, 'search']);
        Route::get('resolve/isbn', [CatalogController::class, 'resolveByIsbn']);
    });

    Route::apiResource('tags', TagController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('collections', CollectionController::class)->only(['index', 'store', 'destroy']);

    Route::get('search', GlobalSearchController::class);

    Route::prefix('analytics')->group(function () {
        Route::get('heatmap', [AnalyticsController::class, 'heatmap']);
        Route::get('speed', [AnalyticsController::class, 'speed']);
        Route::get('yearly', [AnalyticsController::class, 'yearlyRecap']);
        Route::get('learning', [AnalyticsController::class, 'learning']);
        Route::get('suggestions/tech', [AnalyticsController::class, 'techSuggestions']);
        Route::get('export', [AnalyticsController::class, 'export']);
    });

    Route::prefix('retention')->group(function () {
        Route::get('settings', [RetentionController::class, 'settings']);
        Route::put('settings', [RetentionController::class, 'updateSettings']);
        Route::post('import/kindle', [RetentionController::class, 'importKindle']);
        Route::post('import/csv', [RetentionController::class, 'importCsv']);
        Route::get('export', [RetentionController::class, 'exportData']);
        Route::get('weekly-summary', [RetentionController::class, 'weeklySummary']);
        Route::get('goal-risk', [RetentionController::class, 'goalRisk']);
        Route::post('notifications/check', [RetentionController::class, 'checkNotifications']);
    });

    Route::apiResource('books', BookController::class);
    Route::apiResource('user-shelf', UserBookController::class)->except(['create', 'edit']);

    Route::prefix('user-shelf/{userBook}')->group(function () {
        Route::put('tags', [UserBookRelationsController::class, 'syncTags']);
        Route::put('collections', [UserBookRelationsController::class, 'syncCollections']);

        Route::post('file', [ReaderFileController::class, 'upload']);
        Route::get('file', [ReaderFileController::class, 'download']);
    });

    Route::prefix('reader/{userBook}')->group(function () {
        Route::get('/', [ReaderController::class, 'show']);
        Route::put('position', [ReaderController::class, 'upsertPosition']);
        Route::post('annotations', [ReaderController::class, 'createAnnotation']);
        Route::put('annotations/{annotation}', [ReaderController::class, 'updateAnnotation']);
        Route::delete('annotations/{annotation}', [ReaderController::class, 'deleteAnnotation']);
    });

    Route::prefix('ai')->group(function () {
        Route::post('chat/{userBook}', [AiChatController::class, 'chat']);
    });

    Route::get('achievements', [AchievementController::class, 'index']);

    Route::prefix('settings')->group(function () {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::get('security', [SecurityController::class, 'edit'])->name('security.edit');
        Route::put('password', [SecurityController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('user-password.update');
    });

    Route::prefix('social')->group(function () {
        Route::get('feed', [SocialController::class, 'feed']);
        Route::get('profile/{user}', [SocialController::class, 'profile']);
        Route::post('follow/{user}', [SocialController::class, 'follow']);
        Route::delete('follow/{user}', [SocialController::class, 'unfollow']);

        Route::get('reviews', [SocialController::class, 'listPublicReviews']);
        Route::post('reviews', [SocialController::class, 'createReview']);

        Route::get('lists', [SocialController::class, 'listMyLists']);
        Route::post('lists', [SocialController::class, 'createList']);
        Route::post('lists/{list}/items', [SocialController::class, 'addListItem']);
        Route::delete('lists/{list}/items/{item}', [SocialController::class, 'removeListItem']);

        Route::post('highlights/share/{annotation}', [SocialController::class, 'shareHighlight']);

        Route::get('clubs', [SocialController::class, 'listClubs']);
        Route::post('clubs', [SocialController::class, 'createClub']);
        Route::post('clubs/{club}/join', [SocialController::class, 'joinClub']);
        Route::post('clubs/{club}/leave', [SocialController::class, 'leaveClub']);
        Route::get('clubs/{club}/posts', [SocialController::class, 'listClubPosts']);
        Route::post('clubs/{club}/posts', [SocialController::class, 'createClubPost']);
    });
});
