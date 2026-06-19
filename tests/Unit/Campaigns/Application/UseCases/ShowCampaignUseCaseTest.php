<?php

declare(strict_types=1);

namespace Tests\Unit\Campaigns\Application\UseCases;

use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Campaigns\Concerns\MakesCampaign;
use Src\Campaigns\Application\UseCases\ShowCampaignUseCase;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class ShowCampaignUseCaseTest extends TestCase
{
    use MakesCampaign;

    private MockInterface $repository;
    private ShowCampaignUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CampaignRepositoryInterface::class);
        $this->useCase    = new ShowCampaignUseCase($this->repository);
    }

    public function test_returns_campaign_dto_when_found(): void
    {
        $uuid   = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $entity = $this->makeCampaign(['uuid' => $uuid, 'name' => 'Navidad 2026']);

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn($entity);

        $result = $this->useCase->execute($uuid);

        $this->assertInstanceOf(DTOCampaignResponse::class, $result);
        $this->assertSame($uuid, $result->uuid);
        $this->assertSame('Navidad 2026', $result->name);
    }

    public function test_throws_not_found_when_campaign_does_not_exist(): void
    {
        $uuid = 'non-existent-uuid';

        $this->repository
            ->shouldReceive('findByUuid')
            ->once()
            ->with($uuid)
            ->andReturn(null);

        $this->expectException(CampaignNotFoundException::class);
        $this->expectExceptionMessage("Campaign not found with UUID: {$uuid}");

        $this->useCase->execute($uuid);
    }
}
