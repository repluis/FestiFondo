<?php

namespace Src\Members\Infrastructure\Repositories;

use Src\Members\Domain\Entities\Members;
use Src\Members\Domain\Repositories\MembersRepositoryInterface;

class EloquentMembersRepository implements MembersRepositoryInterface
{
    public function findByUuid(string $uuid): ?Members
    {
        $model = MemberModel::where('uuid', $uuid)->first();

        return $model?->toEntity();
    }

    public function existsByIdentification(string $identification, ?int $excludeOid = null): bool
    {
        return MemberModel::where('identification', $identification)
            ->when($excludeOid !== null, fn($q) => $q->where('oid', '!=', $excludeOid))
            ->exists();
    }

    public function existsByEmail(string $email, ?int $excludeOid = null): bool
    {
        return MemberModel::whereNotNull('email')
            ->where('email', $email)
            ->when($excludeOid !== null, fn($q) => $q->where('oid', '!=', $excludeOid))
            ->exists();
    }

    public function listAll(array $filters = []): array
    {
        $query = MemberModel::query();

        if (array_key_exists('status', $filters)) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . strtolower($filters['search']) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(first_name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(identification) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        return $query->orderBy('last_name')
                     ->orderBy('first_name')
                     ->get()
                     ->map(fn($m) => $m->toEntity())
                     ->all();
    }

    public function create(array $data): Members
    {
        $model = MemberModel::create($data);
        $model->refresh();

        return $model->toEntity();
    }

    public function update(int $oid, array $data): Members
    {
        MemberModel::where('oid', $oid)->update($data);

        return MemberModel::where('oid', $oid)->firstOrFail()->toEntity();
    }

    public function deactivate(int $oid, int $updatedByOid): void
    {
        MemberModel::where('oid', $oid)->update([
            'status'         => false,
            'updated_by_oid' => $updatedByOid,
        ]);
    }

    public function activate(int $oid, int $updatedByOid): void
    {
        MemberModel::where('oid', $oid)->update([
            'status'         => true,
            'updated_by_oid' => $updatedByOid,
        ]);
    }
}
