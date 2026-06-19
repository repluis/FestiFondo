<?php

namespace Src\Transactions\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Transactions\Domain\Exceptions\TransactionAlreadyCancelledException;
use Src\Transactions\Domain\Exceptions\TransactionNotFoundException;
use Src\Transactions\Domain\Repositories\TransactionRepositoryInterface;

class CancelTransactionUseCase
{
    public function __construct(
        private readonly TransactionRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid, int $cancelledByOid): void
    {
        Log::info('[CancelTransactionUseCase] Starting', ['uuid' => $uuid]);

        DB::transaction(function () use ($uuid, $cancelledByOid): void {

            Log::info('[CancelTransactionUseCase] Step 1 — Finding transaction');
            $transaction = $this->repository->findByUuid($uuid);

            if ($transaction === null) {
                throw TransactionNotFoundException::withUuid($uuid);
            }

            Log::info('[CancelTransactionUseCase] Step 2 — Checking status');
            if ($transaction->status === false) {
                throw TransactionAlreadyCancelledException::withUuid($uuid);
            }

            Log::info('[CancelTransactionUseCase] Step 3 — Cancelling');
            $this->repository->cancel($transaction->oid, $cancelledByOid);

            Log::info('[CancelTransactionUseCase] Completed', ['uuid' => $uuid]);
        });
    }
}
