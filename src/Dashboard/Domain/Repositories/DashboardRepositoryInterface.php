<?php

namespace Src\Dashboard\Domain\Repositories;

use Src\Dashboard\Domain\Entities\CampaignProgress;
use Src\Dashboard\Domain\Entities\MemberStanding;

interface DashboardRepositoryInterface
{
    /** @return MemberStanding[] Miembros activos con lo pagado y lo adeudado */
    public function getMemberStandings(): array;

    /** @return CampaignProgress[] Campañas activas (status = true) */
    public function getCampaignsProgress(): array;

    /**
     * Pagos válidos (ingresos no anulados) para estadísticas.
     *
     * @return array<int, array{date: string, amount: float, to_fees: float, to_penalties: float}>
     */
    public function getValidPayments(): array;

    /**
     * Últimos pagos registrados.
     *
     * @return array<int, array{date: string, member: string, amount: float, to_fees: float, to_penalties: float, campaign: ?string}>
     */
    public function getRecentPayments(int $limit): array;
}
