<?php

namespace Src\Dashboard\Domain\Entities;

/**
 * Avance de una campaña de recaudación frente a su meta.
 */
class CampaignProgress
{
    public function __construct(
        public readonly string  $uuid,
        public readonly string  $name,
        public readonly string  $statusLabel,
        public readonly float   $targetAmount,
        public readonly float   $collectedAmount,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly int     $membersCount,
        public readonly float   $monthlyFee,
    ) {}

    public function percent(): float
    {
        return $this->targetAmount > 0
            ? min(100.0, round($this->collectedAmount / $this->targetAmount * 100, 1))
            : 0.0;
    }

    public function remaining(): float
    {
        return max(0.0, round($this->targetAmount - $this->collectedAmount, 2));
    }
}
