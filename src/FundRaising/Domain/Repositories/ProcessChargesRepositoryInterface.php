<?php

namespace Src\FundRaising\Domain\Repositories;

interface ProcessChargesRepositoryInterface
{
    public function findProcessExecution(string $processKey): ?object;
    public function resetProcessExecution(int $id): void;
    public function createProcessExecution(array $data): int;
    public function getLastProcessExecution(): ?object;

    public function getSystemConfigs(array $keys): array;

    public function getActiveMembers(): array;
    public function getActiveCampaignMembers(?string $campaignUuid = null): array;
    public function getCampaignOidByUuid(string $uuid): ?int;
    public function getCampaignFeeRates(string $uuid): array;

    public function monthlyFeeExistsForMember(int $memberOid, int $year, int $month, ?int $campaignOid = null): bool;
    public function createMonthlyFee(array $data): void;
    public function getPendingFeeBalance(int $memberOid, ?int $campaignOid = null): float;

    public function penaltyExistsForMember(int $memberOid, string $date, ?int $campaignOid = null): bool;
    public function createPenalty(array $data): void;

    public function completeProcessExecution(int $id, array $data): void;
    public function failProcessExecution(int $id, string $error): void;
}
