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
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;

class CancelCampaignHttpTest extends TestCase
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

    public function test_returns_200_and_cancels_successfully(): void
    {
        $this->service->shouldReceive('cancel')->once()->withArgs(
            fn(string $uuid) => $uuid === self::UUID
        );

        $response = $this->deleteJson('/v1/financial/campaigns/' . self::UUID);

        $response->assertStatus(200)
            ->assertJson([
                'status'  => true,
                'message' => 'Campaign cancelled successfully.',
            ]);
    }

    public function test_returns_404_when_campaign_not_found(): void
    {
        $this->service
            ->shouldReceive('cancel')
            ->once()
            ->andThrow(CampaignNotFoundException::withUuid(self::UUID));

        $response = $this->deleteJson('/v1/financial/campaigns/' . self::UUID);

        $response->assertStatus(404)
            ->assertJson(['status' => false, 'message' => 'Campaign not found.']);
    }

    public function test_returns_409_when_campaign_already_cancelled(): void
    {
        $this->service
            ->shouldReceive('cancel')
            ->once()
            ->andThrow(CampaignAlreadyCancelledException::withUuid(self::UUID));

        $response = $this->deleteJson('/v1/financial/campaigns/' . self::UUID);

        $response->assertStatus(409)
            ->assertJson(['status' => false, 'message' => 'Campaign is already cancelled.']);
    }
}
