<?php

declare(strict_types=1);

namespace Tests\Campaigns\Concerns;

use Src\Campaigns\Application\DTOs\DTOCampaignResponse;
use Src\Campaigns\Application\DTOs\DTOUpdateCampaignRequest;
use Src\Campaigns\Domain\Entities\Campaign;

trait MakesCampaign
{
    protected function makeCampaign(array $overrides = []): Campaign
    {
        $defaults = [
            'id'               => 1,
            'oid'              => 1,
            'uuid'             => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'name'             => 'Test Campaign',
            'description'      => 'A test description',
            'targetAmount'     => 1000.00,
            'collectedAmount'  => 0.00,
            'monthlyFeeAmount' => 10.00,
            'dailyPenaltyRate' => 0.05,
            'dueDay'           => 15,
            'startDate'        => '2026-01-01',
            'endDate'          => null,
            'campaignStatus'   => 'draft',
            'status'           => true,
            'createdByOid'     => 1,
            'updatedByOid'     => null,
            'createdAt'        => '2026-01-01T00:00:00.000000Z',
            'updatedAt'        => '2026-01-01T00:00:00.000000Z',
        ];

        $data = array_merge($defaults, $overrides);

        return new Campaign(
            id:               $data['id'],
            oid:              $data['oid'],
            uuid:             $data['uuid'],
            name:             $data['name'],
            description:      $data['description'],
            targetAmount:     $data['targetAmount'],
            collectedAmount:  $data['collectedAmount'],
            monthlyFeeAmount: $data['monthlyFeeAmount'],
            dailyPenaltyRate: $data['dailyPenaltyRate'],
            dueDay:           $data['dueDay'],
            startDate:        $data['startDate'],
            endDate:          $data['endDate'],
            campaignStatus:   $data['campaignStatus'],
            status:           $data['status'],
            createdByOid:     $data['createdByOid'],
            updatedByOid:     $data['updatedByOid'],
            createdAt:        $data['createdAt'],
            updatedAt:        $data['updatedAt'],
        );
    }

    protected function makeCampaignDto(array $overrides = []): DTOCampaignResponse
    {
        return DTOCampaignResponse::fromEntity($this->makeCampaign($overrides));
    }

    protected function makeUpdateDto(array $overrides = []): DTOUpdateCampaignRequest
    {
        $defaults = [
            'campaignUuid'    => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'name'            => 'Test Campaign',
            'description'     => null,
            'targetAmount'    => 1000.00,
            'monthlyFeeAmount'=> 10.00,
            'dailyPenaltyRate'=> 0.05,
            'dueDay'          => 15,
            'startDate'       => '2026-01-01',
            'endDate'         => null,
            'campaignStatus'  => 'draft',
            'updatedByOid'    => 1,
        ];

        $data = array_merge($defaults, $overrides);

        return new DTOUpdateCampaignRequest(
            campaignUuid:     $data['campaignUuid'],
            name:             $data['name'],
            description:      $data['description'],
            targetAmount:     $data['targetAmount'],
            monthlyFeeAmount: $data['monthlyFeeAmount'],
            dailyPenaltyRate: $data['dailyPenaltyRate'],
            dueDay:           $data['dueDay'],
            startDate:        $data['startDate'],
            endDate:          $data['endDate'],
            campaignStatus:   $data['campaignStatus'],
            updatedByOid:     $data['updatedByOid'],
        );
    }

    protected function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'name'               => 'Test Campaign',
            'description'        => 'A test description',
            'target_amount'      => 1000.00,
            'monthly_fee_amount' => 10.00,
            'daily_penalty_rate' => 0.05,
            'due_day'            => 15,
            'start_date'         => '2026-01-01',
        ], $overrides);
    }

    protected function validUpdatePayload(array $overrides = []): array
    {
        return array_merge($this->validStorePayload(), [
            'campaign_status' => 'draft',
        ], $overrides);
    }
}
