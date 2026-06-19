<?php

namespace Src\Members\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Members\Application\DTOs\DTOCreateMembersRequest;
use Src\Members\Application\DTOs\DTOMembersResponse;
use Src\Members\Domain\Exceptions\MemberIdentificationAlreadyExistsException;
use Src\Members\Domain\Exceptions\MemberEmailAlreadyExistsException;
use Src\Members\Domain\Repositories\MembersRepositoryInterface;

class CreateMembersUseCase
{
    public function __construct(
        private readonly MembersRepositoryInterface $repository,
    ) {}

    public function execute(DTOCreateMembersRequest $dto): DTOMembersResponse
    {
        Log::info('[CreateMembersUseCase] Starting', [
            'identification' => $dto->identification,
            'email'          => $dto->email,
        ]);

        return DB::transaction(function () use ($dto): DTOMembersResponse {

            Log::info('[CreateMembersUseCase] Step 1 — Checking identification uniqueness');
            if ($this->repository->existsByIdentification($dto->identification)) {
                throw MemberIdentificationAlreadyExistsException::withIdentification($dto->identification);
            }

            if ($dto->email !== null) {
                Log::info('[CreateMembersUseCase] Step 2 — Checking email uniqueness');
                if ($this->repository->existsByEmail($dto->email)) {
                    throw MemberEmailAlreadyExistsException::withEmail($dto->email);
                }
            }

            Log::info('[CreateMembersUseCase] Step 3 — Persisting member');
            $member = $this->repository->create([
                'identification'  => $dto->identification,
                'first_name'     => $dto->firstName,
                'last_name'      => $dto->lastName,
                'email'          => $dto->email,
                'phone'          => $dto->phone,
                'address'        => $dto->address,
                'notes'          => $dto->notes,
                'joined_at'      => $dto->joinedAt,
                'status'         => true,
                'created_by_oid' => $dto->createdByOid,
                'updated_by_oid' => $dto->createdByOid,
            ]);

            Log::info('[CreateMembersUseCase] Completed', [
                'member_uuid' => $member->uuid,
            ]);

            return DTOMembersResponse::fromEntity($member);
        });
    }
}
