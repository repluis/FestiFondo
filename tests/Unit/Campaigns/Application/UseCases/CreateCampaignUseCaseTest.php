<?php

declare(strict_types=1);

namespace Tests\Unit\Campaigns\Application\UseCases;

use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Campaigns\Concerns\MakesCampaign;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Application\DTOs\DTOCreateCampaignRequest;
use Src\Campaigns\Application\DTOs\DTOEnrollMembersRequest;
use Src\Campaigns\Application\UseCases\CreateCampaignUseCase;
use Src\Campaigns\Application\UseCases\EnrollMembersUseCase;
use Src\Campaigns\Domain\Exceptions\CampaignNameAlreadyExistsException;
use Src\Campaigns\Domain\Repositories\CampaignRepositoryInterface;

class CreateCampaignUseCaseTest extends TestCase
{
    use MakesCampaign;

    private MockInterface $repository;
    private MockInterface $enrollUseCase;
    private CreateCampaignUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository    = Mockery::mock(CampaignRepositoryInterface::class);
        $this->enrollUseCase = Mockery::mock(EnrollMembersUseCase::class);
        $this->useCase       = new CreateCampaignUseCase($this->repository, $this->enrollUseCase);
    }

    private function makeCreateDto(array $overrides = []): DTOCreateCampaignRequest
    {
        $defaults = [
            'name'            => 'New Campaign',
            'description'     => null,
            'targetAmount'    => 1000.00,
            'monthlyFeeAmount'=> 10.00,
            'dailyPenaltyRate'=> 0.05,
            'dueDay'          => 15,
            'startDate'       => '2026-01-01',
            'endDate'         => null,
            'createdByOid'    => 1,
            'memberOids'      => [],
        ];

        $d = array_merge($defaults, $overrides);

        return new DTOCreateCampaignRequest(
            name:             $d['name'],
            description:      $d['description'],
            targetAmount:     $d['targetAmount'],
            monthlyFeeAmount: $d['monthlyFeeAmount'],
            dailyPenaltyRate: $d['dailyPenaltyRate'],
            dueDay:           $d['dueDay'],
            startDate:        $d['startDate'],
            endDate:          $d['endDate'],
            createdByOid:     $d['createdByOid'],
            memberOids:       $d['memberOids'],
        );
    }

    public function test_creates_campaign_and_returns_dto(): void
    {
        $dto    = $this->makeCreateDto();
        $entity = $this->makeCampaign(['name' => $dto->name]);

        $this->repository->shouldReceive('existsByName')->once()->with($dto->name)->andReturn(false);
        $this->repository->shouldReceive('create')->once()->andReturn($entity);
        $this->enrollUseCase->shouldNotReceive('execute');

        $result = $this->useCase->execute($dto);

        $this->assertInstanceOf(DTOCampaignResponse::class, $result);
        $this->assertSame($dto->name, $result->name);
    }

    public function test_throws_when_name_already_exists(): void
    {
        $dto = $this->makeCreateDto(['name' => 'Existing Campaign']);

        $this->repository->shouldReceive('existsByName')->once()->with('Existing Campaign')->andReturn(true);
        $this->repository->shouldNotReceive('create');

        $this->expectException(CampaignNameAlreadyExistsException::class);
        $this->expectExceptionMessage('A campaign with this name already exists: Existing Campaign');

        $this->useCase->execute($dto);
    }

    public function test_enrolls_members_when_member_oids_provided(): void
    {
        $dto    = $this->makeCreateDto(['memberOids' => [10, 20, 30]]);
        $entity = $this->makeCampaign(['oid' => 5]);

        $this->repository->shouldReceive('existsByName')->once()->andReturn(false);
        $this->repository->shouldReceive('create')->once()->andReturn($entity);

        $this->enrollUseCase
            ->shouldReceive('execute')
            ->once()
            ->withArgs(function (DTOEnrollMembersRequest $enrollDto) use ($entity, $dto): bool {
                return $enrollDto->campaignOid === $entity->oid
                    && $enrollDto->memberOids  === $dto->memberOids
                    && $enrollDto->createdByOid === $dto->createdByOid;
            });

        $this->useCase->execute($dto);
    }

    public function test_does_not_enroll_when_no_member_oids(): void
    {
        $dto    = $this->makeCreateDto(['memberOids' => []]);
        $entity = $this->makeCampaign();

        $this->repository->shouldReceive('existsByName')->once()->andReturn(false);
        $this->repository->shouldReceive('create')->once()->andReturn($entity);
        $this->enrollUseCase->shouldNotReceive('execute');

        $this->useCase->execute($dto);
    }

    public function test_persists_correct_data_to_repository(): void
    {
        $dto    = $this->makeCreateDto([
            'name'            => 'Verified Campaign',
            'description'     => 'A description',
            'targetAmount'    => 5000.00,
            'monthlyFeeAmount'=> 25.00,
            'dailyPenaltyRate'=> 0.10,
            'dueDay'          => 20,
            'startDate'       => '2026-03-01',
            'endDate'         => '2026-12-31',
            'createdByOid'    => 7,
        ]);
        $entity = $this->makeCampaign();

        $this->repository->shouldReceive('existsByName')->once()->andReturn(false);
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->withArgs(function (array $data) use ($dto): bool {
                return $data['name']                === $dto->name
                    && $data['description']         === $dto->description
                    && $data['target_amount']        === $dto->targetAmount
                    && $data['monthly_fee_amount']   === $dto->monthlyFeeAmount
                    && $data['daily_penalty_rate']   === $dto->dailyPenaltyRate
                    && $data['due_day']              === $dto->dueDay
                    && $data['start_date']           === $dto->startDate
                    && $data['end_date']             === $dto->endDate
                    && $data['fund_raising_status']  === 'draft'
                    && $data['collected_amount']     === 0
                    && $data['created_by_oid']       === 7;
            })
            ->andReturn($entity);

        $this->useCase->execute($dto);
    }
}
