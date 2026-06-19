<?php

namespace Src\FundRaising\Application\DTOs;

use Src\FundRaising\Infrastructure\Http\Requests\CreateFundRaisingRequest;

class DTOCreateFundRaisingRequest
{
    public function __construct(
        public readonly string  $name,
        public readonly ?string $description,
        public readonly float   $targetAmount,
        public readonly string  $startDate,
        public readonly ?string $endDate,
        public readonly int     $createdByOid,
        public readonly array   $memberOids = [],  // int[] — optional initial members
    ) {}

    public static function fromRequest(CreateFundRaisingRequest $request, int $createdByOid): self
    {
        return new self(
            name:         trim($request->name),
            description:  $request->description ? trim($request->description) : null,
            targetAmount: (float) $request->target_amount,
            startDate:    $request->start_date,
            endDate:      $request->end_date,
            createdByOid: $createdByOid,
            memberOids:   array_map('intval', $request->member_oids ?? []),
        );
    }
}
