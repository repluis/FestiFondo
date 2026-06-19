<?php

namespace Src\Members\Application\DTOs;

use Src\Members\Domain\Entities\Members;

class DTOMembersResponse
{
    public function __construct(
        public readonly string  $uuid,
        public readonly string  $identification,
        public readonly string  $firstName,
        public readonly string  $lastName,
        public readonly string  $fullName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $notes,
        public readonly string  $joinedAt,
        public readonly bool    $status,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromEntity(Members $member): self
    {
        return new self(
            uuid:       $member->uuid ?? '',
            identification: $member->identification,
            firstName:  $member->firstName,
            lastName:   $member->lastName,
            fullName:   $member->firstName . ' ' . $member->lastName,
            email:      $member->email,
            phone:      $member->phone,
            address:    $member->address,
            notes:      $member->notes,
            joinedAt:   $member->joinedAt,
            status:     $member->status,
            createdAt:  $member->createdAt,
            updatedAt:  $member->updatedAt,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid'        => $this->uuid,
            'identification' => $this->identification,
            'first_name'  => $this->firstName,
            'last_name'   => $this->lastName,
            'full_name'   => $this->fullName,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'address'     => $this->address,
            'notes'       => $this->notes,
            'joined_at'   => $this->joinedAt,
            'status'      => $this->status,
            'created_at'  => $this->createdAt,
            'updated_at'  => $this->updatedAt,
        ];
    }
}
