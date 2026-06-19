<?php

namespace Src\FundRaising\Application\DTOs;

use Src\FundRaising\Infrastructure\Http\Requests\UpdateFundRaisingRequest;

class DTOUpdateFundRaisingRequest
{
    public function __construct(
        public readonly string  $fundRaisingUuid,
        public readonly string  $name,
        public readonly ?string $description,
        public readonly float   $targetAmount,
        public readonly string  $startDate,
        public readonly ?string $endDate,
        public readonly string  $fundRaisingStatus,
        public readonly int     $updatedByOid,
    ) {}

    public static function fromRequest(UpdateFundRaisingRequest $request, string $uuid, int $updatedByOid): self
    {
        return new self(
            fundRaisingUuid:    $uuid,
            name:               trim($request->name),
            description:        $request->description ? trim($request->description) : null,
            targetAmount:       (float) $request->target_amount,
            startDate:          $request->start_date,
            endDate:            $request->end_date,
            fundRaisingStatus:  $request->fund_raising_status,
            updatedByOid:       $updatedByOid,
        );
    }
}
