<?php

namespace Src\FundRaising\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Src\FundRaising\Domain\Repositories\ProcessChargesRepositoryInterface;

class EloquentProcessChargesRepository implements ProcessChargesRepositoryInterface
{
    public function findProcessExecution(string $processKey): ?object
    {
        return DB::table('process_executions')
            ->where('process_key', $processKey)
            ->first();
    }

    public function resetProcessExecution(int $id): void
    {
        DB::table('process_executions')->where('id', $id)->update([
            'execution_status' => 'running',
            'started_at'       => now(),
            'error_message'    => null,
            'updated_at'       => now(),
        ]);
    }

    public function createProcessExecution(array $data): int
    {
        return DB::table('process_executions')->insertGetId($data);
    }

    public function getLastProcessExecution(): ?object
    {
        if (!Schema::hasTable('process_executions')) {
            return null;
        }

        return DB::table('process_executions')
            ->where('process_name', 'fees_and_penalties')
            ->orderByDesc('execution_date')
            ->first();
    }

    public function getSystemConfigs(array $keys): array
    {
        return DB::table('system_configurations')
            ->whereIn('config_key', $keys)
            ->pluck('config_value', 'config_key')
            ->toArray();
    }

    public function getActiveMembers(): array
    {
        return DB::table('members')
            ->where('status', true)
            ->select(['id', 'oid'])
            ->get()
            ->all();
    }

    public function getActiveCampaignMembers(?string $campaignUuid = null): array
    {
        $query = DB::table('members as m')
            ->join('campaign_members as cm', 'cm.member_oid', '=', 'm.oid')
            ->join('fund_raisings as fr', 'fr.oid', '=', 'cm.campaign_oid')
            ->where('m.status', true)
            ->where('cm.status', true)
            ->where('fr.fund_raising_status', 'active')
            ->where('fr.status', true);

        if ($campaignUuid !== null) {
            $query->where('fr.uuid', $campaignUuid);
        }

        return $query->select(['m.id', 'm.oid'])->distinct()->get()->all();
    }

    public function getCampaignOidByUuid(string $uuid): ?int
    {
        return DB::table('fund_raisings')->where('uuid', $uuid)->value('oid');
    }

    public function getCampaignFeeRates(string $uuid): array
    {
        $row = DB::table('fund_raisings')
            ->where('uuid', $uuid)
            ->select(['monthly_fee_amount', 'daily_penalty_rate', 'due_day'])
            ->first();

        return [
            'monthly_fee_amount' => (float) ($row->monthly_fee_amount ?? 1.00),
            'daily_penalty_rate' => (float) ($row->daily_penalty_rate ?? 0.05),
            'due_day'            => (int)   ($row->due_day            ?? 15),
        ];
    }

    public function monthlyFeeExistsForMember(int $memberOid, int $year, int $month, ?int $campaignOid = null): bool
    {
        return DB::table('monthly_fees')
            ->where('member_oid', $memberOid)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->when($campaignOid !== null, fn($q) => $q->where('campaign_oid', $campaignOid))
            ->exists();
    }

    public function createMonthlyFee(array $data): void
    {
        DB::table('monthly_fees')->insert($data);
    }

    public function getPendingFeeBalance(int $memberOid, ?int $campaignOid = null): float
    {
        return (float) DB::table('monthly_fees')
            ->where('member_oid', $memberOid)
            ->whereIn('fee_status', ['pending', 'partial'])
            ->when($campaignOid !== null, fn($q) => $q->where('campaign_oid', $campaignOid))
            ->sum('balance');
    }

    public function penaltyExistsForMember(int $memberOid, string $date, ?int $campaignOid = null): bool
    {
        return DB::table('penalties')
            ->where('member_oid', $memberOid)
            ->where('penalty_date', $date)
            ->when($campaignOid !== null, fn($q) => $q->where('campaign_oid', $campaignOid))
            ->exists();
    }

    public function createPenalty(array $data): void
    {
        DB::table('penalties')->insert($data);
    }

    public function completeProcessExecution(int $id, array $data): void
    {
        DB::table('process_executions')->where('id', $id)->update(
            array_merge($data, ['updated_at' => now()])
        );
    }

    public function failProcessExecution(int $id, string $error): void
    {
        DB::table('process_executions')->where('id', $id)->update([
            'execution_status' => 'failed',
            'finished_at'      => now(),
            'error_message'    => $error,
            'updated_at'       => now(),
        ]);
    }
}
