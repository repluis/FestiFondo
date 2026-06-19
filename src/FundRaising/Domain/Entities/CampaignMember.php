<?php

namespace Src\FundRaising\Domain\Entities;

class CampaignMember
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?int    $oid,
        public readonly ?string $uuid,
        public readonly bool    $status,
        public readonly int     $campaignOid,
        public readonly int     $memberOid,
        public readonly string  $enrolledAt,
        public readonly ?int    $createdByOid,
        public readonly ?int    $updatedByOid,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}
}
