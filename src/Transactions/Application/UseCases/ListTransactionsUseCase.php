<?php

namespace Src\Transactions\Application\UseCases;

use Illuminate\Support\Facades\Log;
use Src\Transactions\Application\DTOs\DTOTransactionResponse;
use Src\Transactions\Domain\Repositories\TransactionRepositoryInterface;

class ListTransactionsUseCase
{
    public function __construct(
        private readonly TransactionRepositoryInterface $repository,
    ) {}

    public function execute(array $filters = []): array
    {
        Log::info('[ListTransactionsUseCase] Starting', ['filters' => $filters]);

        $transactions = array_map(
            fn($t) => DTOTransactionResponse::fromEntity($t),
            $this->repository->listAll($filters),
        );

        Log::info('[ListTransactionsUseCase] Completed', ['count' => count($transactions)]);

        return $transactions;
    }
}
