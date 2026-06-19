<?php

namespace Src\Campaigns\Application\DTOs;

use Src\Campaigns\Infrastructure\Http\Requests\CreateCampaignRequest;

class DTOCreateCampaignRequest
{
    public function __construct(
        public readonly string  $name,
        public readonly ?string $description,
        public readonly float   $targetAmount,
        public readonly float   $monthlyFeeAmount,
        public readonly float   $dailyPenaltyRate,
        public readonly int     $dueDay,
        public readonly string  $startDate,
        public readonly ?string $endDate,
        public readonly int     $createdByOid,
        public readonly array   $memberOids = [],
    ) {}

    public static function fromRequest(CreateCampaignRequest $request, int $createdByOid): self
    {
        return new self(
            name:             trim($request->name),
            description:      $request->description ? trim($request->description) : null,
            targetAmount:     (float) $request->target_amount,
            monthlyFeeAmount: (float) $request->monthly_fee_amount,
            dailyPenaltyRate: (float) $request->daily_penalty_rate,
            dueDay:           (int)   $request->due_day,
            startDate:        $request->start_date,
            endDate:          $request->end_date,
            createdByOid:     $createdByOid,
            memberOids:       array_map('intval', $request->member_oids ?? []),
        );
    }
}
