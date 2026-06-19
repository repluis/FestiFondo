<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Campaigns\Concerns\MakesCampaign;
use Src\Campaigns\Application\Services\CampaignService;
use Src\Campaigns\Domain\Exceptions\CampaignNameAlreadyExistsException;

class CreateCampaignHttpTest extends TestCase
{
    use MakesCampaign;

    private MockInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->service = Mockery::mock(CampaignService::class);
        $this->app->instance(CampaignService::class, $this->service);
    }

    // ── 422 Validation (service never called) ────────────────────────────────

    public function test_returns_422_when_name_is_missing(): void
    {
        $response = $this->postJson(
            '/v1/financial/campaigns',
            $this->validStorePayload(['name' => null])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_returns_422_when_target_amount_is_below_minimum(): void
    {
        $response = $this->postJson(
            '/v1/financial/campaigns',
            $this->validStorePayload(['target_amount' => 0.00])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['target_amount']);
    }

    public function test_returns_422_when_due_day_exceeds_28(): void
    {
        $response = $this->postJson(
            '/v1/financial/campaigns',
            $this->validStorePayload(['due_day' => 29])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['due_day']);
    }

    public function test_returns_422_when_due_day_is_zero(): void
    {
        $response = $this->postJson(
            '/v1/financial/campaigns',
            $this->validStorePayload(['due_day' => 0])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['due_day']);
    }

    public function test_returns_422_when_monthly_fee_is_missing(): void
    {
        $response = $this->postJson(
            '/v1/financial/campaigns',
            $this->validStorePayload(['monthly_fee_amount' => null])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['monthly_fee_amount']);
    }

    public function test_returns_422_when_start_date_is_missing(): void
    {
        $response = $this->postJson(
            '/v1/financial/campaigns',
            $this->validStorePayload(['start_date' => null])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['start_date']);
    }

    public function test_returns_422_when_start_date_is_not_a_date(): void
    {
        $response = $this->postJson(
            '/v1/financial/campaigns',
            $this->validStorePayload(['start_date' => 'not-a-date'])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['start_date']);
    }

    public function test_returns_422_when_daily_penalty_rate_is_negative(): void
    {
        $response = $this->postJson(
            '/v1/financial/campaigns',
            $this->validStorePayload(['daily_penalty_rate' => -0.01])
        );

        $response->assertStatus(422)->assertJsonValidationErrors(['daily_penalty_rate']);
    }

    // ── 201 Success ──────────────────────────────────────────────────────────

    public function test_returns_201_and_campaign_data_on_success(): void
    {
        $dto = $this->makeCampaignDto(['name' => 'Test Campaign']);

        $this->service->shouldReceive('create')->once()->andReturn($dto);

        $response = $this->postJson('/v1/financial/campaigns', $this->validStorePayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status', 'message',
                'data' => [
                    'uuid', 'name', 'target_amount', 'collected_amount',
                    'monthly_fee_amount', 'daily_penalty_rate', 'due_day',
                    'pending_amount', 'progress_percent',
                    'start_date', 'campaign_status', 'status',
                ],
            ])
            ->assertJson(['status' => true, 'message' => 'Campaign created successfully.']);
    }

    // ── 409 Conflict ─────────────────────────────────────────────────────────

    public function test_returns_409_when_name_already_exists(): void
    {
        $this->service
            ->shouldReceive('create')
            ->once()
            ->andThrow(CampaignNameAlreadyExistsException::withName('Test Campaign'));

        $response = $this->postJson('/v1/financial/campaigns', $this->validStorePayload());

        $response->assertStatus(409)
            ->assertJson(['status' => false, 'message' => 'A campaign with this name already exists.']);
    }
}
