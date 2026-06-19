<?php

declare(strict_types=1);

namespace Tests\Unit\Campaigns\Application\UseCases;

use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Campaigns\Concerns\MakesCampaign;
use Src\Campaigns\Application\UseCases\CancelCampaignUseCase;
use Src\Campaigns\Domain\Exceptions\CampaignAlreadyCancelledException;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class CancelCampaignUseCaseTest extends TestCase
{
    use MakesCampaign;

    private MockInterface $repository;
    private CancelCampaignUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CampaignRepositoryInterface::class);
        $this->useCase    = new CancelCampaignUseCase($this->repository);
    }

    public function test_cancels_campaign_successfully(): void
    {
        $uuid   = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $entity = $this->makeCampaign(['oid' => 5, 'uuid' => $uuid, 'campaignStatus' => 'active']);

        $this->repository->shouldReceive('findByUuid')->once()->with($uuid)->andReturn($entity);
        $this->repository->shouldReceive('cancel')->once()->with(5, 1);

        $this->useCase->execute($uuid, 1);
    }

    public function test_throws_not_found_when_campaign_missing(): void
    {
        $uuid = 'missing-uuid';

        $this->repository->shouldReceive('findByUuid')->once()->with($uuid)->andReturn(null);
        $this->repository->shouldNotReceive('cancel');

        $this->expectException(CampaignNotFoundException::class);
        $this->expectExceptionMessage("Campaign not found with UUID: {$uuid}");

        $this->useCase->execute($uuid, 1);
    }

    public function test_throws_when_campaign_is_already_cancelled(): void
    {
        $uuid   = 'already-cancelled-uuid';
        $entity = $this->makeCampaign(['uuid' => $uuid, 'campaignStatus' => 'cancelled']);

        $this->repository->shouldReceive('findByUuid')->once()->with($uuid)->andReturn($entity);
        $this->repository->shouldNotReceive('cancel');

        $this->expectException(CampaignAlreadyCancelledException::class);
        $this->expectExceptionMessage("Campaign is already cancelled: {$uuid}");

        $this->useCase->execute($uuid, 1);
    }

    public function test_can_cancel_a_draft_campaign(): void
    {
        $uuid   = 'draft-uuid';
        $entity = $this->makeCampaign(['oid' => 3, 'uuid' => $uuid, 'campaignStatus' => 'draft']);

        $this->repository->shouldReceive('findByUuid')->once()->andReturn($entity);
        $this->repository->shouldReceive('cancel')->once()->with(3, 99);

        $this->useCase->execute($uuid, 99);
    }
}
