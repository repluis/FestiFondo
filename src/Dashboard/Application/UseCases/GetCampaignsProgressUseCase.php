<?php

namespace Src\Dashboard\Application\UseCases;

use Src\Dashboard\Domain\Entities\CampaignProgress;
use Src\Dashboard\Domain\Repositories\DashboardRepositoryInterface;

class GetCampaignsProgressUseCase
{
    public function __construct(private DashboardRepositoryInterface $repository) {}

    /** @return CampaignProgress[] */
    public function execute(): array
    {
        return $this->repository->getCampaignsProgress();
    }
}
