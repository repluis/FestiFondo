<?php

declare(strict_types=1);

namespace Tests\Unit\Campaigns\Application\UseCases;

use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Campaigns\Concerns\MakesCampaign;
use Src\Campaigns\Application\UseCases\ListCampaignsUseCase;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class ListCampaignsUseCaseTest extends TestCase
{
    use MakesCampaign;

    private MockInterface $repository;
    private ListCampaignsUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CampaignRepositoryInterface::class);
        $this->useCase    = new ListCampaignsUseCase($this->repository);
    }

    public function test_returns_empty_array_when_no_campaigns(): void
    {
        $this->repository
            ->shouldReceive('listAll')
            ->once()
            ->with([])
            ->andReturn([]);

        $result = $this->useCase->execute();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_maps_entities_to_dtos(): void
    {
        $entities = [
            $this->makeCampaign(['oid' => 1, 'name' => 'Campaign A']),
            $this->makeCampaign(['oid' => 2, 'name' => 'Campaign B', 'uuid' => 'bbbb-uuid']),
            $this->makeCampaign(['oid' => 3, 'name' => 'Campaign C', 'uuid' => 'cccc-uuid']),
        ];

        $this->repository
            ->shouldReceive('listAll')
            ->once()
            ->andReturn($entities);

        $result = $this->useCase->execute();

        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(DTOCampaignResponse::class, $result);
        $this->assertSame('Campaign A', $result[0]->name);
        $this->assertSame('Campaign B', $result[1]->name);
        $this->assertSame('Campaign C', $result[2]->name);
    }

    public function test_passes_filters_to_repository(): void
    {
        $filters = ['status' => true, 'campaign_status' => 'active'];

        $this->repository
            ->shouldReceive('listAll')
            ->once()
            ->with($filters)
            ->andReturn([]);

        $this->useCase->execute($filters);
    }

    public function test_passes_search_filter_to_repository(): void
    {
        $filters = ['search' => 'navidad'];

        $this->repository
            ->shouldReceive('listAll')
            ->once()
            ->with($filters)
            ->andReturn([]);

        $this->useCase->execute($filters);
    }
}
