<?php

declare(strict_types=1);

namespace Tests\Unit\Campaigns\Application\UseCases;

use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Campaigns\Concerns\MakesCampaign;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Application\UseCases\UpdateCampaignUseCase;
use Src\Campaigns\Domain\Exceptions\CampaignAlreadyCancelledException;
use Src\Campaigns\Domain\Exceptions\CampaignInvalidStatusTransitionException;
use Src\Campaigns\Domain\Exceptions\CampaignNameAlreadyExistsException;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class UpdateCampaignUseCaseTest extends TestCase
{
    use MakesCampaign;

    private MockInterface $repository;
    private UpdateCampaignUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CampaignRepositoryInterface::class);
        $this->useCase    = new UpdateCampaignUseCase($this->repository);
    }

    public function test_updates_campaign_and_returns_dto(): void
    {
        $uuid   = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $entity = $this->makeCampaign(['oid' => 1, 'uuid' => $uuid, 'campaignStatus' => 'draft']);
        $dto    = $this->makeUpdateDto(['campaignUuid' => $uuid, 'name' => 'Updated Name', 'campaignStatus' => 'draft']);

        $this->repository->shouldReceive('findByUuid')->once()->with($uuid)->andReturn($entity);
        $this->repository->shouldReceive('existsByName')->once()->with('Updated Name', 1)->andReturn(false);
        $this->repository->shouldReceive('update')->once()->andReturn($this->makeCampaign(['name' => 'Updated Name']));

        $result = $this->useCase->execute($dto);

        $this->assertInstanceOf(DTOCampaignResponse::class, $result);
        $this->assertSame('Updated Name', $result->name);
    }

    public function test_throws_not_found_when_campaign_missing(): void
    {
        $uuid = 'missing-uuid';
        $dto  = $this->makeUpdateDto(['campaignUuid' => $uuid]);

        $this->repository->shouldReceive('findByUuid')->once()->with($uuid)->andReturn(null);

        $this->expectException(CampaignNotFoundException::class);

        $this->useCase->execute($dto);
    }

    public function test_throws_when_updating_a_cancelled_campaign(): void
    {
        $uuid   = 'cancelled-uuid';
        $entity = $this->makeCampaign(['uuid' => $uuid, 'campaignStatus' => 'cancelled']);
        $dto    = $this->makeUpdateDto(['campaignUuid' => $uuid, 'campaignStatus' => 'active']);

        $this->repository->shouldReceive('findByUuid')->once()->andReturn($entity);

        $this->expectException(CampaignAlreadyCancelledException::class);

        $this->useCase->execute($dto);
    }

    public function test_throws_when_name_already_taken_by_another_campaign(): void
    {
        $uuid   = 'some-uuid';
        $entity = $this->makeCampaign(['oid' => 1, 'uuid' => $uuid, 'campaignStatus' => 'draft']);
        $dto    = $this->makeUpdateDto(['campaignUuid' => $uuid, 'name' => 'Taken Name']);

        $this->repository->shouldReceive('findByUuid')->once()->andReturn($entity);
        $this->repository->shouldReceive('existsByName')->once()->with('Taken Name', 1)->andReturn(true);

        $this->expectException(CampaignNameAlreadyExistsException::class);

        $this->useCase->execute($dto);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTransitionsProvider')]
    public function test_throws_on_invalid_status_transition(string $current, string $requested): void
    {
        $uuid   = 'some-uuid';
        $entity = $this->makeCampaign(['oid' => 1, 'uuid' => $uuid, 'campaignStatus' => $current]);
        $dto    = $this->makeUpdateDto(['campaignUuid' => $uuid, 'campaignStatus' => $requested]);

        $this->repository->shouldReceive('findByUuid')->once()->andReturn($entity);

        $this->expectException(CampaignInvalidStatusTransitionException::class);
        $this->expectExceptionMessage("Cannot transition campaign from '{$current}' to '{$requested}'.");

        $this->useCase->execute($dto);
    }

    public static function invalidTransitionsProvider(): array
    {
        return [
            'draft → completed'    => ['draft', 'completed'],
            'active → draft'       => ['active', 'draft'],
            'completed → active'   => ['completed', 'active'],
            'completed → draft'    => ['completed', 'draft'],
            'completed → cancelled'=> ['completed', 'cancelled'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validTransitionsProvider')]
    public function test_allows_valid_status_transitions(string $current, string $requested): void
    {
        $uuid   = 'some-uuid';
        $entity = $this->makeCampaign(['oid' => 1, 'uuid' => $uuid, 'campaignStatus' => $current]);
        $dto    = $this->makeUpdateDto(['campaignUuid' => $uuid, 'campaignStatus' => $requested]);
        $updated = $this->makeCampaign(['campaignStatus' => $requested]);

        $this->repository->shouldReceive('findByUuid')->once()->andReturn($entity);
        $this->repository->shouldReceive('existsByName')->once()->andReturn(false);
        $this->repository->shouldReceive('update')->once()->andReturn($updated);

        $result = $this->useCase->execute($dto);

        $this->assertSame($requested, $result->campaignStatus);
    }

    public static function validTransitionsProvider(): array
    {
        return [
            'draft → active'      => ['draft', 'active'],
            'draft → cancelled'   => ['draft', 'cancelled'],
            'active → completed'  => ['active', 'completed'],
            'active → cancelled'  => ['active', 'cancelled'],
        ];
    }

    public function test_same_status_is_not_a_transition_and_does_not_throw(): void
    {
        $uuid   = 'some-uuid';
        $entity = $this->makeCampaign(['oid' => 1, 'uuid' => $uuid, 'campaignStatus' => 'draft']);
        $dto    = $this->makeUpdateDto(['campaignUuid' => $uuid, 'campaignStatus' => 'draft']);

        $this->repository->shouldReceive('findByUuid')->once()->andReturn($entity);
        $this->repository->shouldReceive('existsByName')->once()->andReturn(false);
        $this->repository->shouldReceive('update')->once()->andReturn($entity);

        $result = $this->useCase->execute($dto);

        $this->assertSame('draft', $result->campaignStatus);
    }

    public function test_passes_all_fields_to_repository_update(): void
    {
        $uuid   = 'some-uuid';
        $entity = $this->makeCampaign(['oid' => 99, 'uuid' => $uuid, 'campaignStatus' => 'draft']);
        $dto    = $this->makeUpdateDto([
            'campaignUuid'    => $uuid,
            'name'            => 'Full Update',
            'description'     => 'Updated desc',
            'targetAmount'    => 9999.00,
            'monthlyFeeAmount'=> 50.00,
            'dailyPenaltyRate'=> 0.25,
            'dueDay'          => 5,
            'startDate'       => '2026-07-01',
            'endDate'         => '2026-12-31',
            'campaignStatus'  => 'active',
            'updatedByOid'    => 7,
        ]);
        $updated = $this->makeCampaign(['campaignStatus' => 'active']);

        $this->repository->shouldReceive('findByUuid')->andReturn($entity);
        $this->repository->shouldReceive('existsByName')->andReturn(false);
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->withArgs(function (int $oid, array $data) use ($dto): bool {
                return $oid                        === 99
                    && $data['name']               === $dto->name
                    && $data['description']        === $dto->description
                    && $data['target_amount']       === $dto->targetAmount
                    && $data['monthly_fee_amount']  === $dto->monthlyFeeAmount
                    && $data['daily_penalty_rate']  === $dto->dailyPenaltyRate
                    && $data['due_day']             === $dto->dueDay
                    && $data['start_date']          === $dto->startDate
                    && $data['end_date']            === $dto->endDate
                    && $data['fund_raising_status'] === $dto->campaignStatus
                    && $data['updated_by_oid']      === 7;
            })
            ->andReturn($updated);

        $this->useCase->execute($dto);
    }
}
