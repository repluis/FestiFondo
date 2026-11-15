<?php

declare(strict_types=1);

namespace Tests\Unit\FundRaising\Application\UseCases;

use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;
use Src\FundRaising\Application\UseCases\ProcessChargesAndPenaltiesUseCase;
use Src\FundRaising\Domain\Repositories\ProcessChargesRepositoryInterface;
use Tests\TestCase;

class ProcessChargesAndPenaltiesUseCaseTest extends TestCase
{
    private MockInterface $repository;
    private ProcessChargesAndPenaltiesUseCase $useCase;

    private const FEE_AMOUNT = 1.00;
    private const DAILY_RATE = 0.05;
    private const DUE_DAY    = 15;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(ProcessChargesRepositoryInterface::class);
        $this->useCase     = new ProcessChargesAndPenaltiesUseCase($this->repository);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();
        parent::tearDown();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function member(int $oid, ?int $campaignOid): object
    {
        return (object) ['id' => $oid, 'oid' => $oid, 'campaign_oid' => $campaignOid];
    }

    private function defaultConfigs(): array
    {
        return [
            'monthly_fee_amount' => (string) self::FEE_AMOUNT,
            'daily_penalty_rate' => (string) self::DAILY_RATE,
            'due_day'            => (string) self::DUE_DAY,
        ];
    }

    /** Stubs the process_executions bookkeeping for a single execute() call. */
    private function mockSingleRunLifecycle(): void
    {
        $this->repository->shouldReceive('findProcessExecution')->once()->andReturn(null);
        $this->repository->shouldReceive('createProcessExecution')->once()->andReturn(1);
        $this->repository->shouldReceive('completeProcessExecution')->once();
    }

    /** Stubs the process_executions bookkeeping for two sequential execute() calls (same day). */
    private function mockTwoRunsLifecycle(): void
    {
        $this->repository->shouldReceive('findProcessExecution')
            ->twice()
            ->andReturn(null, (object) ['id' => 1, 'execution_status' => 'completed']);
        $this->repository->shouldReceive('createProcessExecution')->once()->andReturn(1);
        $this->repository->shouldReceive('resetProcessExecution')->once()->with(1);
        $this->repository->shouldReceive('completeProcessExecution')->twice();
    }

    // ── 1. Cuota: no se cobra antes del día de vencimiento ──────────────────

    public function test_does_not_generate_fee_before_due_day(): void
    {
        Carbon::setTestNow('2026-07-01'); // due_day = 15, aún no llega

        $this->mockSingleRunLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->once()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getActiveCampaignMembers')->once()->with(null)
            ->andReturn([$this->member(8, 2)]);

        $this->repository->shouldNotReceive('monthlyFeeExistsForMember');
        $this->repository->shouldNotReceive('createMonthlyFee');
        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->once()->andReturn(null);

        $result = $this->useCase->execute(0, null);

        $this->assertSame(0, $result['fees_generated']);
    }

    // ── 2. Cuota: se cobra exactamente el día de vencimiento ────────────────

    public function test_generates_fee_exactly_on_due_day(): void
    {
        Carbon::setTestNow('2026-07-15');

        $this->mockSingleRunLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->once()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getActiveCampaignMembers')->once()
            ->andReturn([$this->member(8, 2)]);

        $this->repository->shouldReceive('monthlyFeeExistsForMember')
            ->once()->with(8, 2026, 7, 2)->andReturn(false);

        $captured = null;
        $this->repository->shouldReceive('createMonthlyFee')->once()
            ->with(Mockery::on(function (array $data) use (&$captured) {
                $captured = $data;
                return true;
            }));

        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->once()->andReturn(null);

        $result = $this->useCase->execute(0, null);

        $this->assertSame(1, $result['fees_generated']);
        $this->assertSame('2026-07-15', $captured['due_date']);
        $this->assertSame(self::FEE_AMOUNT, $captured['amount']);
        $this->assertSame(2, $captured['campaign_oid']);
    }

    // ── 3. Cuota: no se duplica al correr el proceso dos veces el mismo mes ─

    public function test_does_not_duplicate_fee_when_run_twice_same_month(): void
    {
        Carbon::setTestNow('2026-07-15');

        $this->mockTwoRunsLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->twice()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getActiveCampaignMembers')->twice()
            ->andReturn([$this->member(8, 2)]);

        $this->repository->shouldReceive('monthlyFeeExistsForMember')
            ->twice()->with(8, 2026, 7, 2)->andReturn(false, true);
        $this->repository->shouldReceive('createMonthlyFee')->once();

        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->twice()->andReturn(null);

        $first  = $this->useCase->execute(0, null);
        $second = $this->useCase->execute(0, null);

        $this->assertSame(1, $first['fees_generated']);
        $this->assertSame(0, $second['fees_generated']);
    }

