<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/dashboard', [\App\Http\Controllers\Modules\Dashboard\DashboardController::class, 'index']);

    Route::apiResource('books', \App\Http\Controllers\Modules\Book\BookController::class);
    Route::apiResource('user-shelf', \App\Http\Controllers\Modules\UserShelf\UserBookController::class)->except(['create', 'edit']);
    Route::apiResource('reading-goals', \App\Http\Controllers\Modules\ReadingGoal\ReadingGoalController::class)->only(['index', 'store', 'update']);
});
