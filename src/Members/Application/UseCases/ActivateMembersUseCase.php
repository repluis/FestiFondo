<?php

namespace Src\Members\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Members\Domain\Exceptions\MemberAlreadyActiveException;
use Src\Members\Domain\Exceptions\MemberNotFoundException;
use Src\Members\Domain\Repositories\MembersRepositoryInterface;

class ActivateMembersUseCase
{
    public function __construct(
        private readonly MembersRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid, int $activatedByOid): void
    {
        Log::info('[ActivateMembersUseCase] Starting', ['uuid' => $uuid]);

        DB::transaction(function () use ($uuid, $activatedByOid): void {

            Log::info('[ActivateMembersUseCase] Step 1 — Finding member');
            $member = $this->repository->findByUuid($uuid);

            if ($member === null) {
                throw MemberNotFoundException::withUuid($uuid);
            }

            Log::info('[ActivateMembersUseCase] Step 2 — Checking current status');
            if ($member->status === true) {
                throw MemberAlreadyActiveException::withUuid($uuid);
            }

            Log::info('[ActivateMembersUseCase] Step 3 — Activating member');
            $this->repository->activate($member->oid, $activatedByOid);

            Log::info('[ActivateMembersUseCase] Completed', ['identification' => $member->identification]);
        });
    }
}
