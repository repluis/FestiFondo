<?php

namespace Src\Campaigns\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Campaigns\Domain\Repositories\CampaignMemberRepositoryInterface;

class GetMemberTransactionsUseCase
{
    public function __construct(
        private readonly CampaignMemberRepositoryInterface $repository,
    ) {}

    public function execute(int $campaignOid, int $memberOid): array
    {
        Log::info('[GetMemberTransactionsUseCase] Starting', [
            'campaign_oid' => $campaignOid,
            'member_oid'   => $memberOid,
        ]);
        $transactions = $this->repository->memberTransactions($campaignOid, $memberOid);
        Log::info('[GetMemberTransactionsUseCase] Completed', ['count' => count($transactions)]);
        return $transactions;
    }
}
