<?php

namespace Src\Members\Domain\Entities;

class Members
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?int    $oid,
        public readonly ?string $uuid,
        public readonly string  $identification,
        public readonly string  $firstName,
        public readonly string  $lastName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $notes,
        public readonly string  $joinedAt,
        public readonly bool    $status,
        public readonly ?int    $createdByOid,
        public readonly ?int    $updatedByOid,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}
}
