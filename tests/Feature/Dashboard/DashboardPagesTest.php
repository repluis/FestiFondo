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

    public function test_home_has_share_transparency_button(): void
    {
        $uuid = DB::table('fund_raisings')->where('status', true)->value('uuid');

        $this->actingAs(User::first())
            ->get('/')
            ->assertSee('Compartir transparencia')
            ->assertSee(route('transparency', $uuid));
    }

    public function test_home_has_copy_text_button_with_whatsapp_message(): void
    {
        $uuid = DB::table('fund_raisings')->where('status', true)->value('uuid');
        $data = app(DashboardService::class)->transparency($uuid);

        $html = $this->actingAs(User::first())->get('/')->assertOk()->assertSee('Copiar texto')->getContent();

        preg_match('/class="dash-link copy-text-btn"[^>]*data-text="([^"]*)"/s', $html, $m);
        $text = html_entity_decode($m[1] ?? '', ENT_QUOTES);

        $this->assertStringContainsString('*Transparencia financiera*', $text);
        $this->assertStringContainsString('*Total aportado: $'.number_format($data['total'], 2).'*', $text);
        $this->assertStringContainsString($data['members'][0]['name'].' — $'.number_format($data['members'][0]['total'], 2), $text);
        $this->assertStringContainsString(route('transparency', $uuid), $text);
        $this->assertStringNotContainsString('Deuda', $text);
        $this->assertStringContainsString("\n", $text, 'El mensaje debe conservar los saltos de línea');
    }

    public function test_transparency_page_is_public_and_shows_only_names_and_paid_without_penalties(): void
    {
        $campaign = DB::table('fund_raisings')->where('status', true)->first(['oid', 'uuid']);

        // Un miembro de la campaña y lo que pagó sin mora, calculado directo en la base
        $member = DB::table('campaign_members as cm')->join('members as m', 'm.oid', '=', 'cm.member_oid')
            ->where('cm.campaign_oid', $campaign->oid)->where('cm.status', true)->where('m.status', true)
            ->first(['m.oid', 'm.first_name', 'm.last_name']);
        $expected = (float) DB::table('transactions')
            ->where('campaign_oid', $campaign->oid)->where('member_oid', $member->oid)
            ->where('transaction_type', 'income')->where('status', true)
            ->sum(DB::raw('amount - COALESCE(applied_to_penalties, 0)'));

        $this->get(route('transparency', $campaign->uuid))   // sin sesión
            ->assertOk()
            ->assertSee('Transparencia financiera')
            ->assertSee($member->first_name.' '.$member->last_name)
            ->assertSee('$'.number_format($expected, 2))
            ->assertDontSee('Deuda')
            ->assertDontSee('Cuotas pendientes')
            ->assertDontSee('Financiero');                      // sin menú del sistema
    }

    public function test_transparency_excludes_penalty_payments(): void
    {
        $data = app(DashboardService::class)->transparency(DB::table('fund_raisings')->where('status', true)->value('uuid'));

        $campaignOid = DB::table('fund_raisings')->where('status', true)->value('oid');
        $gross = (float) DB::table('transactions')->where('campaign_oid', $campaignOid)->where('transaction_type', 'income')->where('status', true)->sum('amount');
        $toPen = (float) DB::table('transactions')->where('campaign_oid', $campaignOid)->where('transaction_type', 'income')->where('status', true)->sum('applied_to_penalties');

        $this->assertEqualsWithDelta($gross - $toPen, $data['total'], 0.001);
    }

    public function test_transparency_unknown_campaign_returns_404(): void
    {
        $this->get('/transparencia/00000000-0000-0000-0000-000000000000')->assertNotFound();
        $this->get('/transparencia/no-es-uuid')->assertNotFound();
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
