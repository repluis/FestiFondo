<?php

namespace Src\Transactions\Application\DTOs;

use Src\Transactions\Infrastructure\Http\Requests\ApplyPaymentRequest;

class DTOApplyPaymentRequest
{
    public function __construct(
        public readonly int     $memberOid,
        public readonly ?int    $campaignOid,
        public readonly float   $amount,
        public readonly string  $transactionDate,
        public readonly ?string $notes,
        public readonly int     $createdByOid,
    ) {}

    public static function fromRequest(ApplyPaymentRequest $request, int $createdByOid): self
    {
        return new self(
            memberOid:       (int) $request->member_oid,
            campaignOid:     $request->campaign_oid ? (int) $request->campaign_oid : null,
            amount:          (float) $request->amount,
            transactionDate: $request->transaction_date,
            notes:           $request->notes ? trim($request->notes) : null,
            createdByOid:    $createdByOid,
        );
    }
}
