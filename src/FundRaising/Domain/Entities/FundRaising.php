<?php

namespace Src\FundRaising\Domain\Entities;

class FundRaising
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?int    $oid,
        public readonly ?string $uuid,
        public readonly string  $name,
        public readonly ?string $description,
        public readonly float   $targetAmount,
        public readonly float   $collectedAmount,
        public readonly string  $startDate,
        public readonly ?string $endDate,
        public readonly string  $fundRaisingStatus, // draft|active|completed|cancelled
        public readonly bool    $status,
        public readonly ?int    $createdByOid,
        public readonly ?int    $updatedByOid,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}
}
