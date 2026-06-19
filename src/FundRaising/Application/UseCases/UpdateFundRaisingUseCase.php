<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\FundRaising\Application\DTOs\DTOFundRaisingResponse;
use Src\FundRaising\Application\DTOs\DTOUpdateFundRaisingRequest;
use Src\FundRaising\Domain\Exceptions\FundRaisingAlreadyCancelledException;
use Src\FundRaising\Domain\Exceptions\FundRaisingInvalidStatusTransitionException;
use Src\FundRaising\Domain\Exceptions\FundRaisingNameAlreadyExistsException;
use Src\FundRaising\Domain\Exceptions\FundRaisingNotFoundException;
use Src\FundRaising\Domain\Repositories\FundRaisingRepositoryInterface;

class UpdateFundRaisingUseCase
{
    private const VALID_TRANSITIONS = [
        'draft'     => ['active', 'cancelled'],
        'active'    => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(
        private readonly FundRaisingRepositoryInterface $repository,
    ) {}

    public function execute(DTOUpdateFundRaisingRequest $dto): DTOFundRaisingResponse
    {
        Log::info('[UpdateFundRaisingUseCase] Starting', ['uuid' => $dto->fundRaisingUuid]);

        return DB::transaction(function () use ($dto): DTOFundRaisingResponse {

            Log::info('[UpdateFundRaisingUseCase] Step 1 — Finding campaign');
            $entity = $this->repository->findByUuid($dto->fundRaisingUuid);

            if ($entity === null) {
                throw FundRaisingNotFoundException::withUuid($dto->fundRaisingUuid);
            }

            Log::info('[UpdateFundRaisingUseCase] Step 2 — Checking current status');
            if ($entity->fundRaisingStatus === 'cancelled') {
                throw FundRaisingAlreadyCancelledException::withUuid($dto->fundRaisingUuid);
            }

            Log::info('[UpdateFundRaisingUseCase] Step 3 — Validating status transition');
            $currentStatus   = $entity->fundRaisingStatus;
            $requestedStatus = $dto->fundRaisingStatus;

            if ($currentStatus !== $requestedStatus) {
                $allowed = self::VALID_TRANSITIONS[$currentStatus] ?? [];
                if (!in_array($requestedStatus, $allowed, true)) {
                    throw FundRaisingInvalidStatusTransitionException::from($currentStatus, $requestedStatus);
                }
            }

            Log::info('[UpdateFundRaisingUseCase] Step 4 — Checking name uniqueness');
            if ($this->repository->existsByName($dto->name, $entity->oid)) {
                throw FundRaisingNameAlreadyExistsException::withName($dto->name);
            }

            Log::info('[UpdateFundRaisingUseCase] Step 5 — Persisting changes');
            $updated = $this->repository->update($entity->oid, [
                'name'                => $dto->name,
                'description'         => $dto->description,
                'target_amount'       => $dto->targetAmount,
                'start_date'          => $dto->startDate,
                'end_date'            => $dto->endDate,
                'fund_raising_status' => $dto->fundRaisingStatus,
                'updated_by_oid'      => $dto->updatedByOid,
            ]);

            Log::info('[UpdateFundRaisingUseCase] Completed', ['uuid' => $updated->uuid]);

            return DTOFundRaisingResponse::fromEntity($updated);
        });
    }
}
