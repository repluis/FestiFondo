<?php

namespace Src\FundRaising\Domain\Repositories;

use Src\FundRaising\Domain\Entities\FundRaising;

interface FundRaisingRepositoryInterface
{
    public function findByUuid(string $uuid): ?FundRaising;

    public function existsByName(string $name, ?int $excludeOid = null): bool;

    /** @return FundRaising[] */
    public function listAll(array $filters = []): array;

    public function create(array $data): FundRaising;

    public function update(int $oid, array $data): FundRaising;

    public function cancel(int $oid, int $updatedByOid): void;

    public function getDashboardMembersWithBalance(): array;
}
