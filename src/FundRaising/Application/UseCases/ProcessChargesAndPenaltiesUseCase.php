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
            $details            = []; // detalle por miembro (para la vista previa)

            foreach ($members as $member) {
                $memberOid      = $member->oid ?? $member->id;
                $memberCampaignOid = $member->campaign_oid;
                $detail = [
                    'member_oid'       => (int) $memberOid,
                    'member_name'      => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) ?: "#{$memberOid}",
                    'fees_count'       => 0,
                    'fees_amount'      => 0.0,
                    'penalties_count'  => 0,
                    'penalties_amount' => 0.0,
                    'penalties_from'   => null,
                    'penalties_to'     => null,
                ];

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
                    $detail['fees_count']++;
                    $detail['fees_amount'] += $feeAmount;
                }

                $oldestDueDateRaw = $this->repository->getOldestUnpaidFeeDueDate($memberOid, $memberCampaignOid);

                if ($oldestDueDateRaw !== null) {
                    $oldestDueDate = Carbon::parse($oldestDueDateRaw)->startOfDay();

                    if ($today->greaterThan($oldestDueDate)) {
                        $pendingBalance = $this->repository->getPendingFeeBalance($memberOid, $memberCampaignOid);

                        if ($pendingBalance > 0) {
                            // La mora arranca el día después del vencimiento de la cuota impaga más antigua.
                            // Si ya hay moras registradas, se continúa desde la siguiente a la última,
                            // pero nunca antes del vencimiento: una mora vieja (de un atraso ya pagado)
                            // no debe hacer que se cobren días en que no se debía nada.
                            $firstPenaltyDay    = $oldestDueDate->copy()->addDay();
                            $lastPenaltyDateRaw = $this->repository->getLastPenaltyDate($memberOid, $memberCampaignOid);
                            $cursor = $firstPenaltyDay;
                            if ($lastPenaltyDateRaw) {
                                $afterLast = Carbon::parse($lastPenaltyDateRaw)->startOfDay()->addDay();
                                if ($afterLast->greaterThan($cursor)) {
                                    $cursor = $afterLast;
                                }
                            }

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
                                    $detail['penalties_count']++;
                                    $detail['penalties_amount'] += $dailyRate;
                                    $detail['penalties_from'] ??= $cursor->toDateString();
                                    $detail['penalties_to']    = $cursor->toDateString();
                                }
                                $cursor->addDay();
                            }
                        }
                    }
                }

                if ($detail['fees_count'] > 0 || $detail['penalties_count'] > 0) {
                    $detail['fees_amount']      = round($detail['fees_amount'], 2);
                    $detail['penalties_amount'] = round($detail['penalties_amount'], 2);
                    $details[] = $detail;
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
                'process_id'          => $processId,
                'members_processed'   => count($members),
                'fees_generated'      => $feesGenerated,
                'penalties_generated' => $penaltiesGenerated,
                'fees_amount'         => round(array_sum(array_column($details, 'fees_amount')), 2),
                'penalties_amount'    => round(array_sum(array_column($details, 'penalties_amount')), 2),
                'period'              => $periodYear . '-' . str_pad($periodMonth, 2, '0', STR_PAD_LEFT),
                'details'             => $details,
            ];

        } catch (\Throwable $e) {
            Log::error('[ProcessChargesAndPenaltiesUseCase] Failed', ['error' => $e->getMessage()]);
            // Dentro de una transacción de Postgres ya abortada esta escritura también falla;
            // no debe tapar el error original.
            try {
                $this->repository->failProcessExecution($processId, $e->getMessage());
            } catch (\Throwable) {
                // se ignora: la transacción se revierte completa y el error real se relanza
            }
            throw $e;
        }
    }
}
