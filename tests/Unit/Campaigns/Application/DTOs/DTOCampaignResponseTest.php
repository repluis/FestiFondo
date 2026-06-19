<?php

declare(strict_types=1);

namespace Tests\Unit\Campaigns\Application\DTOs;

use PHPUnit\Framework\TestCase;
use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Domain\Entities\Campaign;
use Tests\Campaigns\Concerns\MakesCampaign;

class DTOCampaignResponseTest extends TestCase
{
    use MakesCampaign;

    public function test_from_entity_maps_all_fields(): void
    {
        $entity = $this->makeCampaign([
            'oid'              => 7,
            'uuid'             => 'test-uuid',
            'name'             => 'Summer Fund',
            'description'      => 'A summer campaign',
            'targetAmount'     => 5000.00,
            'collectedAmount'  => 1250.00,
            'monthlyFeeAmount' => 25.00,
            'dailyPenaltyRate' => 0.10,
            'dueDay'           => 10,
            'startDate'        => '2026-06-01',
            'endDate'          => '2026-08-31',
            'campaignStatus'   => 'active',
            'status'           => true,
            'createdAt'        => '2026-06-01T00:00:00.000000Z',
            'updatedAt'        => '2026-06-15T00:00:00.000000Z',
        ]);

        $dto = DTOCampaignResponse::fromEntity($entity);

        $this->assertSame(7, $dto->oid);
        $this->assertSame('test-uuid', $dto->uuid);
        $this->assertSame('Summer Fund', $dto->name);
        $this->assertSame('A summer campaign', $dto->description);
        $this->assertSame(5000.00, $dto->targetAmount);
        $this->assertSame(1250.00, $dto->collectedAmount);
        $this->assertSame(25.00, $dto->monthlyFeeAmount);
        $this->assertSame(0.10, $dto->dailyPenaltyRate);
        $this->assertSame(10, $dto->dueDay);
        $this->assertSame('2026-06-01', $dto->startDate);
        $this->assertSame('2026-08-31', $dto->endDate);
        $this->assertSame('active', $dto->campaignStatus);
        $this->assertTrue($dto->status);
        $this->assertSame('2026-06-01T00:00:00.000000Z', $dto->createdAt);
        $this->assertSame('2026-06-15T00:00:00.000000Z', $dto->updatedAt);
    }

    public function test_pending_amount_is_target_minus_collected(): void
    {
        $entity = $this->makeCampaign(['targetAmount' => 1000.00, 'collectedAmount' => 300.00]);

        $dto = DTOCampaignResponse::fromEntity($entity);

        $this->assertSame(700.00, $dto->pendingAmount);
    }

    public function test_pending_amount_is_zero_when_fully_collected(): void
    {
        $entity = $this->makeCampaign(['targetAmount' => 1000.00, 'collectedAmount' => 1000.00]);

        $dto = DTOCampaignResponse::fromEntity($entity);

        $this->assertSame(0.0, $dto->pendingAmount);
    }

    public function test_pending_amount_is_zero_when_over_collected(): void
    {
        $entity = $this->makeCampaign(['targetAmount' => 1000.00, 'collectedAmount' => 1500.00]);

        $dto = DTOCampaignResponse::fromEntity($entity);

        $this->assertSame(0.0, $dto->pendingAmount);
    }

    public function test_progress_percent_is_calculated_correctly(): void
    {
        $entity = $this->makeCampaign(['targetAmount' => 1000.00, 'collectedAmount' => 250.00]);

        $dto = DTOCampaignResponse::fromEntity($entity);

        $this->assertSame(25.00, $dto->progressPercent);
    }

    public function test_progress_percent_is_zero_when_target_is_zero(): void
    {
        $entity = $this->makeCampaign(['targetAmount' => 0.0, 'collectedAmount' => 0.0]);

        $dto = DTOCampaignResponse::fromEntity($entity);

        $this->assertSame(0.0, $dto->progressPercent);
    }

    public function test_progress_percent_is_rounded_to_two_decimals(): void
    {
        $entity = $this->makeCampaign(['targetAmount' => 3000.00, 'collectedAmount' => 1000.00]);

        $dto = DTOCampaignResponse::fromEntity($entity);

        $this->assertSame(33.33, $dto->progressPercent);
    }

    public function test_null_uuid_becomes_empty_string(): void
    {
        $entity = $this->makeCampaign(['uuid' => null]);

        $dto = DTOCampaignResponse::fromEntity($entity);

        $this->assertSame('', $dto->uuid);
    }

    public function test_to_array_contains_all_expected_keys(): void
    {
        $dto = DTOCampaignResponse::fromEntity($this->makeCampaign());

        $array = $dto->toArray();

        $this->assertArrayHasKey('oid', $array);
        $this->assertArrayHasKey('uuid', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('target_amount', $array);
        $this->assertArrayHasKey('collected_amount', $array);
        $this->assertArrayHasKey('monthly_fee_amount', $array);
        $this->assertArrayHasKey('daily_penalty_rate', $array);
        $this->assertArrayHasKey('due_day', $array);
        $this->assertArrayHasKey('pending_amount', $array);
        $this->assertArrayHasKey('progress_percent', $array);
        $this->assertArrayHasKey('start_date', $array);
        $this->assertArrayHasKey('end_date', $array);
        $this->assertArrayHasKey('campaign_status', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }

    public function test_to_array_values_match_dto_properties(): void
    {
        $entity = $this->makeCampaign([
            'name'            => 'Verano',
            'targetAmount'    => 2000.00,
            'collectedAmount' => 500.00,
            'campaignStatus'  => 'active',
        ]);
        $dto   = DTOCampaignResponse::fromEntity($entity);
        $array = $dto->toArray();

        $this->assertSame('Verano', $array['name']);
        $this->assertSame(2000.00, $array['target_amount']);
        $this->assertSame(500.00, $array['collected_amount']);
        $this->assertSame(1500.00, $array['pending_amount']);
        $this->assertSame(25.00, $array['progress_percent']);
        $this->assertSame('active', $array['campaign_status']);
    }
}
