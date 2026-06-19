<?php

namespace Src\Reports\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Src\Reports\Application\DTOs\DTOTransactionReportFiltersRequest;
use Src\Reports\Domain\Entities\TransactionReport;
use Src\Reports\Domain\Repositories\ReportsRepositoryInterface;

class EloquentReportsRepository implements ReportsRepositoryInterface
{
    public function getTransactionReport(DTOTransactionReportFiltersRequest $filters): array
    {
        $query = DB::table('transactions as t')
            ->leftJoin('members as m', 't.member_oid', '=', 'm.oid')
            ->leftJoin('fund_raisings as fr', 't.campaign_oid', '=', 'fr.oid')
            ->select([
                't.oid',
                't.uuid',
                't.status',
                't.transaction_type as type',
                't.amount',
                't.description',
                't.reference',
                't.transaction_date',
                't.notes',
                't.member_oid',
                't.campaign_oid',
                't.created_at',
                't.applied_to_penalties',
                't.applied_to_fees',
                't.previous_penalties_balance',
                't.new_penalties_balance',
                't.previous_fees_balance',
                't.new_fees_balance',
                DB::raw("(m.first_name || ' ' || m.last_name) as member_name"),
                'fr.name as campaign_name',
            ]);

        if ($filters->memberOid !== null) {
            $query->where('t.member_oid', $filters->memberOid);
        }

        if ($filters->campaignOid !== null) {
            $query->where('t.campaign_oid', $filters->campaignOid);
        }

        if ($filters->type !== null) {
            $query->where('t.transaction_type', $filters->type);
        }

        if ($filters->dateFrom !== null) {
            $query->where('t.transaction_date', '>=', $filters->dateFrom);
        }

        if ($filters->dateTo !== null) {
            $query->where('t.transaction_date', '<=', $filters->dateTo);
        }

        return $query
            ->orderByDesc('t.transaction_date')
            ->orderByDesc('t.id')
            ->get()
            ->map(fn($row) => new TransactionReport(
                oid:                      $row->oid,
                uuid:                     $row->uuid,
                status:                   (bool) $row->status,
                type:                     $row->type,
                amount:                   (float) $row->amount,
                description:              $row->description,
                reference:                $row->reference ?? null,
                transactionDate:          $row->transaction_date,
                notes:                    $row->notes ?? null,
                memberOid:                $row->member_oid ?? null,
                memberName:               $row->member_name ?? null,
                campaignOid:              $row->campaign_oid ?? null,
                campaignName:             $row->campaign_name ?? null,
                createdAt:                $row->created_at ?? null,
                appliedToPenalties:       isset($row->applied_to_penalties)       ? (float) $row->applied_to_penalties       : null,
                appliedToFees:            isset($row->applied_to_fees)            ? (float) $row->applied_to_fees            : null,
                previousPenaltiesBalance: isset($row->previous_penalties_balance) ? (float) $row->previous_penalties_balance : null,
                newPenaltiesBalance:      isset($row->new_penalties_balance)      ? (float) $row->new_penalties_balance      : null,
                previousFeesBalance:      isset($row->previous_fees_balance)      ? (float) $row->previous_fees_balance      : null,
                newFeesBalance:           isset($row->new_fees_balance)           ? (float) $row->new_fees_balance           : null,
            ))
            ->all();
    }

    public function getMembersDropdown(): array
    {
        return DB::table('members')
            ->where('status', true)
            ->select(['oid', 'first_name', 'last_name'])
            ->orderBy('first_name')
            ->get()
            ->map(fn($m) => [
                'oid'  => $m->oid,
                'name' => $m->first_name . ' ' . $m->last_name,
            ])
            ->all();
    }

    public function getCampaignsDropdown(): array
    {
        return DB::table('fund_raisings')
            ->where('status', true)
            ->select(['oid', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'oid'  => $c->oid,
                'name' => $c->name,
            ])
            ->all();
    }
}
