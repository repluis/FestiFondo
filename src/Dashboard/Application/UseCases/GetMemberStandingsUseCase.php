<?php

namespace Src\Dashboard\Application\UseCases;

use Src\Dashboard\Domain\Entities\MemberStanding;
use Src\Dashboard\Domain\Repositories\DashboardRepositoryInterface;

/**
 * Rankings de miembros: quién pagó más/menos y quién debe más/menos.
 */
class GetMemberStandingsUseCase
{
    public function __construct(private DashboardRepositoryInterface $repository) {}

    /**
     * @return array{
     *   all: MemberStanding[],
     *   top_payers: MemberStanding[], bottom_payers: MemberStanding[],
     *   top_debtors: MemberStanding[], smallest_debtors: MemberStanding[],
     *   up_to_date: MemberStanding[], with_debt: MemberStanding[]
     * }
     */
    public function execute(): array
    {
        $all = $this->repository->getMemberStandings();

        $byPaidDesc = $all;
        usort($byPaidDesc, fn (MemberStanding $a, MemberStanding $b) => [$b->totalPaid, $a->name] <=> [$a->totalPaid, $b->name]);

        $byPaidAsc = $all;
        usort($byPaidAsc, fn (MemberStanding $a, MemberStanding $b) => [$a->totalPaid, $a->name] <=> [$b->totalPaid, $b->name]);

        $withDebt = array_values(array_filter($all, fn (MemberStanding $m) => !$m->isUpToDate()));
        $upToDate = array_values(array_filter($all, fn (MemberStanding $m) => $m->isUpToDate()));

        $debtDesc = $withDebt;
        usort($debtDesc, fn (MemberStanding $a, MemberStanding $b) => [$b->totalDue(), $a->name] <=> [$a->totalDue(), $b->name]);

        return [
            'all'              => $all,
            'top_payers'       => $byPaidDesc,
            'bottom_payers'    => $byPaidAsc,
            'top_debtors'      => $debtDesc,
            'smallest_debtors' => array_reverse($debtDesc),
            'up_to_date'       => $upToDate,
            'with_debt'        => $withDebt,
        ];
    }
}
