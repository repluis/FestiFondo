<?php

namespace Src\Campaigns\Application\DTOs;

use Src\Campaigns\Infrastructure\Http\Requests\UpdateCampaignRequest;

class DTOUpdateCampaignRequest
{
    public function __construct(
        public readonly string  $campaignUuid,
        public readonly string  $name,
        public readonly ?string $description,
        public readonly float   $targetAmount,
        public readonly float   $monthlyFeeAmount,
        public readonly float   $dailyPenaltyRate,
        public readonly int     $dueDay,
        public readonly string  $startDate,
        public readonly ?string $endDate,
        public readonly string  $campaignStatus,
        public readonly int     $updatedByOid,
    ) {}

    public static function fromRequest(UpdateCampaignRequest $request, string $uuid, int $updatedByOid): self
    {
        return new self(
            campaignUuid:     $uuid,
            name:             trim($request->name),
            description:      $request->description ? trim($request->description) : null,
            targetAmount:     (float) $request->target_amount,
            monthlyFeeAmount: (float) $request->monthly_fee_amount,
            dailyPenaltyRate: (float) $request->daily_penalty_rate,
            dueDay:           (int)   $request->due_day,
            startDate:        $request->start_date,
            endDate:          $request->end_date,
            campaignStatus:   $request->campaign_status,
            updatedByOid:     $updatedByOid,
        );
    }
}
