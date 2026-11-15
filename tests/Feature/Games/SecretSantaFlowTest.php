<?php

declare(strict_types=1);

namespace Tests\Feature\Games;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Flujo completo del Amigo Secreto contra Postgres real, dentro de una
 * transacción que se revierte al final de cada test.
 */
class SecretSantaFlowTest extends TestCase
{
    private string $campaignUuid;
    private array  $memberOids;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'pgsql']);
        DB::beginTransaction();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);

        $campaign = DB::table('fund_raisings as fr')
            ->join('campaign_members as cm', 'cm.campaign_oid', '=', 'fr.oid')
            ->where('fr.status', true)
            ->select('fr.uuid')
            ->first();

        if (!$campaign) {
            $this->markTestSkipped('No hay campañas con integrantes en la base de datos.');
        }

        $this->campaignUuid = $campaign->uuid;
        $this->memberOids   = array_column(
            $this->asAdmin()->getJson("/v1/games/secret-santa/campaigns/{$this->campaignUuid}/members")->json('data'),
            'oid',
        );
        $this->asGuest();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function asAdmin(): static
    {
        return $this->actingAs(User::first());
    }

    private function asGuest(): static
    {
        $this->app['auth']->forgetGuards();

        return $this;
    }

    private function drawPayload(): array
    {
        return [
            'campaign_uuid' => $this->campaignUuid,
            'participants'  => $this->memberOids,
            'exclusions'    => [['giver' => $this->memberOids[0], 'receiver' => $this->memberOids[1]]],
        ];
    }

    /** Sortea como admin y vuelve a dejar la sesión como invitado. */
    private function draw(): array
    {
        $data = $this->asAdmin()->postJson('/v1/games/secret-santa/draw', $this->drawPayload())->assertOk()->json('data');
        $this->asGuest();

        return $data;
    }

    public function test_only_admin_can_draw(): void
    {
        $this->postJson('/v1/games/secret-santa/draw', $this->drawPayload())->assertRedirect(route('auth.login'));
        $this->assertSame(0, DB::table('secret_santa_draws')->where('created_at', '>=', now()->subMinute())->count());

        $this->get('/v1/games/secret-santa')->assertOk()->assertSee('¿Ya te llegó tu enlace?')->assertDontSee('Realizar sorteo');
        $this->asAdmin()->get('/v1/games/secret-santa')->assertOk()->assertSee('Realizar sorteo');
    }

    public function test_draw_is_saved_and_does_not_expose_receivers(): void
    {
        $draw = $this->draw();

        $this->assertTrue($draw['active']);
        $this->assertCount(count($this->memberOids), $draw['assignments']);
        foreach ($draw['assignments'] as $a) {
            $this->assertArrayNotHasKey('receiver_name', $a);
            $this->assertFalse($a['viewed']);
            $this->assertStringContainsString('/v1/games/secret-santa/r/', $a['url']);
        }

        $giverOid = $this->memberOids[0];
        $receiver = DB::table('secret_santa_assignments as a')
            ->join('secret_santa_draws as d', 'd.id', '=', 'a.draw_id')
            ->where('d.uuid', $draw['uuid'])
            ->where('a.giver_oid', $giverOid)
            ->value('a.receiver_oid');
        $this->assertNotEquals($this->memberOids[1], $receiver, 'La exclusión no se respetó');
        $this->assertNotEquals($giverOid, $receiver);
    }

    public function test_link_can_only_be_revealed_once_until_admin_resets_it(): void
    {
        $token = $this->draw()['assignments'][0]['token'];

        $this->get("/v1/games/secret-santa/r/{$token}")->assertOk()->assertSee('Tu amigo secreto es');

        $this->postJson("/v1/games/secret-santa/r/{$token}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['giver_name', 'receiver_name']]);

        $this->postJson("/v1/games/secret-santa/r/{$token}")->assertStatus(409);
        $this->get("/v1/games/secret-santa/r/{$token}")->assertOk()->assertSee('Ya revelaste');

        // Un invitado no puede restaurar
        $this->postJson("/v1/games/secret-santa/r/{$token}/reset")->assertRedirect(route('auth.login'));

        // El admin sí
        $this->asAdmin()->postJson("/v1/games/secret-santa/r/{$token}/reset")->assertOk();
        $this->asGuest();

        $this->postJson("/v1/games/secret-santa/r/{$token}")->assertOk();
    }

    public function test_new_draw_invalidates_previous_links(): void
    {
        $oldToken = $this->draw()['assignments'][0]['token'];
        $this->draw();

        $this->postJson("/v1/games/secret-santa/r/{$oldToken}")->assertNotFound();
        $this->get("/v1/games/secret-santa/r/{$oldToken}")->assertSee('Enlace no válido');
    }

    public function test_history_lists_draws_and_can_reopen_them(): void
    {
        $first  = $this->draw();
        $second = $this->draw();

        // Invitados no ven el historial ni los detalles
        $this->getJson('/v1/games/secret-santa/draws')->assertRedirect(route('auth.login'));
        $this->getJson("/v1/games/secret-santa/draws/{$first['uuid']}")->assertRedirect(route('auth.login'));

        $history = $this->asAdmin()->getJson('/v1/games/secret-santa/draws')->assertOk()->json('data');
        $byUuid  = collect($history)->keyBy('uuid');

        $this->assertSame($second['uuid'], $history[0]['uuid'], 'El más reciente va primero');
        $this->assertTrue($byUuid[$second['uuid']]['active']);
        $this->assertFalse($byUuid[$first['uuid']]['active']);
        $this->assertSame(count($this->memberOids), $byUuid[$second['uuid']]['participants']);
        $this->assertSame(0, $byUuid[$second['uuid']]['viewed']);

        // Reabrir el sorteo anterior (reemplazado) desde el historial
        $this->getJson("/v1/games/secret-santa/draws/{$first['uuid']}")
            ->assertOk()
            ->assertJsonPath('data.active', false)
            ->assertJsonCount(count($this->memberOids), 'data.assignments');

        // El contador de vistos se actualiza
        $this->asGuest()->postJson("/v1/games/secret-santa/r/{$second['assignments'][0]['token']}")->assertOk();
        $history = $this->asAdmin()->getJson('/v1/games/secret-santa/draws')->json('data');
        $this->assertSame(1, $history[0]['viewed']);
    }

    public function test_latest_draw_is_admin_only(): void
    {
        $this->draw();

        $this->getJson("/v1/games/secret-santa/campaigns/{$this->campaignUuid}/latest-draw")->assertRedirect(route('auth.login'));

        $this->asAdmin()
            ->getJson("/v1/games/secret-santa/campaigns/{$this->campaignUuid}/latest-draw")
            ->assertOk()
            ->assertJsonCount(count($this->memberOids), 'data.assignments');
    }

    public function test_exclusions_are_saved_per_campaign(): void
    {
        [$a, $b, $c] = $this->memberOids;
        $url = "/v1/games/secret-santa/campaigns/{$this->campaignUuid}/exclusions";

        // Invitados no pueden ver ni guardar exclusiones
        $this->getJson($url)->assertRedirect(route('auth.login'));
        $this->postJson($url, ['giver' => $a, 'receiver' => $b])->assertRedirect(route('auth.login'));

        $this->asAdmin();
        $this->postJson($url, ['giver' => $a, 'receiver' => $b, 'both' => true])->assertOk();
        $this->postJson($url, ['giver' => $a, 'receiver' => $c])->assertOk();
        $this->postJson($url, ['giver' => $a, 'receiver' => $b])->assertOk(); // duplicada: se ignora
        $this->postJson($url, ['giver' => $a, 'receiver' => $a])->assertStatus(422);

        // Siguen ahí al "recargar"
        $this->getJson($url)->assertOk()->assertJsonCount(3, 'data')->assertJsonFragment(['giver' => $b, 'receiver' => $a]);

        $this->deleteJson($url, ['giver' => $a, 'receiver' => $c])->assertOk()->assertJsonCount(2, 'data');
    }

    /** Sorteo real (con base de datos): nadie se toca a sí mismo y no se asignan personas excluidas. */
    public function test_real_draws_respect_self_and_exclusions(): void
    {
        [$a, $b, $c] = $this->memberOids;
        $exclusions  = [
            ['giver' => $a, 'receiver' => $b],
            ['giver' => $b, 'receiver' => $a],
            ['giver' => $c, 'receiver' => $a],
        ];

        for ($run = 0; $run < 25; $run++) {
            $draw = $this->asAdmin()->postJson('/v1/games/secret-santa/draw', [
                'campaign_uuid' => $this->campaignUuid,
                'participants'  => $this->memberOids,
                'exclusions'    => $exclusions,
            ])->assertOk()->json('data');

            $pairs = DB::table('secret_santa_assignments as a')
                ->join('secret_santa_draws as d', 'd.id', '=', 'a.draw_id')
                ->where('d.uuid', $draw['uuid'])
                ->pluck('a.receiver_oid', 'a.giver_oid')
                ->map(fn ($r) => (int) $r)
                ->all();

            $this->assertCount(count($this->memberOids), $pairs);
            $this->assertEqualsCanonicalizing($this->memberOids, array_values($pairs), 'Alguien recibió dos veces o ninguna');
            foreach ($pairs as $giver => $receiver) {
                $this->assertNotSame((int) $giver, $receiver, 'A alguien le tocó él mismo');
            }
            foreach ($exclusions as $e) {
                $this->assertNotSame($e['receiver'], $pairs[$e['giver']], 'Se asignó una persona excluida');
            }
        }
    }

    public function test_invalid_token_shows_not_found_page(): void
    {
        $this->get('/v1/games/secret-santa/r/no-existe')->assertOk()->assertSee('Enlace no válido');
        $this->postJson('/v1/games/secret-santa/r/no-existe')->assertNotFound();
    }
}
