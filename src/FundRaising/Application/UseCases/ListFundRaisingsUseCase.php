<?php

namespace Src\FundRaising\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\FundRaising\Application\DTOs\DTOFundRaisingResponse;
use Src\FundRaising\Domain\Repositories\FundRaisingRepositoryInterface;

class ListFundRaisingsUseCase
{
    public function __construct(
        private readonly FundRaisingRepositoryInterface $repository,
    ) {}

    /** @return DTOFundRaisingResponse[] */
    public function execute(array $filters = []): array
    {
        Log::info('[ListFundRaisingsUseCase] Starting', ['filters' => $filters]);

        Log::info('[ListFundRaisingsUseCase] Step 1 — Querying fund raising campaigns');
        $items = $this->repository->listAll($filters);

        Log::info('[ListFundRaisingsUseCase] Completed', ['count' => count($items)]);

        return array_map(
            static fn($entity) => DTOFundRaisingResponse::fromEntity($entity),
            $items,
        );
    }
}
