<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\FundRaising\Domain\Repositories\CampaignMemberRepositoryInterface;

class ListCampaignMembersUseCase
{
    public function __construct(
        private readonly CampaignMemberRepositoryInterface $repository,
    ) {}

    /** @return array[] each row has member info + fees_balance + penalties_balance + total_paid_in_campaign */
    public function execute(int $campaignOid): array
    {
        Log::info('[ListCampaignMembersUseCase] Starting', ['campaign_oid' => $campaignOid]);

        $members = $this->repository->listMembersWithBalance($campaignOid);

        Log::info('[ListCampaignMembersUseCase] Completed', ['count' => count($members)]);

        return $members;
    }
}
