<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\FundRaising\Application\DTOs\DTOCampaignMemberResponse;
use Src\FundRaising\Application\DTOs\DTOEnrollMembersRequest;
use Src\FundRaising\Domain\Exceptions\CampaignMemberAlreadyEnrolledException;
use Src\FundRaising\Domain\Repositories\CampaignMemberRepositoryInterface;

class EnrollMembersUseCase
{
    public function __construct(
        private readonly CampaignMemberRepositoryInterface $repository,
    ) {}

    /** @return DTOCampaignMemberResponse[] */
    public function execute(DTOEnrollMembersRequest $dto): array
    {
        Log::info('[EnrollMembersUseCase] Starting', [
            'campaign_oid' => $dto->campaignOid,
            'member_count' => count($dto->memberOids),
        ]);

        return DB::transaction(function () use ($dto): array {
            $enrolled = [];

            foreach ($dto->memberOids as $memberOid) {
                Log::info('[EnrollMembersUseCase] Step 1 — Checking existing enrollment', [
                    'member_oid' => $memberOid,
                ]);

                $existing = $this->repository->findActiveByCampaignAndMember(
                    $dto->campaignOid,
                    (int) $memberOid
                );

                if ($existing !== null) {
                    throw CampaignMemberAlreadyEnrolledException::forMember(
                        (int) $memberOid,
                        $dto->campaignOid
                    );
                }

                Log::info('[EnrollMembersUseCase] Step 2 — Enrolling member', [
                    'member_oid' => $memberOid,
                ]);

                $entity = $this->repository->enroll([
                    'campaign_oid'    => $dto->campaignOid,
                    'member_oid'      => (int) $memberOid,
                    'enrolled_at'     => now()->toDateString(),
                    'status'          => true,
                    'created_by_oid'  => $dto->createdByOid,
                    'updated_by_oid'  => $dto->createdByOid,
                ]);

                $enrolled[] = DTOCampaignMemberResponse::fromEntity($entity);
            }

            Log::info('[EnrollMembersUseCase] Completed', ['enrolled' => count($enrolled)]);

            return $enrolled;
        });
    }
}
