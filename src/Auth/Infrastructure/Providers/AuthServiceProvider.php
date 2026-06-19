<?php

namespace Src\Auth\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Auth\Application\Services\AuthService;
use Src\Auth\Application\UseCases\LoginUseCase;
use Src\Auth\Application\UseCases\LogoutUseCase;
use Src\Auth\Application\UseCases\RegisterUseCase;
use Src\Auth\Domain\Repositories\AuthenticationRepositoryInterface;
use Src\Auth\Infrastructure\Repositories\EloquentAuthRepository;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthenticationRepositoryInterface::class, EloquentAuthRepository::class);

        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService(
                loginUseCase: $app->make(LoginUseCase::class),
                logoutUseCase: $app->make(LogoutUseCase::class),
                registerUseCase: $app->make(RegisterUseCase::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}
