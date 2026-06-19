<?php

namespace Src\Transactions\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Transactions\Application\DTOs\DTOCreateTransactionRequest;
use Src\Transactions\Application\DTOs\DTOTransactionResponse;
use Src\Transactions\Domain\Exceptions\InvalidTransactionAmountException;
use Src\Transactions\Domain\Repositories\TransactionRepositoryInterface;

class CreateTransactionUseCase
{
    public function __construct(
        private readonly TransactionRepositoryInterface $repository,
    ) {}

    public function execute(DTOCreateTransactionRequest $dto): DTOTransactionResponse
    {
        Log::info('[CreateTransactionUseCase] Starting', [
            'type'   => $dto->type,
            'amount' => $dto->amount,
        ]);

        return DB::transaction(function () use ($dto): DTOTransactionResponse {

            Log::info('[CreateTransactionUseCase] Step 1 — Validating amount');
            if ($dto->amount <= 0) {
                throw InvalidTransactionAmountException::mustBePositive();
            }

            Log::info('[CreateTransactionUseCase] Step 2 — Persisting transaction');
            $transaction = $this->repository->create([
                'transaction_type' => $dto->type,
                'member_oid'       => $dto->memberOid,
                'amount'           => $dto->amount,
                'description'      => $dto->description,
                'reference'        => $dto->reference,
                'transaction_date' => $dto->transactionDate,
                'notes'            => $dto->notes,
                'status'           => true,
                'created_by_oid'   => $dto->createdByOid,
                'updated_by_oid'   => $dto->createdByOid,
            ]);

            Log::info('[CreateTransactionUseCase] Completed', ['uuid' => $transaction->uuid]);

            return DTOTransactionResponse::fromEntity($transaction);
        });
    }
}
