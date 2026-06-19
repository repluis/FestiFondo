<?php

namespace Src\Transactions\Application\Services;

use Src\Transactions\Application\DTOs\DTOApplyPaymentRequest;
use Src\Transactions\Application\DTOs\DTOCreateTransactionRequest;
use Src\Transactions\Application\DTOs\DTOTransactionResponse;
use Src\Transactions\Application\UseCases\ApplyPaymentUseCase;
use Src\Transactions\Application\UseCases\CancelTransactionUseCase;
use Src\Transactions\Application\UseCases\CreateTransactionUseCase;
use Src\Transactions\Application\UseCases\ListTransactionsUseCase;
use Src\Transactions\Application\UseCases\ShowTransactionUseCase;

class TransactionService
{
    public function __construct(
        private readonly CreateTransactionUseCase  $createUseCase,
        private readonly ListTransactionsUseCase   $listUseCase,
        private readonly ShowTransactionUseCase    $showUseCase,
        private readonly CancelTransactionUseCase  $cancelUseCase,
        private readonly ApplyPaymentUseCase       $applyPaymentUseCase,
    ) {}

    public function create(DTOCreateTransactionRequest $dto): DTOTransactionResponse
    {
        return $this->createUseCase->execute($dto);
    }

    public function list(array $filters = []): array
    {
        return $this->listUseCase->execute($filters);
    }

    public function show(string $uuid): DTOTransactionResponse
    {
        return $this->showUseCase->execute($uuid);
    }

    public function cancel(string $uuid, int $cancelledByOid): void
    {
        $this->cancelUseCase->execute($uuid, $cancelledByOid);
    }

    public function applyPayment(DTOApplyPaymentRequest $dto): array
    {
        return $this->applyPaymentUseCase->execute($dto);
    }
}