    // ── 4. Cuota: respeta tasas por campaña y ajusta due_day al fin de mes ──

    public function test_respects_campaign_fee_rate_overrides_and_clamps_due_day_to_end_of_month(): void
    {
        Carbon::setTestNow('2026-02-28'); // 2026 no es bisiesto, febrero tiene 28 días
        $campaignUuid = 'campaign-uuid-1';

        $this->mockSingleRunLifecycle();
        $this->repository->shouldReceive('getCampaignFeeRates')->once()->with($campaignUuid)->andReturn([
            'monthly_fee_amount' => 25.00,
            'daily_penalty_rate' => 0.10,
            'due_day'            => 31, // no existe en febrero
        ]);
        $this->repository->shouldReceive('getActiveCampaignMembers')->once()->with($campaignUuid)
            ->andReturn([$this->member(8, 5)]);

        $this->repository->shouldReceive('monthlyFeeExistsForMember')->once()->andReturn(false);

        $captured = null;
        $this->repository->shouldReceive('createMonthlyFee')->once()
            ->with(Mockery::on(function (array $data) use (&$captured) {
                $captured = $data;
                return true;
            }));

        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->once()->andReturn(null);

        $this->useCase->execute(0, $campaignUuid);

        $this->assertSame('2026-02-28', $captured['due_date']);
        $this->assertSame(25.00, $captured['amount']);
        $this->assertSame(5, $captured['campaign_oid']);
    }

    // ── 5. Mora: no se genera el mismo día del vencimiento ──────────────────

    public function test_does_not_generate_penalty_on_the_due_date_itself(): void
    {
        Carbon::setTestNow('2026-07-15');

        $this->mockSingleRunLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->once()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getActiveCampaignMembers')->once()
            ->andReturn([$this->member(8, 2)]);

        $this->repository->shouldReceive('monthlyFeeExistsForMember')->once()->andReturn(true);
        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->once()->andReturn('2026-07-15');

        $this->repository->shouldNotReceive('getPendingFeeBalance');
        $this->repository->shouldNotReceive('createPenalty');

        $result = $this->useCase->execute(0, null);

        $this->assertSame(0, $result['penalties_generated']);
    }

    // ── 6. Mora: se incrementa exactamente 5 centavos por cada día vencido ──

    public function test_penalty_accrues_at_configured_daily_rate_for_each_overdue_day(): void
    {
        Carbon::setTestNow('2026-06-18'); // 3 días después del vencimiento (15)

        $this->mockSingleRunLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->once()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getActiveCampaignMembers')->once()
            ->andReturn([$this->member(8, 2)]);

        $this->repository->shouldReceive('monthlyFeeExistsForMember')->once()->andReturn(true);
        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->once()->andReturn('2026-06-15');
        $this->repository->shouldReceive('getPendingFeeBalance')->once()->andReturn(1.00);
        $this->repository->shouldReceive('getLastPenaltyDate')->once()->andReturn(null);
        $this->repository->shouldReceive('penaltyExistsForMember')->times(3)->andReturn(false);

        $captured = [];
        $this->repository->shouldReceive('createPenalty')->times(3)
            ->with(Mockery::on(function (array $data) use (&$captured) {
                $captured[] = $data;
                return true;
            }));

        $result = $this->useCase->execute(0, null);

        $this->assertSame(3, $result['penalties_generated']);
        $this->assertSame(['2026-06-16', '2026-06-17', '2026-06-18'], array_column($captured, 'penalty_date'));
        $this->assertSame([1, 2, 3], array_column($captured, 'days_overdue'));
        foreach ($captured as $row) {
            $this->assertSame(self::DAILY_RATE, $row['amount']);
            $this->assertSame(self::DAILY_RATE, $row['balance']);
        }
    }

    // ── 7. Mora: recupera los días que faltaron tras varios días sin correrlo

