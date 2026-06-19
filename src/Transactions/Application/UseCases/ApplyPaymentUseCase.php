<?php

namespace Src\Transactions\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Transactions\Application\DTOs\DTOApplyPaymentRequest;
use Src\Transactions\Domain\Exceptions\InvalidTransactionAmountException;
use Src\Transactions\Domain\Repositories\TransactionRepositoryInterface;

class ApplyPaymentUseCase
{
    public function __construct(
        private readonly TransactionRepositoryInterface $repository,
    ) {}

    public function execute(DTOApplyPaymentRequest $dto): array
    {
        Log::info('[ApplyPaymentUseCase] Starting', [
            'member_oid' => $dto->memberOid,
            'amount'     => $dto->amount,
        ]);

        return DB::transaction(function () use ($dto): array {

            Log::info('[ApplyPaymentUseCase] Step 1 — Validating amount');
            if ($dto->amount <= 0) {
                throw InvalidTransactionAmountException::mustBePositive();
            }

            // Snapshot balances BEFORE applying
            $prevPenaltiesBalance = $this->repository->getPendingPenaltiesBalance($dto->memberOid);
            $prevFeesBalance      = $this->repository->getPendingFeesBalance($dto->memberOid);

            Log::info('[ApplyPaymentUseCase] Step 2 — Creating transaction record');
            $transaction = $this->repository->create([
                'transaction_type'  => 'income',
                'member_oid'        => $dto->memberOid,
                'campaign_oid'      => $dto->campaignOid,
                'amount'            => $dto->amount,
                'description'       => 'Member payment',
                'reference'         => null,
                'transaction_date'  => $dto->transactionDate,
                'notes'             => $dto->notes,
                'status'            => true,
                'created_by_oid'    => $dto->createdByOid,
                'updated_by_oid'    => $dto->createdByOid,
            ]);

            $remaining     = round($dto->amount, 2);
            $penaltiesPaid = 0.0;
            $feesPaid      = 0.0;
            $details       = [];
            $appliedOrder  = 1;

            Log::info('[ApplyPaymentUseCase] Step 3 — Applying to penalties', [
                'member_oid' => $dto->memberOid,
                'remaining'  => $remaining,
            ]);
            if ($remaining > 0) {
                $penalties = $this->repository->getPendingPenaltiesByMember($dto->memberOid);

                foreach ($penalties as $penalty) {
                    if ($remaining <= 0) {
                        break;
                    }
                    $apply      = min($remaining, (float) $penalty->balance);
                    $newBalance = round((float) $penalty->balance - $apply, 2);

                    $this->repository->applyPaymentToPenalty(
                        $penalty->id,
                        round((float) $penalty->amount_paid + $apply, 2),
                        $newBalance,
                    );

                    $details[] = [
                        'payment_receipt_oid' => $transaction->oid,
                        'member_oid'          => $dto->memberOid,
                        'detail_type'         => 'penalty',
                        'reference_type'      => 'penalties',
                        'reference_oid'       => $penalty->id,
                        'amount_applied'      => $apply,
                        'applied_order'       => $appliedOrder++,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];

                    $remaining     = round($remaining - $apply, 2);
                    $penaltiesPaid = round($penaltiesPaid + $apply, 2);
                }
            }

            Log::info('[ApplyPaymentUseCase] Step 4 — Applying to monthly fees', [
                'member_oid'     => $dto->memberOid,
                'remaining'      => $remaining,
                'penalties_paid' => $penaltiesPaid,
            ]);
            if ($remaining > 0) {
                $fees = $this->repository->getPendingFeesByMember($dto->memberOid);

                foreach ($fees as $fee) {
                    if ($remaining <= 0) {
                        break;
                    }
                    $apply      = min($remaining, (float) $fee->balance);
                    $newBalance = round((float) $fee->balance - $apply, 2);

                    $this->repository->applyPaymentToFee(
                        $fee->id,
                        round((float) $fee->amount_paid + $apply, 2),
                        $newBalance,
                    );

                    $details[] = [
                        'payment_receipt_oid' => $transaction->oid,
                        'member_oid'          => $dto->memberOid,
                        'detail_type'         => 'monthly_fee',
                        'reference_type'      => 'monthly_fees',
                        'reference_oid'       => $fee->id,
                        'amount_applied'      => $apply,
                        'applied_order'       => $appliedOrder++,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];

                    $remaining = round($remaining - $apply, 2);
                    $feesPaid  = round($feesPaid + $apply, 2);
                }
            }

            if ($dto->campaignOid !== null) {
                Log::info('[ApplyPaymentUseCase] Step 5 — Incrementing campaign collected_amount', [
                    'campaign_oid' => $dto->campaignOid,
                    'amount'       => $dto->amount,
                ]);
                $this->repository->incrementCampaignCollected($dto->campaignOid, $dto->amount);
            }

            // Snapshot balances AFTER applying and persist audit fields
            $newPenaltiesBalance = $this->repository->getPendingPenaltiesBalance($dto->memberOid);
            $newFeesBalance      = $this->repository->getPendingFeesBalance($dto->memberOid);

            $this->repository->updateTransactionAuditSnapshot($transaction->uuid, [
                'previous_penalties_balance' => $prevPenaltiesBalance,
                'new_penalties_balance'      => $newPenaltiesBalance,
                'previous_fees_balance'      => $prevFeesBalance,
                'new_fees_balance'           => $newFeesBalance,
                'applied_to_penalties'       => $penaltiesPaid,
                'applied_to_fees'            => $feesPaid,
            ]);

            foreach ($details as $detail) {
                $this->repository->createTransactionDetail($detail);
            }

            Log::info('[ApplyPaymentUseCase] Completed', [
                'transaction_uuid'           => $transaction->uuid,
                'applied_to_penalties'       => $penaltiesPaid,
                'applied_to_fees'            => $feesPaid,
                'overpayment'                => $remaining,
                'previous_penalties_balance' => $prevPenaltiesBalance,
                'new_penalties_balance'      => $newPenaltiesBalance,
                'previous_fees_balance'      => $prevFeesBalance,
                'new_fees_balance'           => $newFeesBalance,
                'campaign_oid'               => $dto->campaignOid,
            ]);

            return [
                'transaction_uuid'           => $transaction->uuid,
                'amount_paid'                => $dto->amount,
                'applied_to_penalties'       => $penaltiesPaid,
                'applied_to_fees'            => $feesPaid,
                'overpayment'                => $remaining,
                'previous_penalties_balance' => $prevPenaltiesBalance,
                'new_penalties_balance'      => $newPenaltiesBalance,
                'previous_fees_balance'      => $prevFeesBalance,
                'new_fees_balance'           => $newFeesBalance,
            ];
        });
    }
}
