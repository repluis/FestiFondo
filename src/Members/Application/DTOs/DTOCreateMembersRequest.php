<?php

namespace Src\Members\Application\DTOs;

use Src\Members\Infrastructure\Http\Requests\CreateMembersRequest;

class DTOCreateMembersRequest
{
    public function __construct(
        public readonly string  $identification,
        public readonly string  $firstName,
        public readonly string  $lastName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $notes,
        public readonly string  $joinedAt,
        public readonly int     $createdByOid,
    ) {}

    public static function fromRequest(CreateMembersRequest $request, int $createdByOid): self
    {
        return new self(
            identification: trim($request->identification),
            firstName:    trim($request->first_name),
            lastName:     trim($request->last_name),
            email:        $request->email ? strtolower(trim($request->email)) : null,
            phone:        $request->phone ? trim($request->phone) : null,
            address:      $request->address,
            notes:        $request->notes,
            joinedAt:     $request->joined_at,
            createdByOid: $createdByOid,
        );
    }
}
