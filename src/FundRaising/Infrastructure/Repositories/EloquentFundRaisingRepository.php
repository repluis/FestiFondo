<?php

namespace Src\FundRaising\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Src\FundRaising\Domain\Entities\FundRaising;
use Src\FundRaising\Domain\Repositories\FundRaisingRepositoryInterface;

class EloquentFundRaisingRepository implements FundRaisingRepositoryInterface
{
    public function findByUuid(string $uuid): ?FundRaising
    {
        $model = FundRaisingModel::where('uuid', $uuid)->first();

        return $model?->toEntity();
    }

    public function existsByName(string $name, ?int $excludeOid = null): bool
    {
        return FundRaisingModel::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->when($excludeOid !== null, fn($q) => $q->where('oid', '!=', $excludeOid))
            ->exists();
    }

    public function listAll(array $filters = []): array
    {
        $query = FundRaisingModel::query();

        if (array_key_exists('status', $filters)) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['fund_raising_status'])) {
            $query->where('fund_raising_status', $filters['fund_raising_status']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . strtolower($filters['search']) . '%';
            $query->whereRaw('LOWER(name) LIKE ?', [$term]);
        }

        return $query->orderBy('start_date', 'desc')
                     ->get()
                     ->map(fn($m) => $m->toEntity())
                     ->all();
    }

    public function create(array $data): FundRaising
    {
        $model = FundRaisingModel::create($data);
        $model->refresh();

        return $model->toEntity();
    }

    public function update(int $oid, array $data): FundRaising
    {
        FundRaisingModel::where('oid', $oid)->update($data);

        return FundRaisingModel::where('oid', $oid)->firstOrFail()->toEntity();
    }

    public function cancel(int $oid, int $updatedByOid): void
    {
        FundRaisingModel::where('oid', $oid)->update([
            'fund_raising_status' => 'cancelled',
            'status'              => false,
            'updated_by_oid'      => $updatedByOid,
        ]);
    }

    public function getDashboardMembersWithBalance(): array
    {
        $hasFees         = Schema::hasTable('monthly_fees');
        $hasPenalties    = Schema::hasTable('penalties');
        $hasTransactions = Schema::hasTable('transactions')
            && Schema::hasColumn('transactions', 'transaction_type')
            && Schema::hasColumn('transactions', 'member_oid');
        $hasPenaltiesPaid = $hasPenalties && Schema::hasColumn('penalties', 'amount_paid');

        $query = DB::table('members as m')->where('m.status', true);

        if ($hasFees) {
            $query->leftJoin(
                DB::raw("(SELECT member_oid, SUM(balance) AS fees_balance
                           FROM monthly_fees
                           WHERE fee_status IN ('pending','partial')
                           GROUP BY member_oid) mf"),
                'mf.member_oid', '=', 'm.oid'
            );
        }

        if ($hasPenalties) {
            $query->leftJoin(
                DB::raw("(SELECT member_oid, SUM(balance) AS penalties_balance
                           FROM penalties
                           WHERE penalty_status IN ('pending','partial')
                           GROUP BY member_oid) pe"),
                'pe.member_oid', '=', 'm.oid'
            );
        }

        if ($hasTransactions) {
            $query->leftJoin(
                DB::raw("(SELECT member_oid,
                                  MAX(transaction_date) AS last_payment_date,
                                  COALESCE(SUM(amount), 0) AS total_paid
                           FROM transactions
                           WHERE transaction_type = 'income' AND status = true
                           GROUP BY member_oid) pa"),
                'pa.member_oid', '=', 'm.oid'
            );
        }

        if ($hasPenaltiesPaid) {
            $query->leftJoin(
                DB::raw("(SELECT member_oid, COALESCE(SUM(amount_paid), 0) AS penalties_paid
                           FROM penalties
                           WHERE status = true
                           GROUP BY member_oid) pp"),
                'pp.member_oid', '=', 'm.oid'
            );
        }

        return $query->select([
            'm.oid AS member_oid',
            'm.uuid',
            'm.identification',
            'm.first_name',
            'm.last_name',
            $hasFees          ? DB::raw('COALESCE(mf.fees_balance, 0) AS fees_balance')          : DB::raw('0 AS fees_balance'),
            $hasPenalties     ? DB::raw('COALESCE(pe.penalties_balance, 0) AS penalties_balance') : DB::raw('0 AS penalties_balance'),
            ($hasFees && $hasPenalties)
                ? DB::raw('COALESCE(mf.fees_balance, 0) + COALESCE(pe.penalties_balance, 0) AS total_balance')
                : DB::raw('0 AS total_balance'),
            $hasTransactions  ? DB::raw('pa.last_payment_date')                     : DB::raw('NULL AS last_payment_date'),
            $hasTransactions  ? DB::raw('COALESCE(pa.total_paid, 0) AS total_paid') : DB::raw('0 AS total_paid'),
            $hasPenaltiesPaid ? DB::raw('COALESCE(pp.penalties_paid, 0) AS penalties_paid') : DB::raw('0 AS penalties_paid'),
        ])
        ->orderBy('m.first_name')
        ->get()
        ->map(fn($r) => (array) $r)
        ->toArray();
    }
}
