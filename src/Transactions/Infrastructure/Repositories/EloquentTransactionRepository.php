<?php

namespace Src\Transactions\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Src\Transactions\Domain\Entities\Transaction;
use Src\Transactions\Domain\Repositories\TransactionRepositoryInterface;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function findByUuid(string $uuid): ?Transaction
    {
        $model = TransactionModel::where('uuid', $uuid)->first();

        return $model?->toEntity();
    }

    public function listAll(array $filters = []): array
    {
        $query = TransactionModel::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('transaction_type', $filters['type']);
        }

        if (isset($filters['member_oid'])) {
            $query->where('member_oid', $filters['member_oid']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . strtolower($filters['search']) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(description) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(reference) LIKE ?', [$term]);
            });
        }

        return $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn($m) => $m->toEntity())
            ->all();
    }

    public function create(array $data): Transaction
    {
        $model = TransactionModel::create($data);
        $model->refresh();

        return $model->toEntity();
    }

    public function cancel(int $oid, int $updatedByOid): void
    {
        TransactionModel::where('oid', $oid)->update([
            'status'          => false,
            'updated_by_oid'  => $updatedByOid,
        ]);
    }

    public function getPendingPenaltiesByMember(int $memberOid): array
    {
        return DB::table('penalties')
            ->where('member_oid', $memberOid)
            ->whereIn('penalty_status', ['pending', 'partial'])
            ->orderBy('penalty_date')
            ->get()
            ->all();
    }

    public function applyPaymentToPenalty(int $penaltyId, float $amountPaid, float $newBalance): void
    {
        DB::table('penalties')->where('id', $penaltyId)->update([
            'amount_paid'    => $amountPaid,
            'balance'        => $newBalance,
            'penalty_status' => $newBalance == 0 ? 'paid' : 'partial',
            'updated_at'     => now(),
        ]);
    }

    public function getPendingPenaltiesBalance(int $memberOid): float
    {
        return (float) DB::table('penalties')
            ->where('member_oid', $memberOid)
            ->whereIn('penalty_status', ['pending', 'partial'])
            ->sum('balance');
    }

    public function getPendingFeesBalance(int $memberOid): float
    {
        return (float) DB::table('monthly_fees')
            ->where('member_oid', $memberOid)
            ->whereIn('fee_status', ['pending', 'partial'])
            ->sum('balance');
    }

    public function getPendingFeesByMember(int $memberOid): array
    {
        return DB::table('monthly_fees')
            ->where('member_oid', $memberOid)
            ->whereIn('fee_status', ['pending', 'partial'])
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->get()
            ->all();
    }

    public function applyPaymentToFee(int $feeId, float $amountPaid, float $newBalance): void
    {
        DB::table('monthly_fees')->where('id', $feeId)->update([
            'amount_paid' => $amountPaid,
            'balance'     => $newBalance,
            'fee_status'  => $newBalance == 0 ? 'paid' : 'partial',
            'updated_at'  => now(),
        ]);
    }

    public function incrementCampaignCollected(int $campaignOid, float $amount): void
    {
        DB::table('fund_raisings')
            ->where('oid', $campaignOid)
            ->increment('collected_amount', $amount);
    }

    public function updateTransactionAuditSnapshot(string $uuid, array $fields): void
    {
        DB::table('transactions')->where('uuid', $uuid)->update($fields);
    }

    public function createTransactionDetail(array $data): void
    {
        DB::table('transaction_details')->insert($data);
    }
}
