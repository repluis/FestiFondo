<?php

namespace Src\Campaigns\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Campaigns\Domain\Repositories\CampaignMemberRepositoryInterface;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;
use Src\Campaigns\Infrastructure\Repositories\EloquentCampaignMemberRepository;
use Src\Campaigns\Infrastructure\Repositories\EloquentCampaignRepository;

class CampaignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CampaignRepositoryInterface::class,
            EloquentCampaignRepository::class,
        );

        $this->app->bind(
            CampaignMemberRepositoryInterface::class,
            EloquentCampaignMemberRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes/web.php');
    }
}
