<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Src\FundRaising\Application\DTOs\DTOCreateFundRaisingRequest;
use Src\FundRaising\Application\Services\FundRaisingService;

class FundRaisingSeeder extends Seeder
{
    public function run(FundRaisingService $service): void
    {
        $memberOids = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

        $dto = new DTOCreateFundRaisingRequest(
            name:         'recaudacion_navidad_2026',
            description:  'Recaudación de fondos para Navidad 2026',
            targetAmount: 1200.00,
            startDate:    '2026-01-15',
            endDate:      '2026-12-25',
            createdByOid: 0,
            memberOids:   $memberOids,
        );

        $result = $service->create($dto);

        DB::table('fund_raisings')
            ->where('name', 'recaudacion_navidad_2026')
            ->update([
                'fund_raising_status' => 'active',
                'monthly_fee_amount'  => 1.00,
                'daily_penalty_rate'  => 0.05,
                'due_day'             => 15,
                'collected_amount'    => 0.00,
            ]);

        $this->command->info("Campaign created & activated: {$result->name} — uuid: {$result->uuid}");
        $this->command->info("Members enrolled: " . count($memberOids));
    }
}
