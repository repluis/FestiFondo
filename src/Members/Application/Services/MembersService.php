<?php

namespace Src\Members\Application\Services;

use Src\Members\Application\DTOs\DTOCreateMembersRequest;
use Src\Members\Application\DTOs\DTOMembersResponse;
use Src\Members\Application\DTOs\DTOUpdateMembersRequest;
use Src\Members\Application\UseCases\ActivateMembersUseCase;
use Src\Members\Application\UseCases\CancelMembersUseCase;
use Src\Members\Application\UseCases\CreateMembersUseCase;
use Src\Members\Application\UseCases\ListMembersUseCase;
use Src\Members\Application\UseCases\ShowMembersUseCase;
use Src\Members\Application\UseCases\UpdateMembersUseCase;

class MembersService
{
    public function __construct(
        private readonly CreateMembersUseCase   $createUseCase,
        private readonly ListMembersUseCase     $listUseCase,
        private readonly ShowMembersUseCase     $showUseCase,
        private readonly UpdateMembersUseCase   $updateUseCase,
        private readonly CancelMembersUseCase   $cancelUseCase,
        private readonly ActivateMembersUseCase $activateUseCase,
    ) {}

    public function create(DTOCreateMembersRequest $dto): DTOMembersResponse
    {
        return $this->createUseCase->execute($dto);
    }

    /** @return DTOMembersResponse[] */
    public function list(array $filters = []): array
    {
        return $this->listUseCase->execute($filters);
    }

    public function show(string $uuid): DTOMembersResponse
    {
        return $this->showUseCase->execute($uuid);
    }

    public function update(DTOUpdateMembersRequest $dto): DTOMembersResponse
    {
        return $this->updateUseCase->execute($dto);
    }

    public function cancel(string $uuid, int $cancelledByOid): void
    {
        $this->cancelUseCase->execute($uuid, $cancelledByOid);
    }

    public function activate(string $uuid, int $activatedByOid): void
    {
        $this->activateUseCase->execute($uuid, $activatedByOid);
    }
}
