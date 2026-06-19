<?php

namespace Src\Campaigns\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class ListCampaignsUseCase
{
    public function __construct(
        private readonly CampaignRepositoryInterface $repository,
    ) {}

    /** @return DTOCampaignResponse[] */
    public function execute(array $filters = []): array
    {
        Log::info('[ListCampaignsUseCase] Starting', ['filters' => $filters]);

        Log::info('[ListCampaignsUseCase] Step 1 — Querying campaigns');
        $items = $this->repository->listAll($filters);

        Log::info('[ListCampaignsUseCase] Completed', ['count' => count($items)]);

        return array_map(
            static fn($entity) => DTOCampaignResponse::fromEntity($entity),
            $items,
        );
    }
}
