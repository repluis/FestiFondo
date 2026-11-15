<?php

declare(strict_types=1);

namespace Tests\Feature\FundRaising;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Src\FundRaising\Application\UseCases\ProcessChargesAndPenaltiesUseCase;
use Tests\TestCase;

/**
 * Runs the real ProcessChargesAndPenaltiesUseCase against the actual configured
 * database (local Postgres), wrapped in a transaction that is rolled back after
 * every test. This exercises the real SQL (NULL handling, unique constraints,
 * date arithmetic) that a mocked repository cannot verify.
 *
 * The app boots with phpunit.xml's sqlite default; the transaction can only be
 * opened on pgsql (where the schema actually lives) after boot, so the default
 * connection is switched manually here instead of via DatabaseTransactions
 * (whose automatic setUp hook would begin the transaction before this runs).
 */
class ProcessChargesAndPenaltiesIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'pgsql']);
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ── Fixtures ─────────────────────────────────────────────────────────────

    private function createCampaign(string $name, array $overrides = []): object
    {
        $defaults = [
            'name'                => $name,
            'target_amount'       => 1000.00,
            'collected_amount'    => 0,
            'start_date'          => '2026-01-01',
            'fund_raising_status' => 'active',
            'status'              => true,
            'created_at'          => now(),
            'updated_at'          => now(),
        ];

        DB::table('fund_raisings')->insert(array_merge($defaults, $overrides));

        return DB::table('fund_raisings')->where('name', $name)->first();
    }

    private function createMember(string $identification): int
    {
        DB::table('members')->insert([
            'identification' => $identification,
            'first_name'     => 'Test',
            'last_name'      => $identification,
            'joined_at'      => '2026-01-01',
            'status'         => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return DB::table('members')->where('identification', $identification)->value('oid');
    }

    private function enroll(int $campaignOid, int $memberOid): void
    {
        DB::table('campaign_members')->insert([
            'campaign_oid' => $campaignOid,
            'member_oid'   => $memberOid,
            'enrolled_at'  => '2026-01-01',
            'status'       => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function createUnpaidFee(int $memberOid, int $campaignOid, string $dueDate, int $year, int $month): void
    {
        DB::table('monthly_fees')->insert([
            'member_oid'   => $memberOid,
            'campaign_oid' => $campaignOid,
            'period_year'  => $year,
            'period_month' => $month,
            'due_date'     => $dueDate,
            'amount'       => 1.00,
            'amount_paid'  => 0,
            'balance'      => 1.00,
            'fee_status'   => 'pending',
            'status'       => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function createPenalty(int $memberOid, int $campaignOid, string $date, int $daysOverdue): void
    {
        DB::table('penalties')->insert([
            'member_oid'          => $memberOid,
            'campaign_oid'        => $campaignOid,
            'period_year'         => (int) substr($date, 0, 4),
            'period_month'        => (int) substr($date, 5, 2),
            'penalty_date'        => $date,
            'days_overdue'        => $daysOverdue,
            'daily_rate_snapshot' => 0.05,
            'amount'              => 0.05,
            'amount_paid'         => 0,
            'balance'             => 0.05,
            'penalty_status'      => 'pending',
            'status'              => true,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    }

    private function runProcess(?string $campaignUuid = null): array
    {
        return app(ProcessChargesAndPenaltiesUseCase::class)->execute(0, $campaignUuid);
    }

    // ── 1. Regresión real: llamada global + llamada por campaña, mismo día ──

    public function test_real_db_does_not_duplicate_penalty_when_called_globally_and_then_scoped_same_day(): void
    {
        $campaign = $this->createCampaign('IT Campaign — global vs scoped');
        $memberOid = $this->createMember('IT-901');
        $this->enroll($campaign->oid, $memberOid);
        $this->createUnpaidFee($memberOid, $campaign->oid, '2026-06-15', 2026, 6);

        Carbon::setTestNow('2026-06-20'); // 5 días vencido

        $this->runProcess(null);               // botón del dashboard (sin campaign_uuid)
        $this->runProcess($campaign->uuid);    // botón de la campaña (con campaign_uuid real)

        $penalties = DB::table('penalties')->where('member_oid', $memberOid)->orderBy('penalty_date')->get();

        $this->assertCount(5, $penalties, 'No debe duplicar mora al invocar global y luego por campaña el mismo día.');
        $this->assertEquals(0.25, round($penalties->sum('balance'), 2));
        $this->assertTrue($penalties->every(fn($p) => (int) $p->campaign_oid === $campaign->oid));
    }

    // ── 2. Regresión real del bug original: mora no se congela entre meses ──

    public function test_real_db_mora_keeps_accruing_across_month_boundary(): void
    {
        $campaign  = $this->createCampaign('IT Campaign — month boundary');
        $memberOid = $this->createMember('IT-902');
        $this->enroll($campaign->oid, $memberOid);
        $this->createUnpaidFee($memberOid, $campaign->oid, '2026-06-15', 2026, 6);

        // Mora ya acumulada hasta el 19 de junio (simula ejecuciones previas)
        foreach ([16, 17, 18, 19] as $i => $day) {
            $this->createPenalty($memberOid, $campaign->oid, "2026-06-{$day}", $i + 1);
        }

        Carbon::setTestNow('2026-07-01'); // ya es julio, pero aún no llega el día 15

        $result = $this->runProcess($campaign->uuid);

        $penalties = DB::table('penalties')->where('member_oid', $memberOid)->get();
        $fees      = DB::table('monthly_fees')->where('member_oid', $memberOid)->get();

        $this->assertSame(0, $result['fees_generated'], 'No debe cobrar julio antes del día 15.');
        $this->assertCount(1, $fees, 'Solo debe existir la cuota de junio.');
        $this->assertCount(16, $penalties, '4 previas + 12 nuevas (20 jun - 1 jul).');
        $this->assertEquals(0.80, round($penalties->sum('balance'), 2));
    }

    // ── 3. Recupera los días de mora que faltaron tras un vacío de uso ──────

    public function test_real_db_backfills_missed_penalty_days(): void
    {
        $campaign  = $this->createCampaign('IT Campaign — backfill');
        $memberOid = $this->createMember('IT-903');
        $this->enroll($campaign->oid, $memberOid);
        $this->createUnpaidFee($memberOid, $campaign->oid, '2026-06-15', 2026, 6);

        Carbon::setTestNow('2026-06-20'); // el botón no se presionó desde el día del vencimiento

        $result = $this->runProcess($campaign->uuid);

        $penalties = DB::table('penalties')->where('member_oid', $memberOid)->orderBy('penalty_date')->get();

        $this->assertSame(5, $result['penalties_generated']);
        $this->assertEquals(
            ['2026-06-16', '2026-06-17', '2026-06-18', '2026-06-19', '2026-06-20'],
            $penalties->pluck('penalty_date')->map(fn($d) => (string) $d)->all()
        );
    }

    // ── 4. Aislamiento real: correr una campaña no toca otra ────────────────

    public function test_real_db_scoped_execution_does_not_affect_other_campaigns(): void
    {
        $campaignA  = $this->createCampaign('IT Campaign A — isolation');
        $memberAOid = $this->createMember('IT-904');
        $this->enroll($campaignA->oid, $memberAOid);
        $this->createUnpaidFee($memberAOid, $campaignA->oid, '2026-06-15', 2026, 6);

        $campaignB  = $this->createCampaign('IT Campaign B — isolation');
        $memberBOid = $this->createMember('IT-905');
        $this->enroll($campaignB->oid, $memberBOid);
        $this->createUnpaidFee($memberBOid, $campaignB->oid, '2026-06-15', 2026, 6);

        Carbon::setTestNow('2026-06-20');

        $result = $this->runProcess($campaignA->uuid);

        $this->assertSame(1, $result['members_processed'], 'Solo debe procesar al miembro de la campaña A.');

        $penaltiesA = DB::table('penalties')->where('member_oid', $memberAOid)->get();
        $penaltiesB = DB::table('penalties')->where('member_oid', $memberBOid)->get();
        $feesB      = DB::table('monthly_fees')->where('member_oid', $memberBOid)->get();

        $this->assertCount(5, $penaltiesA, 'La campaña A sí debe acumular su mora.');
        $this->assertCount(0, $penaltiesB, 'La campaña B no debe recibir mora al correr solo la campaña A.');
        $this->assertCount(1, $feesB, 'La cuota de la campaña B debe quedar exactamente igual (la que insertamos manualmente).');
    }
}
