<?php

namespace Src\Games\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Src\Games\Domain\Repositories\GamesRepositoryInterface;

class EloquentGamesRepository implements GamesRepositoryInterface
{
    public function getCampaigns(): array
    {
        return DB::table('fund_raisings')
            ->where('status', true)
            ->select(['uuid', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['uuid' => $c->uuid, 'name' => $c->name])
            ->all();
    }

    public function getCampaignOidByUuid(string $uuid): ?int
    {
        $oid = DB::table('fund_raisings')->where('uuid', $uuid)->value('oid');

        return $oid !== null ? (int) $oid : null;
    }

    public function getCampaignMembers(string $campaignUuid): array
    {
        return DB::table('members as m')
            ->join('campaign_members as cm', 'cm.member_oid', '=', 'm.oid')
            ->join('fund_raisings as fr', 'fr.oid', '=', 'cm.campaign_oid')
            ->where('fr.uuid', $campaignUuid)
            ->where('m.status', true)
            ->where('cm.status', true)
            ->select(['m.oid', 'm.first_name', 'm.last_name'])
            ->distinct()
            ->orderBy('m.first_name')
            ->get()
            ->map(fn ($m) => [
                'oid'  => (int) $m->oid,
                'name' => trim($m->first_name . ' ' . $m->last_name),
            ])
            ->all();
    }

    public function createDraw(int $campaignOid, ?int $createdByOid, array $assignments): string
    {
        return DB::transaction(function () use ($campaignOid, $createdByOid, $assignments) {
            $now = now();

            // Un sorteo nuevo reemplaza al anterior: sus enlaces dejan de funcionar
            DB::table('secret_santa_draws')
                ->where('campaign_oid', $campaignOid)
                ->where('status', true)
                ->update(['status' => false, 'updated_at' => $now]);

            $drawId = DB::table('secret_santa_draws')->insertGetId([
                'campaign_oid'   => $campaignOid,
                'created_by_oid' => $createdByOid,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            DB::table('secret_santa_assignments')->insert(array_map(fn ($a) => [
                'draw_id'      => $drawId,
                'giver_oid'    => $a['giver_oid'],
                'receiver_oid' => $a['receiver_oid'],
                'token'        => $a['token'],
                'created_at'   => $now,
                'updated_at'   => $now,
            ], $assignments));

            return DB::table('secret_santa_draws')->where('id', $drawId)->value('uuid');
        });
    }

    public function getLatestDraw(string $campaignUuid): ?array
    {
        $uuid = DB::table('secret_santa_draws as d')
            ->join('fund_raisings as fr', 'fr.oid', '=', 'd.campaign_oid')
            ->where('fr.uuid', $campaignUuid)
            ->where('d.status', true)
            ->orderByDesc('d.id')
            ->value('d.uuid');

        return $uuid ? $this->getDrawByUuid($uuid) : null;
    }

    public function getDrawByUuid(string $drawUuid): ?array
    {
        $draw = DB::table('secret_santa_draws as d')
            ->join('fund_raisings as fr', 'fr.oid', '=', 'd.campaign_oid')
            ->where('d.uuid', $drawUuid)
            ->select(['d.id', 'd.uuid', 'd.status', 'd.created_at', 'fr.uuid as campaign_uuid', 'fr.name as campaign_name'])
            ->first();

        if (!$draw) {
            return null;
        }

        $assignments = DB::table('secret_santa_assignments as a')
            ->join('members as g', 'g.oid', '=', 'a.giver_oid')
            ->where('a.draw_id', $draw->id)
            ->orderBy('g.first_name')
            ->select(['a.token', 'a.viewed_at', 'g.first_name', 'g.last_name'])
            ->get()
            ->map(fn ($a) => [
                'giver_name' => trim($a->first_name . ' ' . $a->last_name),
                'token'      => $a->token,
                'viewed_at'  => $a->viewed_at,
            ])
            ->all();

        return [
            'uuid'          => $draw->uuid,
            'active'        => (bool) $draw->status,
            'created_at'    => (string) $draw->created_at,
            'campaign_uuid' => $draw->campaign_uuid,
            'campaign_name' => $draw->campaign_name,
            'assignments'   => $assignments,
        ];
    }

    public function listDraws(int $limit = 30): array
    {
        return DB::table('secret_santa_draws as d')
            ->join('fund_raisings as fr', 'fr.oid', '=', 'd.campaign_oid')
            ->leftJoin('secret_santa_assignments as a', 'a.draw_id', '=', 'd.id')
            ->groupBy('d.id', 'd.uuid', 'd.status', 'd.created_at', 'fr.uuid', 'fr.name')
            ->orderByDesc('d.id')
            ->limit($limit)
            ->select([
                'd.uuid', 'd.status', 'd.created_at',
                'fr.uuid as campaign_uuid', 'fr.name as campaign_name',
                DB::raw('COUNT(a.id) as participants'),
                DB::raw('COUNT(a.viewed_at) as viewed'),
            ])
            ->get()
            ->map(fn ($d) => [
                'uuid'          => $d->uuid,
                'active'        => (bool) $d->status,
                'created_at'    => (string) $d->created_at,
                'campaign_uuid' => $d->campaign_uuid,
                'campaign_name' => $d->campaign_name,
                'participants'  => (int) $d->participants,
                'viewed'        => (int) $d->viewed,
            ])
            ->all();
    }

    public function getExclusions(int $campaignOid): array
    {
        return DB::table('secret_santa_exclusions')
            ->where('campaign_oid', $campaignOid)
            ->where('status', true)
            ->orderBy('id')
            ->get(['giver_oid', 'receiver_oid'])
            ->map(fn ($e) => ['giver' => (int) $e->giver_oid, 'receiver' => (int) $e->receiver_oid])
            ->all();
    }

    public function addExclusions(int $campaignOid, array $pairs, ?int $createdByOid): void
    {
        $now = now();

        DB::table('secret_santa_exclusions')->insertOrIgnore(array_map(fn ($p) => [
            'campaign_oid'   => $campaignOid,
            'giver_oid'      => $p['giver'],
            'receiver_oid'   => $p['receiver'],
            'created_by_oid' => $createdByOid,
            'created_at'     => $now,
            'updated_at'     => $now,
        ], $pairs));
    }

    public function removeExclusion(int $campaignOid, int $giverOid, int $receiverOid): void
    {
        DB::table('secret_santa_exclusions')
            ->where('campaign_oid', $campaignOid)
            ->where('giver_oid', $giverOid)
            ->where('receiver_oid', $receiverOid)
            ->delete();
    }

    public function findAssignmentByToken(string $token): ?array
    {
        $row = DB::table('secret_santa_assignments as a')
            ->join('secret_santa_draws as d', 'd.id', '=', 'a.draw_id')
            ->join('members as g', 'g.oid', '=', 'a.giver_oid')
            ->join('members as r', 'r.oid', '=', 'a.receiver_oid')
            ->where('a.token', $token)
            ->where('a.status', true)
            ->where('d.status', true)
            ->select([
                'a.viewed_at',
                'g.first_name as g_first', 'g.last_name as g_last',
                'r.first_name as r_first', 'r.last_name as r_last',
            ])
            ->first();

        if (!$row) {
            return null;
        }

        return [
            'giver_name'    => trim($row->g_first . ' ' . $row->g_last),
            'receiver_name' => trim($row->r_first . ' ' . $row->r_last),
            'viewed_at'     => $row->viewed_at,
        ];
    }

    public function markAssignmentViewed(string $token): bool
    {
        // Update condicional: si dos pestañas abren a la vez, solo una lo logra
        return DB::table('secret_santa_assignments')
            ->where('token', $token)
            ->whereNull('viewed_at')
            ->update(['viewed_at' => now(), 'updated_at' => now()]) === 1;
    }

    public function resetAssignmentView(string $token): bool
    {
        return DB::table('secret_santa_assignments')
            ->where('token', $token)
            ->update(['viewed_at' => null, 'updated_at' => now()]) === 1;
    }
}
