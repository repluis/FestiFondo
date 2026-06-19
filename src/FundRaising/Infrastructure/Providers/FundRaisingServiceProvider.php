<?php

namespace Src\FundRaising\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\FundRaising\Domain\Repositories\CampaignMemberRepositoryInterface;
use Src\FundRaising\Domain\Repositories\FundRaisingRepositoryInterface;
use Src\FundRaising\Domain\Repositories\ProcessChargesRepositoryInterface;
use Src\FundRaising\Infrastructure\Repositories\EloquentCampaignMemberRepository;
use Src\FundRaising\Infrastructure\Repositories\EloquentFundRaisingRepository;
use Src\FundRaising\Infrastructure\Repositories\EloquentProcessChargesRepository;

class FundRaisingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            FundRaisingRepositoryInterface::class,
            EloquentFundRaisingRepository::class,
        );

        $this->app->bind(
            CampaignMemberRepositoryInterface::class,
            EloquentCampaignMemberRepository::class,
        );

        $this->app->bind(
            ProcessChargesRepositoryInterface::class,
            EloquentProcessChargesRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}
