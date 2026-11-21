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

        return $query->select(['m.id', 'm.oid', 'm.first_name', 'm.last_name', 'cm.campaign_oid'])->distinct()->get()->all();
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

    public function getOldestUnpaidFeeDueDate(int $memberOid, ?int $campaignOid = null): ?string
    {
        return DB::table('monthly_fees')
            ->where('member_oid', $memberOid)
            ->whereIn('fee_status', ['pending', 'partial'])
            ->when($campaignOid !== null, fn($q) => $q->where('campaign_oid', $campaignOid))
            ->min('due_date');
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

    public function getLastPenaltyDate(int $memberOid, ?int $campaignOid = null): ?string
    {
        return DB::table('penalties')
            ->where('member_oid', $memberOid)
            ->when($campaignOid !== null, fn($q) => $q->where('campaign_oid', $campaignOid))
            ->max('penalty_date');
    }

    public function completeProcessExecution(int $id, array $data): void
    {
        DB::table('process_executions')->where('id', $id)->update(
            array_merge($data, ['updated_at' => now()])
        );
    }

    public function getProcessExecutionById(int $id): ?object
    {
        return DB::table('process_executions')->where('id', $id)->first();
    }

    public function getLatestExecutionByName(string $processName): ?object
    {
        return DB::table('process_executions')
            ->where('process_name', $processName)
            ->orderByDesc('execution_date')
            ->orderByDesc('id')
            ->first();
    }

    public function countPaidGeneratedRows(int $processId): int
    {
        $fees = DB::table('monthly_fees')
            ->where('generated_by_process_oid', $processId)
            ->where(fn ($q) => $q->where('amount_paid', '>', 0)->orWhere('fee_status', '!=', 'pending'))
            ->count();

        $penalties = DB::table('penalties')
            ->where('generated_by_process_oid', $processId)
            ->where(fn ($q) => $q->where('amount_paid', '>', 0)->orWhere('penalty_status', '!=', 'pending'))
            ->count();

        return $fees + $penalties;
    }

    public function deleteGeneratedRows(int $processId): array
    {
        return [
            'fees'      => DB::table('monthly_fees')->where('generated_by_process_oid', $processId)->delete(),
            'penalties' => DB::table('penalties')->where('generated_by_process_oid', $processId)->delete(),
        ];
    }

    public function markExecutionReverted(int $id, int $revertedByOid, array $deleted): void
    {
        $execution = $this->getProcessExecutionById($id);
        $metadata  = json_decode($execution->metadata ?? '[]', true) ?: [];
        $metadata['reverted'] = [
            'by_oid'            => $revertedByOid,
            'at'                => now()->toDateTimeString(),
            'fees_deleted'      => $deleted['fees'],
            'penalties_deleted' => $deleted['penalties'],
        ];

        DB::table('process_executions')->where('id', $id)->update([
            'execution_status' => 'reverted',
            'metadata'         => json_encode($metadata),
            'updated_at'       => now(),
        ]);
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
