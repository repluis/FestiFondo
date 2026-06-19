<?php

namespace Src\Transactions\Domain\Repositories;

use Src\Transactions\Domain\Entities\Transaction;

interface TransactionRepositoryInterface
{
    public function findByUuid(string $uuid): ?Transaction;
    public function listAll(array $filters = []): array;
    public function create(array $data): Transaction;
    public function cancel(int $oid, int $updatedByOid): void;

    public function getPendingPenaltiesByMember(int $memberOid): array;
    public function applyPaymentToPenalty(int $penaltyId, float $amountPaid, float $newBalance): void;
    public function getPendingFeesBalance(int $memberOid): float;
    public function getPendingFeesByMember(int $memberOid): array;
    public function applyPaymentToFee(int $feeId, float $amountPaid, float $newBalance): void;
    public function incrementCampaignCollected(int $campaignOid, float $amount): void;

    public function getPendingPenaltiesBalance(int $memberOid): float;
    public function updateTransactionAuditSnapshot(string $uuid, array $fields): void;
    public function createTransactionDetail(array $data): void;
}
