<?php

namespace Src\Campaigns\Domain\Repositories;

use Src\Campaigns\Domain\Entities\Campaign;

interface CampaignRepositoryInterface
{
    public function findByUuid(string $uuid): ?Campaign;

    public function existsByName(string $name, ?int $excludeOid = null): bool;

    /** @return Campaign[] */
    public function listAll(array $filters = []): array;

    public function create(array $data): Campaign;

    public function update(int $oid, array $data): Campaign;

    public function cancel(int $oid, int $updatedByOid): void;

    public function getLastProcessExecution(?string $campaignUuid = null): ?\stdClass;
}
