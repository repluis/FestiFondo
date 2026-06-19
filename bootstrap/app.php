<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \Src\FundTransfers\Infrastructure\Providers\FundTransferServiceProvider::class,
        \Src\FundRaising\Infrastructure\Providers\FundRaisingServiceProvider::class,
        \Src\Campaigns\Infrastructure\Providers\CampaignServiceProvider::class,
        \Src\Auth\Infrastructure\Providers\AuthServiceProvider::class,
        \Src\Members\Infrastructure\Providers\MembersServiceProvider::class,
        \Src\Transactions\Infrastructure\Providers\TransactionServiceProvider::class,
        \Src\Reports\Infrastructure\Providers\ReportsServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
