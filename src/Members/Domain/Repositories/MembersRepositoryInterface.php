<?php

namespace Src\Members\Domain\Repositories;

use Src\Members\Domain\Entities\Members;

interface MembersRepositoryInterface
{
    public function findByUuid(string $uuid): ?Members;

    public function existsByIdentification(string $identification, ?int $excludeOid = null): bool;

    public function existsByEmail(string $email, ?int $excludeOid = null): bool;

    /** @return Members[] */
    public function listAll(array $filters = []): array;

    public function create(array $data): Members;

    public function update(int $oid, array $data): Members;

    public function deactivate(int $oid, int $updatedByOid): void;

    public function activate(int $oid, int $updatedByOid): void;
}
