<?php

namespace Src\Campaigns\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;
use Src\Campaigns\Domain\Entities\Campaign;

class CampaignModel extends Model
{
    protected $table = 'fund_raisings';

    protected $fillable = [
        'name',
        'description',
        'target_amount',
        'collected_amount',
        'monthly_fee_amount',
        'daily_penalty_rate',
        'due_day',
        'start_date',
        'end_date',
        'fund_raising_status',
        'status',
        'created_by_oid',
        'updated_by_oid',
    ];

    protected function casts(): array
    {
        return [
            'target_amount'    => 'float',
            'collected_amount' => 'float',
            'start_date'       => 'date:Y-m-d',
            'end_date'         => 'date:Y-m-d',
            'status'           => 'boolean',
        ];
    }

    public function toEntity(): Campaign
    {
        return new Campaign(
            id:               $this->id,
            oid:              $this->oid,
            uuid:             $this->uuid,
            name:             $this->name,
            description:      $this->description,
            targetAmount:     (float) $this->target_amount,
            collectedAmount:  (float) $this->collected_amount,
            monthlyFeeAmount: (float) ($this->monthly_fee_amount ?? 1.00),
            dailyPenaltyRate: (float) ($this->daily_penalty_rate ?? 0.05),
            dueDay:           (int)   ($this->due_day ?? 15),
            startDate:        $this->start_date?->format('Y-m-d') ?? '',
            endDate:          $this->end_date?->format('Y-m-d'),
            campaignStatus:   $this->fund_raising_status,
            status:           (bool) $this->status,
            createdByOid:     $this->created_by_oid,
            updatedByOid:     $this->updated_by_oid,
            createdAt:        $this->created_at?->toISOString(),
            updatedAt:        $this->updated_at?->toISOString(),
        );
    }
}
