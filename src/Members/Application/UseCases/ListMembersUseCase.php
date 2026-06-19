<?php

namespace Src\Members\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Members\Application\DTOs\DTOMembersResponse;
use Src\Members\Domain\Repositories\MembersRepositoryInterface;

class ListMembersUseCase
{
    public function __construct(
        private readonly MembersRepositoryInterface $repository,
    ) {}

    /** @return DTOMembersResponse[] */
    public function execute(array $filters = []): array
    {
        Log::info('[ListMembersUseCase] Starting', ['filters' => $filters]);

        Log::info('[ListMembersUseCase] Step 1 — Querying members');
        $members = $this->repository->listAll($filters);

        Log::info('[ListMembersUseCase] Completed', ['count' => count($members)]);

        return array_map(
            static fn($member) => DTOMembersResponse::fromEntity($member),
            $members,
        );
    }
}
