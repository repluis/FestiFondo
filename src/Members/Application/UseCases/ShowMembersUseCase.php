<?php

namespace Src\Members\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Members\Application\DTOs\DTOMembersResponse;
use Src\Members\Domain\Exceptions\MemberNotFoundException;
use Src\Members\Domain\Repositories\MembersRepositoryInterface;

class ShowMembersUseCase
{
    public function __construct(
        private readonly MembersRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid): DTOMembersResponse
    {
        Log::info('[ShowMembersUseCase] Starting', ['uuid' => $uuid]);

        Log::info('[ShowMembersUseCase] Step 1 — Finding member by UUID');
        $member = $this->repository->findByUuid($uuid);

        if ($member === null) {
            throw MemberNotFoundException::withUuid($uuid);
        }

        Log::info('[ShowMembersUseCase] Completed', ['identification' => $member->identification]);

        return DTOMembersResponse::fromEntity($member);
    }
}
