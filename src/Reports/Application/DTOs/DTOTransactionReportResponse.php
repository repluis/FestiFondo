<?php

namespace Src\Reports\Application\DTOs;

use Src\Reports\Domain\Entities\TransactionReport;

class DTOTransactionReportResponse
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

    public static function fromEntity(TransactionReport $entity): self
    {
        return new self(
            oid:                      $entity->oid,
            uuid:                     $entity->uuid,
            status:                   $entity->status,
            type:                     $entity->type,
            amount:                   $entity->amount,
            description:              $entity->description,
            reference:                $entity->reference,
            transactionDate:          $entity->transactionDate,
            notes:                    $entity->notes,
            memberOid:                $entity->memberOid,
            memberName:               $entity->memberName,
            campaignOid:              $entity->campaignOid,
            campaignName:             $entity->campaignName,
            createdAt:                $entity->createdAt,
            appliedToPenalties:       $entity->appliedToPenalties,
            appliedToFees:            $entity->appliedToFees,
            previousPenaltiesBalance: $entity->previousPenaltiesBalance,
            newPenaltiesBalance:      $entity->newPenaltiesBalance,
            previousFeesBalance:      $entity->previousFeesBalance,
            newFeesBalance:           $entity->newFeesBalance,
        );
    }
}
