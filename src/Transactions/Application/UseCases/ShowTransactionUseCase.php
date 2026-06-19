<?php

namespace Src\Transactions\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Transactions\Application\DTOs\DTOTransactionResponse;
use Src\Transactions\Domain\Exceptions\TransactionNotFoundException;
use Src\Transactions\Domain\Repositories\TransactionRepositoryInterface;

class ShowTransactionUseCase
{
    public function __construct(
        private readonly TransactionRepositoryInterface $repository,
    ) {}

    public function execute(string $uuid): DTOTransactionResponse
    {
        Log::info('[ShowTransactionUseCase] Starting', ['uuid' => $uuid]);

        $transaction = $this->repository->findByUuid($uuid);

        if ($transaction === null) {
            throw TransactionNotFoundException::withUuid($uuid);
        }

        Log::info('[ShowTransactionUseCase] Completed', ['uuid' => $uuid]);

        return DTOTransactionResponse::fromEntity($transaction);
    }
}
