<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\FundRaising\Application\DTOs\DTOFundRaisingResponse;
use Src\FundRaising\Domain\Exceptions\FundRaisingNotFoundException;
use Src\FundRaising\Domain\Repositories\FundRaisingRepositoryInterface;

class ShowFundRaisingUseCase
{
    public function __construct(
        private readonly FundRaisingRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid): DTOFundRaisingResponse
    {
        Log::info('[ShowFundRaisingUseCase] Starting', ['uuid' => $uuid]);

        Log::info('[ShowFundRaisingUseCase] Step 1 — Finding campaign by UUID');
        $entity = $this->repository->findByUuid($uuid);

        if ($entity === null) {
            throw FundRaisingNotFoundException::withUuid($uuid);
        }

        Log::info('[ShowFundRaisingUseCase] Completed', ['name' => $entity->name]);

        return DTOFundRaisingResponse::fromEntity($entity);
    }
}
