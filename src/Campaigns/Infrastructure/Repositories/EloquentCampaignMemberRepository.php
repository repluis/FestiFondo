<?php

namespace Src\Campaigns\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Src\Campaigns\Domain\Entities\CampaignMember;
use Src\Campaigns\Domain\Repositories\CampaignMemberRepositoryInterface;

class EloquentCampaignMemberRepository implements CampaignMemberRepositoryInterface
{
    public function enroll(array $data): CampaignMember
    {
        $model = CampaignMemberModel::create($data);
        $model->refresh();

        return $model->toEntity();
    }

    public function findByUuid(string $uuid): ?CampaignMember
    {
        $model = CampaignMemberModel::where('uuid', $uuid)->first();

        return $model?->toEntity();
    }

    public function findActiveByCampaignAndMember(int $campaignOid, int $memberOid): ?CampaignMember
    {
        $model = CampaignMemberModel::where('campaign_oid', $campaignOid)
            ->where('member_oid', $memberOid)
            ->where('status', true)
            ->first();

        return $model?->toEntity();
    }

    /** @return CampaignMember[] */
    public function listByCampaign(int $campaignOid): array
    {
        return CampaignMemberModel::where('campaign_oid', $campaignOid)
            ->where('status', true)
            ->orderBy('enrolled_at')
            ->get()
            ->map(fn($m) => $m->toEntity())
            ->all();
    }

    public function listMembersWithBalance(int $campaignOid): array
    {
        $hasFees      = Schema::hasTable('monthly_fees');
        $hasPenalties = Schema::hasTable('penalties');
        $hasTx        = Schema::hasTable('transactions')
            && Schema::hasColumn('transactions', 'campaign_oid')
            && Schema::hasColumn('transactions', 'transaction_type');

        $query = DB::table('campaign_members AS cm')
            ->join('members AS m', 'm.oid', '=', 'cm.member_oid')
            ->where('cm.campaign_oid', $campaignOid)
            ->where('cm.status', true)
            ->where('m.status', true);

        if ($hasFees) {
            $campaignOidFilter = Schema::hasColumn('monthly_fees', 'campaign_oid')
                ? "AND campaign_oid = {$campaignOid}"
                : '';
            $query->leftJoin(
                DB::raw("(SELECT member_oid,
                                  COALESCE(SUM(balance), 0) AS fees_balance
                           FROM monthly_fees
                           WHERE fee_status IN ('pending','partial')
                           {$campaignOidFilter}
                           GROUP BY member_oid) mf"),
                'mf.member_oid', '=', 'cm.member_oid'
            );
        }

        if ($hasPenalties) {
            $campaignOidFilter = Schema::hasColumn('penalties', 'campaign_oid')
                ? "AND campaign_oid = {$campaignOid}"
                : '';
            $query->leftJoin(
                DB::raw("(SELECT member_oid,
                                  COALESCE(SUM(balance), 0)     AS penalties_balance,
                                  COALESCE(SUM(amount_paid), 0) AS penalties_paid
                           FROM penalties
                           WHERE status = true
                           {$campaignOidFilter}
                           GROUP BY member_oid) pe"),
                'pe.member_oid', '=', 'cm.member_oid'
            );
        }

        if ($hasTx) {
            $query->leftJoin(
                DB::raw("(SELECT member_oid,
                                  COALESCE(SUM(amount), 0) AS total_paid_in_campaign,
                                  MAX(transaction_date)    AS last_payment_date
                           FROM transactions
                           WHERE transaction_type = 'income'
                             AND status = true
                             AND campaign_oid = {$campaignOid}
                           GROUP BY member_oid) tx"),
                'tx.member_oid', '=', 'cm.member_oid'
            );
        }

        return $query->select([
            'cm.uuid AS cm_uuid',
            'cm.oid  AS cm_oid',
            'm.oid   AS member_oid',
            'm.uuid  AS member_uuid',
            'm.identification',
            'm.first_name',
            'm.last_name',
            $hasFees      ? DB::raw('COALESCE(mf.fees_balance, 0) AS fees_balance')          : DB::raw('0 AS fees_balance'),
            $hasPenalties ? DB::raw('COALESCE(pe.penalties_balance, 0) AS penalties_balance') : DB::raw('0 AS penalties_balance'),
            $hasPenalties ? DB::raw('COALESCE(pe.penalties_paid, 0) AS penalties_paid')       : DB::raw('0 AS penalties_paid'),
            ($hasFees && $hasPenalties)
                ? DB::raw('COALESCE(mf.fees_balance, 0) + COALESCE(pe.penalties_balance, 0) AS total_balance')
                : DB::raw('0 AS total_balance'),
            $hasTx ? DB::raw('COALESCE(tx.total_paid_in_campaign, 0) AS total_paid_in_campaign') : DB::raw('0 AS total_paid_in_campaign'),
            $hasTx ? DB::raw('tx.last_payment_date')                                              : DB::raw('NULL AS last_payment_date'),
        ])
        ->orderBy('m.first_name')
        ->get()
        ->map(fn($r) => (array) $r)
        ->all();
    }

    public function remove(int $oid, int $updatedByOid): void
    {
        CampaignMemberModel::where('oid', $oid)->update([
            'status'         => false,
            'updated_by_oid' => $updatedByOid,
        ]);
    }

    public function memberTransactions(int $campaignOid, int $memberOid): array
    {
        if (!Schema::hasTable('transactions')) {
            return [];
        }

        $hasCampaignOid = Schema::hasColumn('transactions', 'campaign_oid');

        $query = DB::table('transactions AS t')
            ->where('t.member_oid', $memberOid)
            ->where('t.status', true)
            ->orderByDesc('t.transaction_date')
            ->orderByDesc('t.id');

        if ($hasCampaignOid) {
            $query->where('t.campaign_oid', $campaignOid);
        }

        $cols = [
            't.id',
            't.transaction_date',
            't.amount',
            't.notes',
        ];

        if (Schema::hasColumn('transactions', 'transaction_type')) {
            $cols[] = 't.transaction_type';
        }
        if (Schema::hasColumn('transactions', 'description')) {
            $cols[] = 't.description';
        }

        return $query->select($cols)
            ->get()
            ->map(fn($r) => (array) $r)
            ->all();
    }

    public function availableMembers(int $campaignOid): array
    {
        $enrolledOids = CampaignMemberModel::where('campaign_oid', $campaignOid)
            ->where('status', true)
            ->pluck('member_oid')
            ->toArray();

        return DB::table('members')
            ->where('status', true)
            ->when(!empty($enrolledOids), fn($q) => $q->whereNotIn('oid', $enrolledOids))
            ->orderBy('first_name')
            ->select(['oid', 'uuid', 'identification', 'first_name', 'last_name'])
            ->get()
            ->map(fn($r) => (array) $r)
            ->all();
    }
}
