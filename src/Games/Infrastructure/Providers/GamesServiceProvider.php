<?php

namespace Src\Games\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Games\Domain\Repositories\GamesRepositoryInterface;
use Src\Games\Infrastructure\Repositories\EloquentGamesRepository;

class GamesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GamesRepositoryInterface::class, EloquentGamesRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}
