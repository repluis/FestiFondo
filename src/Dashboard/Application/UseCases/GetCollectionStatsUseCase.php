<?php

namespace Src\Dashboard\Application\UseCases;

use Carbon\Carbon;
use Src\Dashboard\Domain\Repositories\DashboardRepositoryInterface;

/**
 * Estadísticas de recaudación: total, por mes y a qué se aplicó lo pagado.
 */
class GetCollectionStatsUseCase
{
    private const MONTHS = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    public function __construct(private DashboardRepositoryInterface $repository) {}

    /**
     * @return array{
     *   total: float, payments_count: int, average_payment: float,
     *   to_fees: float, to_penalties: float, unallocated: float,
     *   monthly: array<int, array{key: string, label: string, total: float, to_fees: float, to_penalties: float, count: int}>,
     *   best_month: ?array
     * }
     */
    public function execute(int $months = 12): array
    {
        $payments = $this->repository->getValidPayments();

        // Últimos N meses hasta el mes actual, aunque no tengan pagos
        $monthly = [];
        $cursor  = Carbon::today()->startOfMonth()->subMonths($months - 1);
        for ($i = 0; $i < $months; $i++) {
            $key = $cursor->format('Y-m');
            $monthly[$key] = [
                'key'          => $key,
                'label'        => self::MONTHS[$cursor->month - 1] . ' ' . $cursor->format('y'),
                'total'        => 0.0,
                'to_fees'      => 0.0,
                'to_penalties' => 0.0,
                'count'        => 0,
            ];
            $cursor->addMonth();
        }

        $total = $toFees = $toPenalties = 0.0;
        foreach ($payments as $p) {
            $total       += $p['amount'];
            $toFees      += $p['to_fees'];
            $toPenalties += $p['to_penalties'];

            $key = substr($p['date'], 0, 7);
            if (isset($monthly[$key])) {
                $monthly[$key]['total']        += $p['amount'];
                $monthly[$key]['to_fees']      += $p['to_fees'];
                $monthly[$key]['to_penalties'] += $p['to_penalties'];
                $monthly[$key]['count']++;
            }
        }

        $monthly = array_values(array_map(fn ($m) => array_merge($m, [
            'total'        => round($m['total'], 2),
            'to_fees'      => round($m['to_fees'], 2),
            'to_penalties' => round($m['to_penalties'], 2),
        ]), $monthly));

        $best = null;
        foreach ($monthly as $m) {
            if ($m['total'] > 0 && ($best === null || $m['total'] > $best['total'])) {
                $best = $m;
            }
        }

        $count = count($payments);

        return [
            'total'           => round($total, 2),
            'payments_count'  => $count,
            'average_payment' => $count ? round($total / $count, 2) : 0.0,
            'to_fees'         => round($toFees, 2),
            'to_penalties'    => round($toPenalties, 2),
            // Pagos registrados antes de que existiera el reparto automático, o saldo a favor
            'unallocated'     => max(0.0, round($total - $toFees - $toPenalties, 2)),
            'monthly'         => $monthly,
            'best_month'      => $best,
        ];
    }
}
