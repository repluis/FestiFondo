<?php

namespace Src\Members\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Members\Domain\Repositories\MembersRepositoryInterface;
use Src\Members\Infrastructure\Repositories\EloquentMembersRepository;

class MembersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            MembersRepositoryInterface::class,
            EloquentMembersRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}
