<?php

namespace Src\Dashboard\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Src\Dashboard\Domain\Entities\CampaignProgress;
use Src\Dashboard\Domain\Entities\MemberStanding;
use Src\Dashboard\Domain\Repositories\DashboardRepositoryInterface;

class EloquentDashboardRepository implements DashboardRepositoryInterface
{
    private const OPEN_STATUSES = ['pending', 'partial'];

    public function getMemberStandings(): array
    {
        $paid = DB::table('transactions')
            ->where('transaction_type', 'income')
            ->where('status', true)
            ->groupBy('member_oid')
            ->select([
                'member_oid',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as cnt'),
                DB::raw('MAX(transaction_date) as last_payment'),
            ]);

        $fees = DB::table('monthly_fees')
            ->whereIn('fee_status', self::OPEN_STATUSES)
            ->groupBy('member_oid')
            ->select(['member_oid', DB::raw('SUM(balance) as due'), DB::raw('COUNT(*) as months')]);

        $penalties = DB::table('penalties')
            ->whereIn('penalty_status', self::OPEN_STATUSES)
            ->groupBy('member_oid')
            ->select(['member_oid', DB::raw('SUM(balance) as due')]);

        return DB::table('members as m')
            ->leftJoinSub($paid, 'tp', 'tp.member_oid', '=', 'm.oid')
            ->leftJoinSub($fees, 'f', 'f.member_oid', '=', 'm.oid')
            ->leftJoinSub($penalties, 'p', 'p.member_oid', '=', 'm.oid')
            ->where('m.status', true)
            ->orderBy('m.first_name')
            ->select([
                'm.oid', 'm.first_name', 'm.last_name',
                'tp.total', 'tp.cnt', 'tp.last_payment',
                'f.due as fees_due', 'f.months',
                'p.due as penalties_due',
            ])
            ->get()
            ->map(fn ($r) => new MemberStanding(
                memberOid:       (int) $r->oid,
                name:            trim($r->first_name . ' ' . $r->last_name),
                totalPaid:       round((float) ($r->total ?? 0), 2),
                paymentsCount:   (int) ($r->cnt ?? 0),
                lastPaymentDate: $r->last_payment ? substr((string) $r->last_payment, 0, 10) : null,
                feesDue:         round((float) ($r->fees_due ?? 0), 2),
                monthsDue:       (int) ($r->months ?? 0),
                penaltiesDue:    round((float) ($r->penalties_due ?? 0), 2),
            ))
            ->all();
    }

    public function getCampaignsProgress(): array
    {
        $members = DB::table('campaign_members')
            ->where('status', true)
            ->groupBy('campaign_oid')
            ->select(['campaign_oid', DB::raw('COUNT(*) as cnt')]);

        return DB::table('fund_raisings as fr')
            ->leftJoinSub($members, 'cm', 'cm.campaign_oid', '=', 'fr.oid')
            ->where('fr.status', true)
            ->orderByDesc('fr.start_date')
            ->select([
                'fr.uuid', 'fr.name', 'fr.fund_raising_status', 'fr.target_amount', 'fr.collected_amount',
                'fr.start_date', 'fr.end_date', 'fr.monthly_fee_amount', 'cm.cnt',
            ])
            ->get()
            ->map(fn ($c) => new CampaignProgress(
                uuid:            $c->uuid,
                name:            $c->name,
                statusLabel:     $c->fund_raising_status ?? 'draft',
                targetAmount:    (float) ($c->target_amount ?? 0),
                collectedAmount: (float) ($c->collected_amount ?? 0),
                startDate:       $c->start_date ? substr((string) $c->start_date, 0, 10) : null,
                endDate:         $c->end_date ? substr((string) $c->end_date, 0, 10) : null,
                membersCount:    (int) ($c->cnt ?? 0),
                monthlyFee:      (float) ($c->monthly_fee_amount ?? 0),
            ))
            ->all();
    }

    public function getValidPayments(): array
    {
        return DB::table('transactions')
            ->where('transaction_type', 'income')
            ->where('status', true)
            ->orderBy('transaction_date')
            ->get(['transaction_date', 'amount', 'applied_to_fees', 'applied_to_penalties'])
            ->map(fn ($t) => [
                'date'         => substr((string) $t->transaction_date, 0, 10),
                'amount'       => (float) $t->amount,
                'to_fees'      => (float) ($t->applied_to_fees ?? 0),
                'to_penalties' => (float) ($t->applied_to_penalties ?? 0),
            ])
            ->all();
    }

    public function getCampaignTransparency(string $campaignUuid): ?array
    {
        $campaign = DB::table('fund_raisings')
            ->where('uuid', $campaignUuid)
            ->where('status', true)
            ->first(['oid', 'name']);

        if (!$campaign) {
            return null;
        }

        // Lo pagado en esta campaña menos lo que se fue a mora
        $paid = DB::table('transactions')
            ->where('campaign_oid', $campaign->oid)
            ->where('transaction_type', 'income')
            ->where('status', true)
            ->groupBy('member_oid')
            ->select([
                'member_oid',
                DB::raw('SUM(amount - COALESCE(applied_to_penalties, 0)) as total'),
            ]);

        $members = DB::table('campaign_members as cm')
            ->join('members as m', 'm.oid', '=', 'cm.member_oid')
            ->leftJoinSub($paid, 'p', 'p.member_oid', '=', 'm.oid')
            ->where('cm.campaign_oid', $campaign->oid)
            ->where('cm.status', true)
            ->where('m.status', true)
            ->orderBy('m.first_name')
            ->orderBy('m.last_name')
            ->get(['m.first_name', 'm.last_name', 'p.total'])
            ->map(fn ($r) => [
                'name'  => trim($r->first_name . ' ' . $r->last_name),
                'total' => max(0.0, round((float) ($r->total ?? 0), 2)),
            ])
            ->all();

        return ['campaign' => $campaign->name, 'members' => $members];
    }

    public function getRecentPayments(int $limit): array
    {
        return DB::table('transactions as t')
            ->join('members as m', 'm.oid', '=', 't.member_oid')
            ->leftJoin('fund_raisings as fr', 'fr.oid', '=', 't.campaign_oid')
            ->where('t.transaction_type', 'income')
            ->where('t.status', true)
            ->orderByDesc('t.transaction_date')
            ->orderByDesc('t.id')
            ->limit($limit)
            ->get(['t.transaction_date', 't.amount', 't.applied_to_fees', 't.applied_to_penalties', 'm.first_name', 'm.last_name', 'fr.name as campaign'])
            ->map(fn ($t) => [
                'date'         => substr((string) $t->transaction_date, 0, 10),
                'member'       => trim($t->first_name . ' ' . $t->last_name),
                'amount'       => (float) $t->amount,
                'to_fees'      => (float) ($t->applied_to_fees ?? 0),
                'to_penalties' => (float) ($t->applied_to_penalties ?? 0),
                'campaign'     => $t->campaign,
            ])
            ->all();
    }
}
