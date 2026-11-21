<?php

namespace Src\Dashboard\Application\Services;

use Src\Dashboard\Application\UseCases\GetCampaignsProgressUseCase;
use Src\Dashboard\Application\UseCases\GetCampaignTransparencyUseCase;
use Src\Dashboard\Application\UseCases\GetCollectionStatsUseCase;
use Src\Dashboard\Application\UseCases\GetMemberStandingsUseCase;
use Src\Dashboard\Application\UseCases\GetRecentPaymentsUseCase;
use Src\Dashboard\Domain\Entities\MemberStanding;

/**
 * Datos para las dos vistas privadas:
 *   - Inicio:    resumen rápido (campañas, indicadores, top 5, últimos pagos)
 *   - Dashboard: análisis completo (gráficas y tablas de todos los miembros)
 */
class DashboardService
{
    public function __construct(
        private GetMemberStandingsUseCase   $standings,
        private GetCollectionStatsUseCase   $collection,
        private GetCampaignsProgressUseCase $campaigns,
        private GetRecentPaymentsUseCase    $recentPayments,
        private GetCampaignTransparencyUseCase $transparency,
    ) {}

    public function transparency(string $campaignUuid): ?array
    {
        return $this->transparency->execute($campaignUuid);
    }

    public function home(): array
    {
        $standings  = $this->standings->execute();
        $collection = $this->collection->execute();

        $campaigns = $this->campaigns->execute();

        // Datos de transparencia por campaña, para el botón "Copiar texto" (WhatsApp)
        $transparency = [];
        foreach ($campaigns as $c) {
            $transparency[$c->uuid] = $this->transparency->execute($c->uuid);
        }

        return [
            'kpis'            => $this->kpis($standings, $collection),
            'campaigns'       => $campaigns,
            'transparency'    => $transparency,
            'topPayers'       => array_slice($standings['top_payers'], 0, 5),
            'topDebtors'      => array_slice($standings['top_debtors'], 0, 5),
            'recentPayments'  => $this->recentPayments->execute(8),
        ];
    }

    public function dashboard(): array
    {
        $standings  = $this->standings->execute();
        $collection = $this->collection->execute();

        return [
            'kpis'            => $this->kpis($standings, $collection),
            'collection'      => $collection,
            'standings'       => $standings,
            'campaigns'       => $this->campaigns->execute(),
        ];
    }

    private function kpis(array $standings, array $collection): array
    {
        $all = $standings['all'];

        return [
            'collected'       => $collection['total'],
            'payments_count'  => $collection['payments_count'],
            'average_payment' => $collection['average_payment'],
            'members'         => count($all),
            'up_to_date'      => count($standings['up_to_date']),
            'with_debt'       => count($standings['with_debt']),
            'fees_due'        => round(array_sum(array_map(fn (MemberStanding $m) => $m->feesDue, $all)), 2),
            'penalties_due'   => round(array_sum(array_map(fn (MemberStanding $m) => $m->penaltiesDue, $all)), 2),
            'total_due'       => round(array_sum(array_map(fn (MemberStanding $m) => $m->totalDue(), $all)), 2),
            'months_due'      => array_sum(array_map(fn (MemberStanding $m) => $m->monthsDue, $all)),
        ];
    }
}
