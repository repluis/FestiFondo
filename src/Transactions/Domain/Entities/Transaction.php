<?php

namespace Src\Transactions\Domain\Entities;

class Transaction
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?int    $oid,
        public readonly ?string $uuid,
        public readonly bool    $status,
        public readonly string  $type,            // 'income' | 'expense'
        public readonly ?int    $memberOid,
        public readonly ?int    $campaignOid,
        public readonly float   $amount,
        public readonly string  $description,
        public readonly ?string $reference,
        public readonly string  $transactionDate,
        public readonly ?string $notes,
        public readonly ?int    $createdByOid,
        public readonly ?int    $updatedByOid,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}
}
