<?php

namespace Src\Campaigns\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Campaigns\Application\DTOs\DTOCreateCampaignRequest;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Application\DTOs\DTOEnrollMembersRequest;
use Src\Campaigns\Domain\Exceptions\CampaignNameAlreadyExistsException;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class CreateCampaignUseCase
{
    public function __construct(
        private readonly CampaignRepositoryInterface $repository,
        private readonly EnrollMembersUseCase        $enrollMembersUseCase,
    ) {}

    public function execute(DTOCreateCampaignRequest $dto): DTOCampaignResponse
    {
        Log::info('[CreateCampaignUseCase] Starting', [
            'name'          => $dto->name,
            'target_amount' => $dto->targetAmount,
            'members'       => count($dto->memberOids),
        ]);

        return DB::transaction(function () use ($dto): DTOCampaignResponse {

            Log::info('[CreateCampaignUseCase] Step 1 — Checking name uniqueness');
            if ($this->repository->existsByName($dto->name)) {
                throw CampaignNameAlreadyExistsException::withName($dto->name);
            }

            Log::info('[CreateCampaignUseCase] Step 2 — Persisting campaign');
            $entity = $this->repository->create([
                'name'                => $dto->name,
                'description'         => $dto->description,
                'target_amount'       => $dto->targetAmount,
                'collected_amount'    => 0,
                'monthly_fee_amount'  => $dto->monthlyFeeAmount,
                'daily_penalty_rate'  => $dto->dailyPenaltyRate,
                'due_day'             => $dto->dueDay,
                'start_date'          => $dto->startDate,
                'end_date'            => $dto->endDate,
                'fund_raising_status' => 'draft',
                'status'              => true,
                'created_by_oid'      => $dto->createdByOid,
                'updated_by_oid'      => $dto->createdByOid,
            ]);

            if (!empty($dto->memberOids)) {
                Log::info('[CreateCampaignUseCase] Step 3 — Enrolling initial members');
                $enrollDto = new DTOEnrollMembersRequest(
                    campaignOid:  $entity->oid,
                    memberOids:   $dto->memberOids,
                    createdByOid: $dto->createdByOid,
                );
                $this->enrollMembersUseCase->execute($enrollDto);
            }

            Log::info('[CreateCampaignUseCase] Completed', ['uuid' => $entity->uuid]);

            return DTOCampaignResponse::fromEntity($entity);
        });
    }
}
