<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

// Importar Interfaces
use App\Interfaces\Modules\Book\BookRepositoryInterface;
use App\Interfaces\Modules\Book\BookServiceInterface;
use App\Interfaces\Modules\UserShelf\UserBookRepositoryInterface;
use App\Interfaces\Modules\UserShelf\UserBookServiceInterface;
use App\Interfaces\Modules\ReadingSession\ReadingSessionRepositoryInterface;
use App\Interfaces\Modules\ReadingSession\ReadingSessionServiceInterface;
use App\Interfaces\Modules\Statistics\StatisticsServiceInterface;

// Importar Implementações
use App\Repositories\Modules\Book\BookRepository;
use App\Repositories\Modules\UserShelf\UserBookRepository;
use App\Repositories\Modules\ReadingSession\ReadingSessionRepository;
use App\Services\Modules\Book\BookService;
use App\Services\Modules\UserShelf\UserBookService;
use App\Services\Modules\ReadingSession\ReadingSessionService;
use App\Services\Modules\Statistics\StatisticsService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositórios
        $this->app->bind(BookRepositoryInterface::class, BookRepository::class);
        $this->app->bind(UserBookRepositoryInterface::class, UserBookRepository::class);
        $this->app->bind(ReadingSessionRepositoryInterface::class, ReadingSessionRepository::class);

        // Serviços
        $this->app->bind(BookServiceInterface::class, BookService::class);
        $this->app->bind(UserBookServiceInterface::class, UserBookService::class);
        $this->app->bind(ReadingSessionServiceInterface::class, ReadingSessionService::class);
        $this->app->bind(StatisticsServiceInterface::class, StatisticsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
