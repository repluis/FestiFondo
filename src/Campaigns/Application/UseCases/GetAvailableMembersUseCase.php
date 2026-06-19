<?php

namespace Src\Campaigns\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Campaigns\Domain\Repositories\CampaignMemberRepositoryInterface;

class GetAvailableMembersUseCase
{
    public function __construct(
        private readonly CampaignMemberRepositoryInterface $repository,
    ) {}

    public function execute(int $campaignOid): array
    {
        Log::info('[GetAvailableMembersUseCase] Starting', ['campaign_oid' => $campaignOid]);
        $members = $this->repository->availableMembers($campaignOid);
        Log::info('[GetAvailableMembersUseCase] Completed', ['count' => count($members)]);
        return $members;
    }
}
