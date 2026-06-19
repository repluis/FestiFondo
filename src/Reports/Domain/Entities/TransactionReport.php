<?php

namespace Src\Reports\Domain\Entities;

class TransactionReport
{
    public function __construct(
        public readonly int     $oid,
        public readonly string  $uuid,
        public readonly bool    $status,
        public readonly string  $type,
        public readonly float   $amount,
        public readonly string  $description,
        public readonly ?string $reference,
        public readonly string  $transactionDate,
        public readonly ?string $notes,
        public readonly ?int    $memberOid,
        public readonly ?string $memberName,
        public readonly ?int    $campaignOid,
        public readonly ?string $campaignName,
        public readonly ?string $createdAt,
        public readonly ?float  $appliedToPenalties,
        public readonly ?float  $appliedToFees,
        public readonly ?float  $previousPenaltiesBalance,
        public readonly ?float  $newPenaltiesBalance,
        public readonly ?float  $previousFeesBalance,
        public readonly ?float  $newFeesBalance,
    ) {}
}
