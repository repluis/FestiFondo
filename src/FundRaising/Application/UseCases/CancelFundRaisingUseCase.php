<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\FundRaising\Domain\Exceptions\FundRaisingAlreadyCancelledException;
use Src\FundRaising\Domain\Exceptions\FundRaisingNotFoundException;
use Src\FundRaising\Domain\Repositories\FundRaisingRepositoryInterface;

class CancelFundRaisingUseCase
{
    public function __construct(
        private readonly FundRaisingRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid, int $cancelledByOid): void
    {
        Log::info('[CancelFundRaisingUseCase] Starting', ['uuid' => $uuid]);

        DB::transaction(function () use ($uuid, $cancelledByOid): void {

            Log::info('[CancelFundRaisingUseCase] Step 1 — Finding campaign');
            $entity = $this->repository->findByUuid($uuid);

            if ($entity === null) {
                throw FundRaisingNotFoundException::withUuid($uuid);
            }

            Log::info('[CancelFundRaisingUseCase] Step 2 — Checking current status');
            if ($entity->fundRaisingStatus === 'cancelled') {
                throw FundRaisingAlreadyCancelledException::withUuid($uuid);
            }

            Log::info('[CancelFundRaisingUseCase] Step 3 — Cancelling campaign');
            $this->repository->cancel($entity->oid, $cancelledByOid);

            Log::info('[CancelFundRaisingUseCase] Completed', ['name' => $entity->name]);
        });
    }
}
