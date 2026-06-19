<?php

declare(strict_types=1);

namespace Tests\Unit\Campaigns\Domain;

use PHPUnit\Framework\TestCase;
use Src\Campaigns\Domain\Entities\Campaign;

class CampaignEntityTest extends TestCase
{
    private function make(array $overrides = []): Campaign
    {
        $d = array_merge([
            'id' => 1, 'oid' => 1,
            'uuid'             => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'name'             => 'Navidad 2026',
            'description'      => 'Fondo navideño',
            'targetAmount'     => 5000.00,
            'collectedAmount'  => 250.00,
            'monthlyFeeAmount' => 50.00,
            'dailyPenaltyRate' => 0.10,
            'dueDay'           => 15,
            'startDate'        => '2026-01-01',
            'endDate'          => '2026-12-31',
            'campaignStatus'   => 'draft',
            'status'           => true,
            'createdByOid'     => 1,
            'updatedByOid'     => null,
            'createdAt'        => '2026-01-01T00:00:00.000000Z',
            'updatedAt'        => '2026-01-01T00:00:00.000000Z',
        ], $overrides);

        return new Campaign(
            id:               $d['id'],
            oid:              $d['oid'],
            uuid:             $d['uuid'],
            name:             $d['name'],
            description:      $d['description'],
            targetAmount:     $d['targetAmount'],
            collectedAmount:  $d['collectedAmount'],
            monthlyFeeAmount: $d['monthlyFeeAmount'],
            dailyPenaltyRate: $d['dailyPenaltyRate'],
            dueDay:           $d['dueDay'],
            startDate:        $d['startDate'],
            endDate:          $d['endDate'],
            campaignStatus:   $d['campaignStatus'],
            status:           $d['status'],
            createdByOid:     $d['createdByOid'],
            updatedByOid:     $d['updatedByOid'],
            createdAt:        $d['createdAt'],
            updatedAt:        $d['updatedAt'],
        );
    }

    public function test_entity_exposes_all_properties(): void
    {
        $campaign = $this->make();

        $this->assertSame(1, $campaign->id);
        $this->assertSame(1, $campaign->oid);
        $this->assertSame('a1b2c3d4-e5f6-7890-abcd-ef1234567890', $campaign->uuid);
        $this->assertSame('Navidad 2026', $campaign->name);
        $this->assertSame('Fondo navideño', $campaign->description);
        $this->assertSame(5000.00, $campaign->targetAmount);
        $this->assertSame(250.00, $campaign->collectedAmount);
        $this->assertSame(50.00, $campaign->monthlyFeeAmount);
        $this->assertSame(0.10, $campaign->dailyPenaltyRate);
        $this->assertSame(15, $campaign->dueDay);
        $this->assertSame('2026-01-01', $campaign->startDate);
        $this->assertSame('2026-12-31', $campaign->endDate);
        $this->assertSame('draft', $campaign->campaignStatus);
        $this->assertTrue($campaign->status);
        $this->assertSame(1, $campaign->createdByOid);
        $this->assertNull($campaign->updatedByOid);
    }

    public function test_nullable_fields_accept_null(): void
    {
        $campaign = $this->make([
            'id'          => null,
            'oid'         => null,
            'uuid'        => null,
            'description' => null,
            'endDate'     => null,
            'updatedByOid'=> null,
            'createdAt'   => null,
            'updatedAt'   => null,
        ]);

        $this->assertNull($campaign->id);
        $this->assertNull($campaign->oid);
        $this->assertNull($campaign->uuid);
        $this->assertNull($campaign->description);
        $this->assertNull($campaign->endDate);
        $this->assertNull($campaign->updatedByOid);
        $this->assertNull($campaign->createdAt);
        $this->assertNull($campaign->updatedAt);
    }

    public function test_properties_are_readonly(): void
    {
        $campaign = $this->make();

        $this->expectException(\Error::class);

        /** @phpstan-ignore-next-line */
        $campaign->name = 'Modified';
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('campaignStatusProvider')]
    public function test_all_valid_statuses_are_stored_correctly(string $status): void
    {
        $campaign = $this->make(['campaignStatus' => $status]);

        $this->assertSame($status, $campaign->campaignStatus);
    }

    public static function campaignStatusProvider(): array
    {
        return [
            'draft'     => ['draft'],
            'active'    => ['active'],
            'completed' => ['completed'],
            'cancelled' => ['cancelled'],
        ];
    }

    public function test_status_boolean_stored_correctly(): void
    {
        $active   = $this->make(['status' => true]);
        $inactive = $this->make(['status' => false]);

        $this->assertTrue($active->status);
        $this->assertFalse($inactive->status);
    }

    public function test_due_day_stored_as_integer(): void
    {
        $campaign = $this->make(['dueDay' => 28]);

        $this->assertIsInt($campaign->dueDay);
        $this->assertSame(28, $campaign->dueDay);
    }

    public function test_monetary_fields_stored_as_float(): void
    {
        $campaign = $this->make([
            'targetAmount'     => 999.99,
            'collectedAmount'  => 1.01,
            'monthlyFeeAmount' => 25.50,
            'dailyPenaltyRate' => 0.0500,
        ]);

        $this->assertIsFloat($campaign->targetAmount);
        $this->assertIsFloat($campaign->collectedAmount);
        $this->assertIsFloat($campaign->monthlyFeeAmount);
        $this->assertIsFloat($campaign->dailyPenaltyRate);
    }
}