    public function test_backfills_all_missed_penalty_days_after_a_gap(): void
    {
        Carbon::setTestNow('2026-06-20'); // el botón no se presionó desde el día 16

        $this->mockSingleRunLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->once()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getActiveCampaignMembers')->once()
            ->andReturn([$this->member(8, 2)]);

        $this->repository->shouldReceive('monthlyFeeExistsForMember')->once()->andReturn(true);
        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->once()->andReturn('2026-06-15');
        $this->repository->shouldReceive('getPendingFeeBalance')->once()->andReturn(1.00);
        $this->repository->shouldReceive('getLastPenaltyDate')->once()->andReturn('2026-06-16');
        $this->repository->shouldReceive('penaltyExistsForMember')->times(4)->andReturn(false);

        $captured = [];
        $this->repository->shouldReceive('createPenalty')->times(4)
            ->with(Mockery::on(function (array $data) use (&$captured) {
                $captured[] = $data;
                return true;
            }));

        $result = $this->useCase->execute(0, null);

        $this->assertSame(4, $result['penalties_generated']);
        $this->assertSame(['2026-06-17', '2026-06-18', '2026-06-19', '2026-06-20'], array_column($captured, 'penalty_date'));
        $this->assertSame([2, 3, 4, 5], array_column($captured, 'days_overdue'));
    }

    // ── 8. Mora: NO se congela al cruzar de mes (bug original reportado) ────

    public function test_mora_keeps_accruing_across_month_boundary_before_new_due_date(): void
    {
        Carbon::setTestNow('2026-07-01'); // cuota de junio sin pagar, aún no llega el 15 de julio

        $this->mockSingleRunLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->once()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getActiveCampaignMembers')->once()
            ->andReturn([$this->member(8, 2)]);

        // Julio: hoy (01) es anterior al vencimiento (15) -> no se genera cuota nueva
        $this->repository->shouldNotReceive('monthlyFeeExistsForMember');
        $this->repository->shouldNotReceive('createMonthlyFee');

        // Pero la mora de junio sigue vigente y debe seguir acumulando
        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->once()->andReturn('2026-06-15');
        $this->repository->shouldReceive('getPendingFeeBalance')->once()->andReturn(1.00);
        $this->repository->shouldReceive('getLastPenaltyDate')->once()->andReturn('2026-06-19');
        $this->repository->shouldReceive('penaltyExistsForMember')->times(12)->andReturn(false);

        $captured = [];
        $this->repository->shouldReceive('createPenalty')->times(12)
            ->with(Mockery::on(function (array $data) use (&$captured) {
                $captured[] = $data;
                return true;
            }));

        $result = $this->useCase->execute(0, null);

        $this->assertSame(0, $result['fees_generated']);
        $this->assertSame(12, $result['penalties_generated']);
        $this->assertSame('2026-06-20', $captured[0]['penalty_date']);
        $this->assertSame('2026-07-01', end($captured)['penalty_date']);
    }

    // ── 9. Mora: no se duplica al correr el proceso dos veces el mismo día ──

    public function test_does_not_duplicate_penalty_when_run_twice_same_day(): void
    {
        Carbon::setTestNow('2026-06-20');

        $this->mockTwoRunsLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->twice()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getActiveCampaignMembers')->twice()
            ->andReturn([$this->member(8, 2)]);

        $this->repository->shouldReceive('monthlyFeeExistsForMember')->twice()->andReturn(true);
        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->twice()->andReturn('2026-06-15');
        $this->repository->shouldReceive('getPendingFeeBalance')->twice()->andReturn(1.00);

        // 1ra corrida: no hay mora previa -> genera 16,17,18,19,20 (5 días)
        // 2da corrida: ya existe mora hasta el 20 -> no queda ningún día por generar
        $this->repository->shouldReceive('getLastPenaltyDate')->twice()->andReturn(null, '2026-06-20');
        $this->repository->shouldReceive('penaltyExistsForMember')->times(5)->andReturn(false);
        $this->repository->shouldReceive('createPenalty')->times(5);

        $first  = $this->useCase->execute(0, null);
        $second = $this->useCase->execute(0, null);

        $this->assertSame(5, $first['penalties_generated']);
        $this->assertSame(0, $second['penalties_generated']);
    }

    // ── 10. Mora: no se genera si el miembro ya pagó todo ───────────────────

    public function test_does_not_generate_penalty_when_member_has_no_pending_balance(): void
    {
        Carbon::setTestNow('2026-07-20'); // muy vencido, pero ya no debe nada

        $this->mockSingleRunLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->once()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getActiveCampaignMembers')->once()
            ->andReturn([$this->member(8, 2)]);

        $this->repository->shouldReceive('monthlyFeeExistsForMember')->once()->andReturn(true);
        // Sin cuotas pending/partial -> no hay fecha de vencimiento pendiente
        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->once()->andReturn(null);

        $this->repository->shouldNotReceive('getPendingFeeBalance');
        $this->repository->shouldNotReceive('createPenalty');

        $result = $this->useCase->execute(0, null);

        $this->assertSame(0, $result['penalties_generated']);
    }

