<?php

namespace Src\Campaigns\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Campaigns\Domain\Exceptions\CampaignAlreadyCancelledException;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class CancelCampaignUseCase
{
    public function __construct(
        private readonly CampaignRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid, int $cancelledByOid): void
    {
        Log::info('[CancelCampaignUseCase] Starting', ['uuid' => $uuid]);

        DB::transaction(function () use ($uuid, $cancelledByOid): void {

            Log::info('[CancelCampaignUseCase] Step 1 — Finding campaign');
            $entity = $this->repository->findByUuid($uuid);

            if ($entity === null) {
                throw CampaignNotFoundException::withUuid($uuid);
            }

            Log::info('[CancelCampaignUseCase] Step 2 — Checking current status');
            if ($entity->campaignStatus === 'cancelled') {
                throw CampaignAlreadyCancelledException::withUuid($uuid);
            }

            Log::info('[CancelCampaignUseCase] Step 3 — Cancelling campaign');
            $this->repository->cancel($entity->oid, $cancelledByOid);

            Log::info('[CancelCampaignUseCase] Completed', ['name' => $entity->name]);
        });
    }
}
