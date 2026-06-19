<?php

namespace Src\Campaigns\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Application\DTOs\DTOUpdateCampaignRequest;
use Src\Campaigns\Domain\Exceptions\CampaignAlreadyCancelledException;
use Src\Campaigns\Domain\Exceptions\CampaignInvalidStatusTransitionException;
use Src\Campaigns\Domain\Exceptions\CampaignNameAlreadyExistsException;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class UpdateCampaignUseCase
{
    private const VALID_TRANSITIONS = [
        'draft'     => ['active', 'cancelled'],
        'active'    => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function __construct(
        private readonly CampaignRepositoryInterface $repository,
    ) {}

    public function execute(DTOUpdateCampaignRequest $dto): DTOCampaignResponse
    {
        Log::info('[UpdateCampaignUseCase] Starting', ['uuid' => $dto->campaignUuid]);

        return DB::transaction(function () use ($dto): DTOCampaignResponse {

            Log::info('[UpdateCampaignUseCase] Step 1 — Finding campaign');
            $entity = $this->repository->findByUuid($dto->campaignUuid);

            if ($entity === null) {
                throw CampaignNotFoundException::withUuid($dto->campaignUuid);
            }

            Log::info('[UpdateCampaignUseCase] Step 2 — Checking current status');
            if ($entity->campaignStatus === 'cancelled') {
                throw CampaignAlreadyCancelledException::withUuid($dto->campaignUuid);
            }

            Log::info('[UpdateCampaignUseCase] Step 3 — Validating status transition');
            $current   = $entity->campaignStatus;
            $requested = $dto->campaignStatus;

            if ($current !== $requested) {
                $allowed = self::VALID_TRANSITIONS[$current] ?? [];
                if (!in_array($requested, $allowed, true)) {
                    throw CampaignInvalidStatusTransitionException::from($current, $requested);
                }
            }

            Log::info('[UpdateCampaignUseCase] Step 4 — Checking name uniqueness');
            if ($this->repository->existsByName($dto->name, $entity->oid)) {
                throw CampaignNameAlreadyExistsException::withName($dto->name);
            }

            Log::info('[UpdateCampaignUseCase] Step 5 — Persisting changes');
            $updated = $this->repository->update($entity->oid, [
                'name'                => $dto->name,
                'description'         => $dto->description,
                'target_amount'       => $dto->targetAmount,
                'monthly_fee_amount'  => $dto->monthlyFeeAmount,
                'daily_penalty_rate'  => $dto->dailyPenaltyRate,
                'due_day'             => $dto->dueDay,
                'start_date'          => $dto->startDate,
                'end_date'            => $dto->endDate,
                'fund_raising_status' => $dto->campaignStatus,
                'updated_by_oid'      => $dto->updatedByOid,
            ]);

            Log::info('[UpdateCampaignUseCase] Completed', ['uuid' => $updated->uuid]);

            return DTOCampaignResponse::fromEntity($updated);
        });
    }
}
