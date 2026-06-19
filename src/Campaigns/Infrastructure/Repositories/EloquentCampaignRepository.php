<?php

namespace Src\Campaigns\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Src\Campaigns\Domain\Entities\Campaign;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class EloquentCampaignRepository implements CampaignRepositoryInterface
{
    public function findByUuid(string $uuid): ?Campaign
    {
        $model = CampaignModel::where('uuid', $uuid)->first();

        return $model?->toEntity();
    }

    public function existsByName(string $name, ?int $excludeOid = null): bool
    {
        return CampaignModel::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->when($excludeOid !== null, fn($q) => $q->where('oid', '!=', $excludeOid))
            ->exists();
    }

    public function listAll(array $filters = []): array
    {
        $query = CampaignModel::query();

        if (array_key_exists('status', $filters)) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['campaign_status'])) {
            $query->where('fund_raising_status', $filters['campaign_status']);
        }

        if (!empty($filters['search'])) {
            $term = '%' . strtolower($filters['search']) . '%';
            $query->whereRaw('LOWER(name) LIKE ?', [$term]);
        }

        return $query->orderBy('start_date', 'desc')
                     ->get()
                     ->map(fn($m) => $m->toEntity())
                     ->all();
    }

    public function create(array $data): Campaign
    {
        $model = CampaignModel::create($data);
        $model->refresh();

        return $model->toEntity();
    }

    public function update(int $oid, array $data): Campaign
    {
        CampaignModel::where('oid', $oid)->update($data);

        return CampaignModel::where('oid', $oid)->firstOrFail()->toEntity();
    }

    public function cancel(int $oid, int $updatedByOid): void
    {
        CampaignModel::where('oid', $oid)->update([
            'fund_raising_status' => 'cancelled',
            'status'              => false,
            'updated_by_oid'      => $updatedByOid,
        ]);
    }

    public function getLastProcessExecution(?string $campaignUuid = null): ?\stdClass
    {
        if (!Schema::hasTable('process_executions')) {
            return null;
        }

        $processName = $campaignUuid
            ? 'fees_and_penalties_campaign_' . substr($campaignUuid, 0, 8)
            : 'fees_and_penalties';

        return DB::table('process_executions')
            ->where('process_name', $processName)
            ->orderByDesc('execution_date')
            ->orderByDesc('id')
            ->first();
    }
}
