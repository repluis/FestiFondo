<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\FundRaising\Application\DTOs\DTOCreateFundRaisingRequest;
use Src\FundRaising\Application\DTOs\DTOEnrollMembersRequest;
use Src\FundRaising\Application\DTOs\DTOFundRaisingResponse;
use Src\FundRaising\Domain\Exceptions\FundRaisingNameAlreadyExistsException;
use Src\FundRaising\Domain\Repositories\FundRaisingRepositoryInterface;

class CreateFundRaisingUseCase
{
    public function __construct(
        private readonly FundRaisingRepositoryInterface    $repository,
        private readonly EnrollMembersUseCase              $enrollMembersUseCase,
    ) {}

    public function execute(DTOCreateFundRaisingRequest $dto): DTOFundRaisingResponse
    {
        Log::info('[CreateFundRaisingUseCase] Starting', [
            'name'          => $dto->name,
            'target_amount' => $dto->targetAmount,
            'members'       => count($dto->memberOids),
        ]);

        return DB::transaction(function () use ($dto): DTOFundRaisingResponse {

            Log::info('[CreateFundRaisingUseCase] Step 1 — Checking name uniqueness');
            if ($this->repository->existsByName($dto->name)) {
                throw FundRaisingNameAlreadyExistsException::withName($dto->name);
            }

            Log::info('[CreateFundRaisingUseCase] Step 2 — Persisting fund raising campaign');
            $entity = $this->repository->create([
                'name'                 => $dto->name,
                'description'          => $dto->description,
                'target_amount'        => $dto->targetAmount,
                'collected_amount'     => 0,
                'start_date'           => $dto->startDate,
                'end_date'             => $dto->endDate,
                'fund_raising_status'  => 'draft',
                'status'               => true,
                'created_by_oid'       => $dto->createdByOid,
                'updated_by_oid'       => $dto->createdByOid,
            ]);

            if (!empty($dto->memberOids)) {
                Log::info('[CreateFundRaisingUseCase] Step 3 — Enrolling initial members');
                $enrollDto = new DTOEnrollMembersRequest(
                    campaignOid:  $entity->oid,
                    memberOids:   $dto->memberOids,
                    createdByOid: $dto->createdByOid,
                );
                $this->enrollMembersUseCase->execute($enrollDto);
            }

            Log::info('[CreateFundRaisingUseCase] Completed', ['uuid' => $entity->uuid]);

            return DTOFundRaisingResponse::fromEntity($entity);
        });
    }
}
