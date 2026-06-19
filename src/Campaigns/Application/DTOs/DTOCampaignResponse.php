<?php

namespace Src\Campaigns\Application\DTOs;

use Src\Campaigns\Domain\Entities\Campaign;

class DTOCampaignResponse
{
    public function __construct(
        public readonly ?int    $oid,
        public readonly string  $uuid,
        public readonly string  $name,
        public readonly ?string $description,
        public readonly float   $targetAmount,
        public readonly float   $collectedAmount,
        public readonly float   $monthlyFeeAmount,
        public readonly float   $dailyPenaltyRate,
        public readonly int     $dueDay,
        public readonly float   $pendingAmount,
        public readonly float   $progressPercent,
        public readonly string  $startDate,
        public readonly ?string $endDate,
        public readonly string  $campaignStatus,
        public readonly bool    $status,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromEntity(Campaign $entity): self
    {
        $pending  = max(0, $entity->targetAmount - $entity->collectedAmount);
        $progress = $entity->targetAmount > 0
            ? round(($entity->collectedAmount / $entity->targetAmount) * 100, 2)
            : 0.0;

        return new self(
            oid:              $entity->oid,
            uuid:             $entity->uuid ?? '',
            name:             $entity->name,
            description:      $entity->description,
            targetAmount:     $entity->targetAmount,
            collectedAmount:  $entity->collectedAmount,
            monthlyFeeAmount: $entity->monthlyFeeAmount,
            dailyPenaltyRate: $entity->dailyPenaltyRate,
            dueDay:           $entity->dueDay,
            pendingAmount:    $pending,
            progressPercent:  $progress,
            startDate:        $entity->startDate,
            endDate:          $entity->endDate,
            campaignStatus:   $entity->campaignStatus,
            status:           $entity->status,
            createdAt:        $entity->createdAt,
            updatedAt:        $entity->updatedAt,
        );
    }

    public function toArray(): array
    {
        return [
            'oid'               => $this->oid,
            'uuid'              => $this->uuid,
            'name'              => $this->name,
            'description'       => $this->description,
            'target_amount'     => $this->targetAmount,
            'collected_amount'  => $this->collectedAmount,
            'monthly_fee_amount'=> $this->monthlyFeeAmount,
            'daily_penalty_rate'=> $this->dailyPenaltyRate,
            'due_day'           => $this->dueDay,
            'pending_amount'    => $this->pendingAmount,
            'progress_percent'  => $this->progressPercent,
            'start_date'        => $this->startDate,
            'end_date'          => $this->endDate,
            'campaign_status'   => $this->campaignStatus,
            'status'            => $this->status,
            'created_at'        => $this->createdAt,
            'updated_at'        => $this->updatedAt,
        ];
    }
}
