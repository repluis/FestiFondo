<?php

namespace Src\Campaigns\Application\Services;

use Src\Campaigns\Application\DTOs\DTOCampaignMemberResponse;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Application\DTOs\DTOCreateCampaignRequest;
use Src\Campaigns\Application\DTOs\DTOEnrollMembersRequest;
use Src\Campaigns\Application\DTOs\DTOUpdateCampaignRequest;
use Src\Campaigns\Application\UseCases\CancelCampaignUseCase;
use Src\Campaigns\Application\UseCases\CreateCampaignUseCase;
use Src\Campaigns\Application\UseCases\EnrollMembersUseCase;
use Src\Campaigns\Application\UseCases\GetAvailableMembersUseCase;
use Src\Campaigns\Application\UseCases\GetLastProcessExecutionUseCase;
use Src\Campaigns\Application\UseCases\GetMemberTransactionsUseCase;
use Src\Campaigns\Application\UseCases\ListCampaignMembersUseCase;
use Src\Campaigns\Application\UseCases\ListCampaignsUseCase;
use Src\Campaigns\Application\UseCases\RemoveCampaignMemberUseCase;
use Src\Campaigns\Application\UseCases\ShowCampaignUseCase;
use Src\Campaigns\Application\UseCases\UpdateCampaignUseCase;

class CampaignService
{
    public function __construct(
        private readonly CreateCampaignUseCase          $createUseCase,
        private readonly ListCampaignsUseCase           $listUseCase,
        private readonly ShowCampaignUseCase            $showUseCase,
        private readonly UpdateCampaignUseCase          $updateUseCase,
        private readonly CancelCampaignUseCase          $cancelUseCase,
        private readonly EnrollMembersUseCase           $enrollMembersUseCase,
        private readonly RemoveCampaignMemberUseCase    $removeMemberUseCase,
        private readonly ListCampaignMembersUseCase     $listMembersUseCase,
        private readonly GetAvailableMembersUseCase     $getAvailableMembersUseCase,
        private readonly GetMemberTransactionsUseCase   $getMemberTransactionsUseCase,
        private readonly GetLastProcessExecutionUseCase $getLastProcessExecutionUseCase,
    ) {}

    public function create(DTOCreateCampaignRequest $dto): DTOCampaignResponse
    {
        return $this->createUseCase->execute($dto);
    }

    /** @return DTOCampaignResponse[] */
    public function list(array $filters = []): array
    {
        return $this->listUseCase->execute($filters);
    }

    public function show(string $uuid): DTOCampaignResponse
    {
        return $this->showUseCase->execute($uuid);
    }

    public function update(DTOUpdateCampaignRequest $dto): DTOCampaignResponse
    {
        return $this->updateUseCase->execute($dto);
    }

    public function cancel(string $uuid, int $cancelledByOid): void
    {
        $this->cancelUseCase->execute($uuid, $cancelledByOid);
    }

    /** @return DTOCampaignMemberResponse[] */
    public function enrollMembers(DTOEnrollMembersRequest $dto): array
    {
        return $this->enrollMembersUseCase->execute($dto);
    }

    public function removeMember(string $memberUuid, int $updatedByOid): void
    {
        $this->removeMemberUseCase->execute($memberUuid, $updatedByOid);
    }

    public function listCampaignMembers(int $campaignOid): array
    {
        return $this->listMembersUseCase->execute($campaignOid);
    }

    public function availableMembers(int $campaignOid): array
    {
        return $this->getAvailableMembersUseCase->execute($campaignOid);
    }

    public function memberTransactions(int $campaignOid, int $memberOid): array
    {
        return $this->getMemberTransactionsUseCase->execute($campaignOid, $memberOid);
    }

    public function getLastProcessExecution(?string $campaignUuid = null): ?\stdClass
    {
        return $this->getLastProcessExecutionUseCase->execute($campaignUuid);
    }
}