    // ── 11. Regresión: no duplica al llamar sin campaign_uuid y luego con él

    public function test_uses_member_actual_campaign_oid_regardless_of_global_or_scoped_invocation(): void
    {
        Carbon::setTestNow('2026-07-15');

        $this->mockTwoRunsLifecycle();
        $this->repository->shouldReceive('getSystemConfigs')->once()->andReturn($this->defaultConfigs());
        $this->repository->shouldReceive('getCampaignFeeRates')->once()->with('campaign-uuid-2')->andReturn([
            'monthly_fee_amount' => self::FEE_AMOUNT,
            'daily_penalty_rate' => self::DAILY_RATE,
            'due_day'            => self::DUE_DAY,
        ]);

        $this->repository->shouldReceive('getActiveCampaignMembers')->once()->with(null)
            ->andReturn([$this->member(8, 2)]);
        $this->repository->shouldReceive('getActiveCampaignMembers')->once()->with('campaign-uuid-2')
            ->andReturn([$this->member(8, 2)]);

        // Clave: ambas invocaciones deben consultar con campaign_oid = 2 (el real del miembro),
        // nunca null, sin importar si la llamada fue global o específica de campaña.
        $this->repository->shouldReceive('monthlyFeeExistsForMember')
            ->twice()->with(8, 2026, 7, 2)->andReturn(false, true);
        $this->repository->shouldReceive('createMonthlyFee')->once()
            ->with(Mockery::on(fn(array $d) => $d['campaign_oid'] === 2));

        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->twice()->with(8, 2)->andReturn(null);

        $global = $this->useCase->execute(0, null);
        $scoped = $this->useCase->execute(0, 'campaign-uuid-2');

        $this->assertSame(1, $global['fees_generated']);
        $this->assertSame(0, $scoped['fees_generated']);
    }

    // ── 12. Guarda: no permite correr si ya hay un proceso "running" ────────

    public function test_throws_when_process_already_running(): void
    {
        Carbon::setTestNow('2026-07-01');

        $this->repository->shouldReceive('findProcessExecution')->once()
            ->andReturn((object) ['id' => 1, 'execution_status' => 'running']);
        $this->repository->shouldNotReceive('createProcessExecution');
        $this->repository->shouldNotReceive('getActiveCampaignMembers');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Process is currently running. Please wait and try again in a few moments.');

        $this->useCase->execute(0, null);
    }

    // ── 13. Aislamiento: al correr por campaña, no toca datos de otras ──────

    public function test_scoped_execution_only_processes_members_of_the_specified_campaign(): void
    {
        Carbon::setTestNow('2026-07-15');

        $this->mockSingleRunLifecycle();
        $this->repository->shouldReceive('getCampaignFeeRates')->once()->with('campaign-a-uuid')->andReturn([
            'monthly_fee_amount' => self::FEE_AMOUNT,
            'daily_penalty_rate' => self::DAILY_RATE,
            'due_day'            => self::DUE_DAY,
        ]);

        // El repositorio solo debe ser consultado por el uuid de la campaña A;
        // un miembro de la campaña B (oid 99, campaign_oid 7) nunca debe aparecer aquí.
        $this->repository->shouldReceive('getActiveCampaignMembers')
            ->once()->with('campaign-a-uuid')
            ->andReturn([$this->member(8, 2)]);

        $this->repository->shouldReceive('monthlyFeeExistsForMember')->once()->with(8, 2026, 7, 2)->andReturn(false);
        $this->repository->shouldReceive('createMonthlyFee')->once()
            ->with(Mockery::on(fn(array $d) => $d['member_oid'] === 8 && $d['campaign_oid'] === 2));
        $this->repository->shouldReceive('getOldestUnpaidFeeDueDate')->once()->with(8, 2)->andReturn(null);

        // Ninguna llamada debe hacerse jamás con datos de la campaña B (oid 99 / campaign_oid 7)
        $this->repository->shouldNotReceive('monthlyFeeExistsForMember')->with(99, Mockery::any(), Mockery::any(), 7);
        $this->repository->shouldNotReceive('createMonthlyFee')->with(Mockery::on(fn(array $d) => $d['campaign_oid'] === 7));
        $this->repository->shouldNotReceive('getOldestUnpaidFeeDueDate')->with(99, 7);
        $this->repository->shouldNotReceive('createPenalty')->with(Mockery::on(fn(array $d) => $d['campaign_oid'] === 7));

        $result = $this->useCase->execute(0, 'campaign-a-uuid');

        $this->assertSame(1, $result['members_processed']);
        $this->assertSame(1, $result['fees_generated']);
    }
}
