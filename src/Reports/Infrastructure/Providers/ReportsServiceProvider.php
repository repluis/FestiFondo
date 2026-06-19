<?php

namespace Src\Reports\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Reports\Domain\Repositories\ReportsRepositoryInterface;
use Src\Reports\Infrastructure\Repositories\EloquentReportsRepository;

class ReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ReportsRepositoryInterface::class,
            EloquentReportsRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}
