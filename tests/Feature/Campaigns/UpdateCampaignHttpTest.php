<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Campaigns\Concerns\MakesCampaign;
use Src\Campaigns\Application\Services\CampaignService;
use Src\Campaigns\Domain\Exceptions\CampaignAlreadyCancelledException;
use Src\Campaigns\Domain\Exceptions\CampaignInvalidStatusTransitionException;
use Src\Campaigns\Domain\Exceptions\CampaignNameAlreadyExistsException;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;

class UpdateCampaignHttpTest extends TestCase
{
    use MakesCampaign;

    private const UUID = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

    private MockInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->service = Mockery::mock(CampaignService::class);
        $this->app->instance(CampaignService::class, $this->service);
    }

    private function putCampaign(array $payload = []): \Illuminate\Testing\TestResponse
    {
        return $this->putJson(
            '/v1/financial/campaigns/' . self::UUID,
            array_merge($this->validUpdatePayload(), $payload)
        );
    }

    // ── 422 Validation ───────────────────────────────────────────────────────

    public function test_returns_422_when_name_missing(): void
    {
        $response = $this->putCampaign(['name' => null]);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    public function test_returns_422_when_campaign_status_is_invalid(): void
    {
        $response = $this->putCampaign(['campaign_status' => 'suspended']);

        $response->assertStatus(422)->assertJsonValidationErrors(['campaign_status']);
    }

    public function test_returns_422_when_due_day_exceeds_28(): void
    {
        $response = $this->putCampaign(['due_day' => 31]);

        $response->assertStatus(422)->assertJsonValidationErrors(['due_day']);
    }

    // ── 200 Success ──────────────────────────────────────────────────────────

    public function test_returns_200_on_successful_update(): void
    {
        $dto = $this->makeCampaignDto(['campaignStatus' => 'active']);

        $this->service->shouldReceive('show')->once()->andReturn($dto);
        $this->service->shouldReceive('update')->once()->andReturn($dto);

        $response = $this->putCampaign(['campaign_status' => 'active']);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => true,
                'message' => 'Campaign updated successfully.',
            ])
            ->assertJsonStructure(['status', 'message', 'data']);
    }

    // ── 404 Not Found ────────────────────────────────────────────────────────

    public function test_returns_404_when_campaign_not_found(): void
    {
        $this->service
            ->shouldReceive('show')
            ->once()
            ->andThrow(CampaignNotFoundException::withUuid(self::UUID));

        $response = $this->putCampaign();

        $response->assertStatus(404)
            ->assertJson(['status' => false, 'message' => 'Campaign not found.']);
    }

    // ── 409 Conflict ─────────────────────────────────────────────────────────

    public function test_returns_409_when_campaign_is_already_cancelled(): void
    {
        $dto = $this->makeCampaignDto();
        $this->service->shouldReceive('show')->once()->andReturn($dto);
        $this->service
            ->shouldReceive('update')
            ->once()
            ->andThrow(CampaignAlreadyCancelledException::withUuid(self::UUID));

        $response = $this->putCampaign();

        $response->assertStatus(409)
            ->assertJson(['status' => false, 'message' => 'Cannot update a cancelled campaign.']);
    }

    public function test_returns_409_when_name_already_taken(): void
    {
        $dto = $this->makeCampaignDto();
        $this->service->shouldReceive('show')->once()->andReturn($dto);
        $this->service
            ->shouldReceive('update')
            ->once()
            ->andThrow(CampaignNameAlreadyExistsException::withName('Taken'));

        $response = $this->putCampaign();

        $response->assertStatus(409)
            ->assertJson(['status' => false, 'message' => 'A campaign with this name already exists.']);
    }

    public function test_returns_422_when_status_transition_is_invalid(): void
    {
        $dto = $this->makeCampaignDto();
        $this->service->shouldReceive('show')->once()->andReturn($dto);
        $this->service
            ->shouldReceive('update')
            ->once()
            ->andThrow(CampaignInvalidStatusTransitionException::from('completed', 'draft'));

        $response = $this->putCampaign(['campaign_status' => 'draft']);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }
}
