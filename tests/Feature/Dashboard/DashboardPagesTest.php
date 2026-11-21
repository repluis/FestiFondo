<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Src\Dashboard\Application\Services\DashboardService;
use Tests\TestCase;

/**
 * Inicio y Dashboard contra Postgres real (transacción revertida al final).
 */
class DashboardPagesTest extends TestCase
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
        parent::tearDown();
    }

    public function test_guest_sees_public_landing_without_data(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bienvenido a')
            ->assertSee('Iniciar sesión')
            ->assertDontSee('Total recaudado')
            ->assertDontSee('Quienes más deben');
    }

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('auth.login'));
    }

    public function test_guest_menu_hides_private_sections(): void
    {
        $this->get('/')->assertDontSee('Financiero')->assertDontSee('Administrativo')->assertSee('Juegos');
    }

    public function test_home_shows_real_totals_for_users(): void
    {
        $collected = (float) DB::table('transactions')->where('transaction_type', 'income')->where('status', true)->sum('amount');

        $this->actingAs(User::first())
            ->get('/')
            ->assertOk()
            ->assertSee('Total recaudado')
            ->assertSee('$'.number_format($collected, 2))
            ->assertSee('Quienes más han pagado')
            ->assertSee('Últimos pagos');
    }

    public function test_dashboard_shows_charts_and_rankings(): void
    {
        $this->actingAs(User::first())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Recaudación por mes')
            ->assertSee('Quienes menos han pagado')
            ->assertSee('Quienes menos deben')
            ->assertSee('Estado de cuenta de todos los miembros');
    }

    public function test_figures_match_the_database(): void
    {
        $data = app(DashboardService::class)->dashboard();
        $kpis = $data['kpis'];

        $feesDue = (float) DB::table('monthly_fees')->whereIn('fee_status', ['pending', 'partial'])->sum('balance');
        $penDue  = (float) DB::table('penalties')->whereIn('penalty_status', ['pending', 'partial'])
            ->whereIn('member_oid', DB::table('members')->where('status', true)->pluck('oid'))->sum('balance');

        $this->assertEqualsWithDelta($feesDue, $kpis['fees_due'], 0.001);
        $this->assertEqualsWithDelta($penDue, $kpis['penalties_due'], 0.001);
        $this->assertSame($kpis['members'], $kpis['up_to_date'] + $kpis['with_debt']);

        // Rankings ordenados correctamente
        $paid = array_map(fn ($m) => $m->totalPaid, $data['standings']['top_payers']);
        $this->assertSame($paid, collect($paid)->sortDesc()->values()->all());

        $debt = array_map(fn ($m) => $m->totalDue(), $data['standings']['top_debtors']);
        $this->assertSame($debt, collect($debt)->sortDesc()->values()->all());
        foreach ($data['standings']['top_debtors'] as $m) {
            $this->assertGreaterThan(0, $m->totalDue(), 'En "quienes más deben" solo debe haber miembros con deuda');
        }

        // La suma mensual coincide con el total cuando todos los pagos caen en los últimos 12 meses
        $this->assertLessThanOrEqual($data['collection']['total'] + 0.001, array_sum(array_column($data['collection']['monthly'], 'total')));
    }
}
