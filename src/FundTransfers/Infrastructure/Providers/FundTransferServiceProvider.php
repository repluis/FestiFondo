<?php

namespace Src\FundTransfers\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class FundTransferServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}

