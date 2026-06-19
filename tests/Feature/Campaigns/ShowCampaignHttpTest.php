<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Campaigns\Concerns\MakesCampaign;
use Src\Campaigns\Application\Services\CampaignService;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;

class ShowCampaignHttpTest extends TestCase
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

    public function test_returns_200_and_view_when_campaign_exists(): void
    {
        $dto = $this->makeCampaignDto(['uuid' => self::UUID, 'name' => 'Navidad 2026']);

        $this->service->shouldReceive('show')->once()->with(self::UUID)->andReturn($dto);
        $this->service->shouldReceive('listCampaignMembers')->once()->andReturn([]);
        $this->service->shouldReceive('getLastProcessExecution')->once()->andReturn(null);

        $response = $this->get('/v1/financial/campaigns/' . self::UUID);

        $response->assertStatus(200);
    }

    public function test_returns_404_json_when_campaign_not_found(): void
    {
        $this->service
            ->shouldReceive('show')
            ->once()
            ->with(self::UUID)
            ->andThrow(CampaignNotFoundException::withUuid(self::UUID));

        $response = $this->getJson('/v1/financial/campaigns/' . self::UUID);

        $response->assertStatus(404)
            ->assertJson(['status' => false, 'message' => 'Campaign not found.']);
    }

    public function test_create_form_returns_200(): void
    {
        $response = $this->get('/v1/financial/campaigns/create');

        $response->assertStatus(200);
    }

    public function test_edit_form_returns_200_when_campaign_exists(): void
    {
        $dto = $this->makeCampaignDto(['uuid' => self::UUID]);

        $this->service->shouldReceive('show')->once()->with(self::UUID)->andReturn($dto);

        $response = $this->get('/v1/financial/campaigns/' . self::UUID . '/edit');

        $response->assertStatus(200);
    }

    public function test_edit_returns_404_when_campaign_not_found(): void
    {
        $this->service
            ->shouldReceive('show')
            ->once()
            ->andThrow(CampaignNotFoundException::withUuid(self::UUID));

        $response = $this->getJson('/v1/financial/campaigns/' . self::UUID . '/edit');

        $response->assertStatus(404);
    }
}
