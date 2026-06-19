<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\FundRaising\Domain\Exceptions\CampaignMemberNotFoundException;
use Src\FundRaising\Domain\Repositories\CampaignMemberRepositoryInterface;

class RemoveCampaignMemberUseCase
{
    public function __construct(
        private readonly CampaignMemberRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid, int $updatedByOid): void
    {
        Log::info('[RemoveCampaignMemberUseCase] Starting', ['uuid' => $uuid]);

        DB::transaction(function () use ($uuid, $updatedByOid): void {

            Log::info('[RemoveCampaignMemberUseCase] Step 1 — Finding campaign member');
            $member = $this->repository->findByUuid($uuid);

            if ($member === null) {
                throw CampaignMemberNotFoundException::withUuid($uuid);
            }

            Log::info('[RemoveCampaignMemberUseCase] Step 2 — Deactivating membership', [
                'oid' => $member->oid,
            ]);

            $this->repository->remove($member->oid, $updatedByOid);

            Log::info('[RemoveCampaignMemberUseCase] Completed', ['uuid' => $uuid]);
        });
    }
}
