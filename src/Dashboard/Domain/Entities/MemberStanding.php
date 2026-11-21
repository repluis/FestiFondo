<?php

namespace Src\Dashboard\Domain\Entities;

/**
 * Situación de pago de un miembro: cuánto ha pagado y cuánto debe.
 */
class MemberStanding
{
    public function __construct(
        public readonly int     $memberOid,
        public readonly string  $name,
        public readonly float   $totalPaid,
        public readonly int     $paymentsCount,
        public readonly ?string $lastPaymentDate,
        public readonly float   $feesDue,
        public readonly int     $monthsDue,
        public readonly float   $penaltiesDue,
    ) {}

    public function totalDue(): float
    {
        return round($this->feesDue + $this->penaltiesDue, 2);
    }

    public function isUpToDate(): bool
    {
        return $this->totalDue() <= 0.0;
    }
}
