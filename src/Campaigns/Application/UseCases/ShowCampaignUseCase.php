<?php

namespace Src\Campaigns\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class ShowCampaignUseCase
{
    public function __construct(
        private readonly CampaignRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid): DTOCampaignResponse
    {
        Log::info('[ShowCampaignUseCase] Starting', ['uuid' => $uuid]);

        Log::info('[ShowCampaignUseCase] Step 1 — Finding campaign by UUID');
        $entity = $this->repository->findByUuid($uuid);

        if ($entity === null) {
            throw CampaignNotFoundException::withUuid($uuid);
        }

        Log::info('[ShowCampaignUseCase] Completed', ['name' => $entity->name]);

        return DTOCampaignResponse::fromEntity($entity);
    }
}
