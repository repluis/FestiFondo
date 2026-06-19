<?php

namespace Src\Transactions\Application\DTOs;

use Src\Transactions\Infrastructure\Http\Requests\CreateTransactionRequest;

class DTOCreateTransactionRequest
{
    public function __construct(
        public readonly string  $type,
        public readonly ?int    $memberOid,
        public readonly float   $amount,
        public readonly string  $description,
        public readonly ?string $reference,
        public readonly string  $transactionDate,
        public readonly ?string $notes,
        public readonly int     $createdByOid,
    ) {}

    public static function fromRequest(CreateTransactionRequest $request, int $createdByOid): self
    {
        return new self(
            type:            $request->type,
            memberOid:       $request->member_oid ?? null,
            amount:          (float) $request->amount,
            description:     trim($request->description),
            reference:       $request->reference ? trim($request->reference) : null,
            transactionDate: $request->transaction_date,
            notes:           $request->notes ? trim($request->notes) : null,
            createdByOid:    $createdByOid,
        );
    }
}
