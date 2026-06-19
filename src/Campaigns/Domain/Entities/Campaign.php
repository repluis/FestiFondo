<?php

namespace Src\Campaigns\Domain\Entities;

class Campaign
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?int    $oid,
        public readonly ?string $uuid,
        public readonly string  $name,
        public readonly ?string $description,
        public readonly float   $targetAmount,
        public readonly float   $collectedAmount,
        public readonly float   $monthlyFeeAmount,
        public readonly float   $dailyPenaltyRate,
        public readonly int     $dueDay,
        public readonly string  $startDate,
        public readonly ?string $endDate,
        public readonly string  $campaignStatus, // draft|active|completed|cancelled
        public readonly bool    $status,
        public readonly ?int    $createdByOid,
        public readonly ?int    $updatedByOid,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}
}
