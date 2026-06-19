<?php

namespace Src\Members\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Members\Domain\Exceptions\MemberAlreadyInactiveException;
use Src\Members\Domain\Exceptions\MemberNotFoundException;
use Src\Members\Domain\Repositories\MembersRepositoryInterface;

class CancelMembersUseCase
{
    public function __construct(
        private readonly MembersRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid, int $cancelledByOid): void
    {
        Log::info('[CancelMembersUseCase] Starting', ['uuid' => $uuid]);

        DB::transaction(function () use ($uuid, $cancelledByOid): void {

            Log::info('[CancelMembersUseCase] Step 1 — Finding member');
            $member = $this->repository->findByUuid($uuid);

            if ($member === null) {
                throw MemberNotFoundException::withUuid($uuid);
            }

            Log::info('[CancelMembersUseCase] Step 2 — Checking current status');
            if ($member->status === false) {
                throw MemberAlreadyInactiveException::withUuid($uuid);
            }

            Log::info('[CancelMembersUseCase] Step 3 — Deactivating member');
            $this->repository->deactivate($member->oid, $cancelledByOid);

            Log::info('[CancelMembersUseCase] Completed', ['identification' => $member->identification]);
        });
    }
}
