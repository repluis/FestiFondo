<?php

namespace Src\Members\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Members\Application\DTOs\DTOMembersResponse;
use Src\Members\Application\DTOs\DTOUpdateMembersRequest;
use Src\Members\Domain\Exceptions\MemberEmailAlreadyExistsException;
use Src\Members\Domain\Exceptions\MemberNotFoundException;
use Src\Members\Domain\Repositories\MembersRepositoryInterface;

class UpdateMembersUseCase
{
    public function __construct(
        private readonly MembersRepositoryInterface $repository,
    ) {}

    public function execute(DTOUpdateMembersRequest $dto): DTOMembersResponse
    {
        Log::info('[UpdateMembersUseCase] Starting', ['uuid' => $dto->memberUuid]);

        return DB::transaction(function () use ($dto): DTOMembersResponse {

            Log::info('[UpdateMembersUseCase] Step 1 — Finding member');
            $member = $this->repository->findByUuid($dto->memberUuid);

            if ($member === null) {
                throw MemberNotFoundException::withUuid($dto->memberUuid);
            }

            if ($dto->email !== null) {
                Log::info('[UpdateMembersUseCase] Step 2 — Checking email uniqueness');
                if ($this->repository->existsByEmail($dto->email, $member->oid)) {
                    throw MemberEmailAlreadyExistsException::withEmail($dto->email);
                }
            }

            Log::info('[UpdateMembersUseCase] Step 3 — Persisting changes');
            $updated = $this->repository->update($member->oid, [
                'first_name'     => $dto->firstName,
                'last_name'      => $dto->lastName,
                'email'          => $dto->email,
                'phone'          => $dto->phone,
                'address'        => $dto->address,
                'notes'          => $dto->notes,
                'joined_at'      => $dto->joinedAt,
                'updated_by_oid' => $dto->updatedByOid,
            ]);

            Log::info('[UpdateMembersUseCase] Completed', ['member_uuid' => $updated->uuid]);

            return DTOMembersResponse::fromEntity($updated);
        });
    }
}
