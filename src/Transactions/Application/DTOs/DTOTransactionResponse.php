<?php

namespace Src\Transactions\Application\DTOs;

use Src\Transactions\Domain\Entities\Transaction;

class DTOTransactionResponse
{
    public function __construct(
        public readonly ?int    $id,
        public readonly ?int    $oid,
        public readonly ?string $uuid,
        public readonly bool    $status,
        public readonly string  $type,
        public readonly ?int    $memberOid,
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

    public static function fromEntity(Transaction $e): self
    {
        return new self(
            id:              $e->id,
            oid:             $e->oid,
            uuid:            $e->uuid,
            status:          $e->status,
            type:            $e->type,
            memberOid:       $e->memberOid,
            amount:          $e->amount,
            description:     $e->description,
            reference:       $e->reference,
            transactionDate: $e->transactionDate,
            notes:           $e->notes,
            createdByOid:    $e->createdByOid,
            updatedByOid:    $e->updatedByOid,
            createdAt:       $e->createdAt,
            updatedAt:       $e->updatedAt,
        );
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'oid'              => $this->oid,
            'uuid'             => $this->uuid,
            'status'           => $this->status,
            'type'             => $this->type,
            'member_oid'       => $this->memberOid,
            'amount'           => $this->amount,
            'description'      => $this->description,
            'reference'        => $this->reference,
            'transaction_date' => $this->transactionDate,
            'notes'            => $this->notes,
            'created_by_oid'   => $this->createdByOid,
            'updated_by_oid'   => $this->updatedByOid,
            'created_at'       => $this->createdAt,
            'updated_at'       => $this->updatedAt,
        ];
    }
}
