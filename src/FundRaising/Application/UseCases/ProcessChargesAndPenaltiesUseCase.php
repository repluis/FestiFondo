<?php

namespace Src\FundRaising\Application\UseCases;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Src\FundRaising\Domain\Repositories\ProcessChargesRepositoryInterface;

class ProcessChargesAndPenaltiesUseCase
{
    public function __construct(
        private readonly ProcessChargesRepositoryInterface $repository,
    ) {}

    public function execute(int $triggeredByOid, ?string $campaignUuid = null): array
    {
        $today       = Carbon::today();
        $processName = $campaignUuid
            ? 'fees_and_penalties_campaign_' . substr($campaignUuid, 0, 8)
            : 'fees_and_penalties';
        $processKey  = 'fees_penalties_' . $today->format('Y_m_d')
            . ($campaignUuid ? '_' . substr($campaignUuid, 0, 8) : '');

        Log::info('[ProcessChargesAndPenaltiesUseCase] Starting', [
            'process_key'   => $processKey,
            'process_name'  => $processName,
            'triggered_by'  => $triggeredByOid,
            'campaign_uuid' => $campaignUuid,
        ]);

        Log::info('[ProcessChargesAndPenaltiesUseCase] Step 1 — Checking prior execution');
        $existing = $this->repository->findProcessExecution($processKey);

        if ($existing) {
            if ($existing->execution_status === 'running') {
                throw new \RuntimeException('Process is currently running. Please wait and try again in a few moments.');
            }
            $this->repository->resetProcessExecution($existing->id);
            $processId = $existing->id;
        } else {
            Log::info('[ProcessChargesAndPenaltiesUseCase] Step 2 — Registering process execution');
            $processId = $this->repository->createProcessExecution([
                'process_name'     => $processName,
                'process_key'      => $processKey,
                'execution_date'   => $today->toDateString(),
                'started_at'       => now(),
                'execution_status' => 'running',
                'triggered_by_oid' => $triggeredByOid,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        try {
            Log::info('[ProcessChargesAndPenaltiesUseCase] Step 3 — Loading fee configuration');
            if ($campaignUuid !== null) {
                $rates     = $this->repository->getCampaignFeeRates($campaignUuid);
                $feeAmount = $rates['monthly_fee_amount'];
                $dailyRate = $rates['daily_penalty_rate'];
                $dueDay    = $rates['due_day'];
            } else {
                $configs   = $this->repository->getSystemConfigs([
                    'monthly_fee_amount',
                    'daily_penalty_rate',
                    'due_day',
                ]);
                $feeAmount = (float) ($configs['monthly_fee_amount'] ?? 1.00);
                $dailyRate = (float) ($configs['daily_penalty_rate'] ?? 0.05);
                $dueDay    = (int)   ($configs['due_day']            ?? 15);
            }

            $periodYear  = $today->year;
            $periodMonth = $today->month;
            $safeDay     = min($dueDay, $today->copy()->endOfMonth()->day);
            $dueDate     = Carbon::create($periodYear, $periodMonth, $safeDay)->startOfDay();

            Log::info('[ProcessChargesAndPenaltiesUseCase] Step 4 — Period determined', [
                'period'     => "{$periodYear}-{$periodMonth}",
                'due_date'   => $dueDate->toDateString(),
                'fee_amount' => $feeAmount,
                'daily_rate' => $dailyRate,
            ]);

            Log::info('[ProcessChargesAndPenaltiesUseCase] Step 5 — Loading active campaign members', [
                'campaign_uuid' => $campaignUuid,
            ]);
            $members = $this->repository->getActiveCampaignMembers($campaignUuid);

            $feesGenerated      = 0;
            $penaltiesGenerated = 0;

            foreach ($members as $member) {
                $memberOid      = $member->oid ?? $member->id;
                $memberCampaignOid = $member->campaign_oid;

                Log::info('[ProcessChargesAndPenaltiesUseCase] Step 6 — Generating monthly fee', [
                    'member_oid' => $memberOid,
                ]);
                if (
                    $today->greaterThanOrEqualTo($dueDate)
                    && !$this->repository->monthlyFeeExistsForMember($memberOid, $periodYear, $periodMonth, $memberCampaignOid)
                ) {
                    $this->repository->createMonthlyFee([
                        'member_oid'               => $memberOid,
                        'campaign_oid'             => $memberCampaignOid,
                        'period_year'              => $periodYear,
                        'period_month'             => $periodMonth,
                        'due_date'                 => $dueDate->toDateString(),
                        'amount'                   => $feeAmount,
                        'amount_paid'              => 0,
                        'balance'                  => $feeAmount,
                        'fee_status'               => 'pending',
                        'generated_by_process_oid' => $processId,
                        'created_at'               => now(),
                        'updated_at'               => now(),
                    ]);
                    $feesGenerated++;
                }

                $oldestDueDateRaw = $this->repository->getOldestUnpaidFeeDueDate($memberOid, $memberCampaignOid);

                if ($oldestDueDateRaw !== null) {
                    $oldestDueDate = Carbon::parse($oldestDueDateRaw)->startOfDay();

                    if ($today->greaterThan($oldestDueDate)) {
                        $pendingBalance = $this->repository->getPendingFeeBalance($memberOid, $memberCampaignOid);

                        if ($pendingBalance > 0) {
                            $lastPenaltyDateRaw = $this->repository->getLastPenaltyDate($memberOid, $memberCampaignOid);
                            $cursor = $lastPenaltyDateRaw
                                ? Carbon::parse($lastPenaltyDateRaw)->startOfDay()->addDay()
                                : $oldestDueDate->copy()->addDay();

                            Log::info('[ProcessChargesAndPenaltiesUseCase] Step 7 — Generating daily penalties', [
                                'member_oid'      => $memberOid,
                                'oldest_due_date' => $oldestDueDate->toDateString(),
                                'from'            => $cursor->toDateString(),
                                'to'              => $today->toDateString(),
                                'pending_balance' => $pendingBalance,
                            ]);

                            while ($cursor->lessThanOrEqualTo($today)) {
                                if (!$this->repository->penaltyExistsForMember($memberOid, $cursor->toDateString(), $memberCampaignOid)) {
                                    $this->repository->createPenalty([
                                        'member_oid'               => $memberOid,
                                        'campaign_oid'             => $memberCampaignOid,
                                        'period_year'              => $cursor->year,
                                        'period_month'             => $cursor->month,
                                        'penalty_date'             => $cursor->toDateString(),
                                        'days_overdue'             => (int) $oldestDueDate->diffInDays($cursor),
                                        'daily_rate_snapshot'      => $dailyRate,
                                        'amount'                   => $dailyRate,
                                        'amount_paid'              => 0,
                                        'balance'                  => $dailyRate,
                                        'penalty_status'           => 'pending',
                                        'generated_by_process_oid' => $processId,
                                        'created_at'               => now(),
                                        'updated_at'               => now(),
                                    ]);
                                    $penaltiesGenerated++;
                                }
                                $cursor->addDay();
                            }
                        }
                    }
                }
            }

            Log::info('[ProcessChargesAndPenaltiesUseCase] Step 8 — Finalizing process execution');
            $this->repository->completeProcessExecution($processId, [
                'execution_status'    => 'completed',
                'finished_at'         => now(),
                'members_processed'   => count($members),
                'fees_generated'      => $feesGenerated,
                'penalties_generated' => $penaltiesGenerated,
            ]);

            Log::info('[ProcessChargesAndPenaltiesUseCase] Completed', [
                'members'    => count($members),
                'fees'       => $feesGenerated,
                'penalties'  => $penaltiesGenerated,
            ]);

            return [
                'members_processed'   => count($members),
                'fees_generated'      => $feesGenerated,
                'penalties_generated' => $penaltiesGenerated,
                'period'              => $periodYear . '-' . str_pad($periodMonth, 2, '0', STR_PAD_LEFT),
            ];

        } catch (\Throwable $e) {
            $this->repository->failProcessExecution($processId, $e->getMessage());
            throw $e;
        }
    }
}
