<?php
use Illuminate\Foundation\Application;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Modules\ReadingSession\ReadingSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Modules\Book\BookController;
use App\Http\Controllers\Modules\UserShelf\UserBookController;
use App\Http\Controllers\Modules\Dashboard\DashboardController;





Route::get('/', function () {
    return response()->json([
        'message' => 'Bem-vindo à API do Meu Acervo Digital',
        'status' => 'online',
        'laravel_version' => Application::VERSION,
        'php_version' => PHP_VERSION,
    ]);
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Rotas para Livros (Catálogo)
        Route::resource('books', BookController::class);

        // Rotas para Estante do Usuário
        Route::resource('user-shelf', UserBookController::class)->except(['create', 'edit']); // Não precisamos de create/edit forms separados para user-shelf, será via catálogo
    
        Route::resource('reading-sessions', ReadingSessionController::class);    });

require __DIR__ . '/settings.php';
