<?php

namespace Src\Transactions\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;
use Src\Transactions\Domain\Entities\Transaction;

class TransactionModel extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'transaction_type',
        'member_oid',
        'campaign_oid',
        'amount',
        'description',
        'reference',
        'transaction_date',
        'notes',
        'status',
        'created_by_oid',
        'updated_by_oid',
        'previous_penalties_balance',
        'new_penalties_balance',
        'previous_fees_balance',
        'new_fees_balance',
        'applied_to_penalties',
        'applied_to_fees',
    ];

    protected $casts = [
        'status' => 'boolean',
        'amount' => 'float',
    ];

    public function toEntity(): Transaction
    {
        return new Transaction(
            id:              $this->id,
            oid:             $this->oid,
            uuid:            $this->uuid,
            status:          (bool) $this->status,
            type:            $this->transaction_type,
            memberOid:       $this->member_oid,
            campaignOid:     $this->campaign_oid,
            amount:          (float) $this->amount,
            description:     $this->description,
            reference:       $this->reference,
            transactionDate: $this->transaction_date,
            notes:           $this->notes,
            createdByOid:    $this->created_by_oid,
            updatedByOid:    $this->updated_by_oid,
            createdAt:       $this->created_at?->toDateTimeString(),
            updatedAt:       $this->updated_at?->toDateTimeString(),
        );
    }
}
