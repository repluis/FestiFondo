<?php

namespace Src\Transactions\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Transactions\Domain\Repositories\TransactionRepositoryInterface;
use Src\Transactions\Infrastructure\Repositories\EloquentTransactionRepository;

class TransactionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TransactionRepositoryInterface::class,
            EloquentTransactionRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}
