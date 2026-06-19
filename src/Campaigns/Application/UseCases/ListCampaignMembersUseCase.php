<?php

namespace Src\Campaigns\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Campaigns\Domain\Repositories\CampaignMemberRepositoryInterface;

class ListCampaignMembersUseCase
{
    public function __construct(
        private readonly CampaignMemberRepositoryInterface $repository,
    ) {}

    public function execute(int $campaignOid): array
    {
        Log::info('[ListCampaignMembersUseCase] Starting', ['campaign_oid' => $campaignOid]);

        $members = $this->repository->listMembersWithBalance($campaignOid);

        Log::info('[ListCampaignMembersUseCase] Completed', ['count' => count($members)]);

        return $members;
    }
}
