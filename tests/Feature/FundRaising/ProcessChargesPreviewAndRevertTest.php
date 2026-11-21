<?php

declare(strict_types=1);

namespace Tests\Feature\FundRaising;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Vista previa (no guarda), guardado atómico y restablecer de "Actualizar Cobros y Moras".
 * Corre contra Postgres real dentro de una transacción que se revierte al final.
 */
class ProcessChargesPreviewAndRevertTest extends TestCase
{
    private object $campaign;
    private int    $memberOid;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'pgsql']);
        DB::beginTransaction();
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->actingAs(User::first());

        // Hoy = 20/12/2026; la cuota vence el 15 → 1 cuota nueva + moras del 16 al 20 (5)
        Carbon::setTestNow('2026-12-20 10:00:00');

        DB::table('fund_raisings')->insert([
            'name' => 'test_preview_revert', 'target_amount' => 100, 'collected_amount' => 0,
            'start_date' => '2026-12-01', 'fund_raising_status' => 'active', 'status' => true,
            'monthly_fee_amount' => 1.00, 'daily_penalty_rate' => 0.05, 'due_day' => 15,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->campaign = DB::table('fund_raisings')->where('name', 'test_preview_revert')->first();

        DB::table('members')->insert([
            'identification' => 'TPR-1', 'first_name' => 'Prueba', 'last_name' => 'Revert',
            'joined_at' => '2026-12-01', 'status' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->memberOid = (int) DB::table('members')->where('identification', 'TPR-1')->value('oid');

        DB::table('campaign_members')->insert([
            'campaign_oid' => $this->campaign->oid, 'member_oid' => $this->memberOid,
            'enrolled_at' => '2026-12-01', 'status' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::rollBack();
        parent::tearDown();
    }

    private function counts(): array
    {
        return [
            'fees'      => DB::table('monthly_fees')->where('member_oid', $this->memberOid)->count(),
            'penalties' => DB::table('penalties')->where('member_oid', $this->memberOid)->count(),
        ];
    }

    private function save(): array
    {
        return $this->postJson('/v1/financial/fund-raising/process-charges', ['campaign_uuid' => $this->campaign->uuid])
            ->assertOk()->json('data');
    }

    public function test_preview_shows_what_would_be_created_but_saves_nothing(): void
    {
        $data = $this->postJson('/v1/financial/fund-raising/process-charges/preview', ['campaign_uuid' => $this->campaign->uuid])
            ->assertOk()->json('data');

        $this->assertSame(1, $data['fees_generated']);
        $this->assertSame(5, $data['penalties_generated']);
        $this->assertEqualsWithDelta(0.25, $data['penalties_amount'], 0.001);
        $this->assertSame('Prueba Revert', $data['details'][0]['member_name']);
        $this->assertSame('2026-12-16', $data['details'][0]['penalties_from']);
        $this->assertSame('2026-12-20', $data['details'][0]['penalties_to']);

        // Nada se guardó: ni cuotas, ni moras, ni el registro de ejecución
        $this->assertSame(['fees' => 0, 'penalties' => 0], $this->counts());
        $this->assertFalse(DB::table('process_executions')->where('process_name', 'like', '%'.substr($this->campaign->uuid, 0, 8))->exists());
    }

    public function test_save_creates_exactly_what_the_preview_showed(): void
    {
        $preview = $this->postJson('/v1/financial/fund-raising/process-charges/preview', ['campaign_uuid' => $this->campaign->uuid])->json('data');
        $saved   = $this->save();

        $this->assertSame($preview['fees_generated'], $saved['fees_generated']);
        $this->assertSame($preview['penalties_generated'], $saved['penalties_generated']);
        $this->assertSame(['fees' => 1, 'penalties' => 5], $this->counts());
    }

    public function test_revert_restores_the_previous_state(): void
    {
        $saved = $this->save();
        $this->assertSame(['fees' => 1, 'penalties' => 5], $this->counts());

        $this->postJson("/v1/financial/fund-raising/executions/{$saved['process_id']}/revert")
            ->assertOk()
            ->assertJsonPath('data.fees_deleted', 1)
            ->assertJsonPath('data.penalties_deleted', 5);

        $this->assertSame(['fees' => 0, 'penalties' => 0], $this->counts());
        $this->assertSame('reverted', DB::table('process_executions')->where('id', $saved['process_id'])->value('execution_status'));

        // Se puede volver a ejecutar después de restablecer
        $this->save();
        $this->assertSame(['fees' => 1, 'penalties' => 5], $this->counts());
    }

    public function test_revert_is_blocked_if_a_generated_row_was_paid(): void
    {
        $saved = $this->save();
        DB::table('monthly_fees')->where('generated_by_process_oid', $saved['process_id'])
            ->update(['amount_paid' => 1, 'balance' => 0, 'fee_status' => 'paid']);

        $this->postJson("/v1/financial/fund-raising/executions/{$saved['process_id']}/revert")
            ->assertStatus(422)
            ->assertJsonPath('status', false);

        $this->assertSame(['fees' => 1, 'penalties' => 5], $this->counts(), 'No debe borrar nada');
    }

    public function test_revert_twice_is_rejected(): void
    {
        $saved = $this->save();
        $this->postJson("/v1/financial/fund-raising/executions/{$saved['process_id']}/revert")->assertOk();
        $this->postJson("/v1/financial/fund-raising/executions/{$saved['process_id']}/revert")->assertStatus(422);
    }

    public function test_only_the_latest_execution_can_be_reverted(): void
    {
        $first = $this->save();

        // Al día siguiente se ejecuta otra vez (nueva ejecución con 1 mora más)
        Carbon::setTestNow('2026-12-21 10:00:00');
        $this->save();

        $this->postJson("/v1/financial/fund-raising/executions/{$first['process_id']}/revert")->assertStatus(422);
        $this->assertSame(['fees' => 1, 'penalties' => 6], $this->counts());
    }

    public function test_guests_cannot_preview_save_or_revert(): void
    {
        $this->app['auth']->forgetGuards();

        $this->postJson('/v1/financial/fund-raising/process-charges/preview', ['campaign_uuid' => $this->campaign->uuid])->assertRedirect(route('auth.login'));
        $this->postJson('/v1/financial/fund-raising/executions/1/revert')->assertRedirect(route('auth.login'));
        $this->assertSame(['fees' => 0, 'penalties' => 0], $this->counts());
    }
}
