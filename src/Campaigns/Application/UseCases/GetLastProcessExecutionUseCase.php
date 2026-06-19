<?php

namespace Src\Campaigns\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class GetLastProcessExecutionUseCase
{
    public function __construct(
        private readonly CampaignRepositoryInterface $repository,
    ) {}

    public function execute(?string $campaignUuid = null): ?\stdClass
    {
        Log::info('[GetLastProcessExecutionUseCase] Starting', ['campaign_uuid' => $campaignUuid]);
        $result = $this->repository->getLastProcessExecution($campaignUuid);
        Log::info('[GetLastProcessExecutionUseCase] Completed', ['found' => $result !== null]);
        return $result;
    }
}
