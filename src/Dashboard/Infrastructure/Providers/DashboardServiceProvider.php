<?php

namespace Src\Dashboard\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Dashboard\Domain\Repositories\DashboardRepositoryInterface;
use Src\Dashboard\Infrastructure\Repositories\EloquentDashboardRepository;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DashboardRepositoryInterface::class, EloquentDashboardRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}
