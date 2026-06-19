<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FullStateSeeder extends Seeder
{
    public function run(): void
    {
        $campaign   = DB::table('fund_raisings')->where('name', 'recaudacion_navidad_2026')->first();
        $campaignOid = $campaign->oid;

        // identification (original user id) → member oid
        $m = DB::table('members')->pluck('oid', 'identification')->toArray();

        // Groups by identification key
        $groupA = [(string)1, (string)3, (string)5, (string)6, (string)10, (string)12];  // all 6 months paid
        $groupB = [(string)4, (string)8];                                                   // Jan-May paid, Jun pending
        $groupC = [(string)2, (string)7, (string)9, (string)11];                            // only Jan paid

        $this->command->info('Seeding monthly fees...');
        $this->seedMonthlyFees($m, $campaignOid, $groupA, $groupB, $groupC);

        $this->command->info('Seeding penalties...');
        $this->seedPenalties($m, $campaignOid, $groupB, $groupC);

        $this->command->info('Seeding transactions...');
        $collected = $this->seedTransactions($m, $campaignOid);

        DB::table('fund_raisings')
            ->where('oid', $campaignOid)
            ->update(['collected_amount' => $collected]);

        $this->command->info("collected_amount updated → \${$collected}");
        $this->command->info('FullStateSeeder completed.');
    }

    // ─────────────────────────────────────────────────────────────
    // MONTHLY FEES
    // ─────────────────────────────────────────────────────────────
    private function seedMonthlyFees(
        array $m, int $campaignOid,
        array $groupA, array $groupB, array $groupC
    ): void {
        $months = [
            ['year' => 2026, 'month' => 1, 'due' => '2026-01-15'],
            ['year' => 2026, 'month' => 2, 'due' => '2026-02-15'],
            ['year' => 2026, 'month' => 3, 'due' => '2026-03-15'],
            ['year' => 2026, 'month' => 4, 'due' => '2026-04-15'],
            ['year' => 2026, 'month' => 5, 'due' => '2026-05-15'],
            ['year' => 2026, 'month' => 6, 'due' => '2026-06-15'],
        ];

        $now  = now()->toDateTimeString();
        $rows = [];

        foreach ($months as $mo) {
            foreach ($m as $identification => $oid) {
                $isPaid = match (true) {
                    in_array($identification, $groupA)                       => true,
                    in_array($identification, $groupB) && $mo['month'] <= 5  => true,
                    in_array($identification, $groupC) && $mo['month'] === 1 => true,
                    default                                                  => false,
                };

                $rows[] = [
                    'member_oid'               => $oid,
                    'campaign_oid'             => $campaignOid,
                    'period_year'              => $mo['year'],
                    'period_month'             => $mo['month'],
                    'due_date'                 => $mo['due'],
                    'amount'                   => 1.00,
                    'amount_paid'              => $isPaid ? 1.00 : 0.00,
                    'balance'                  => $isPaid ? 0.00 : 1.00,
                    'fee_status'               => $isPaid ? 'paid' : 'pending',
                    'generated_by_process_oid' => null,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('monthly_fees')->insert($chunk);
        }

        $this->command->line('  ' . count($rows) . ' monthly fee records inserted.');
    }

    // ─────────────────────────────────────────────────────────────
    // PENALTIES
    // ─────────────────────────────────────────────────────────────
    private function seedPenalties(
        array $m, int $campaignOid,
        array $groupB, array $groupC
    ): void {
        $now  = now()->toDateTimeString();
        $rows = [];

        // Group B — 4 days: Jun 16-19
        foreach ($groupB as $id) {
            $oid = $m[$id];
            for ($day = 16; $day <= 19; $day++) {
                $rows[] = [
                    'member_oid'               => $oid,
                    'campaign_oid'             => $campaignOid,
                    'period_year'              => 2026,
                    'period_month'             => 6,
                    'penalty_date'             => "2026-06-{$day}",
                    'days_overdue'             => $day - 15,
                    'daily_rate_snapshot'      => 0.0500,
                    'amount'                   => 0.05,
                    'amount_paid'              => 0.00,
                    'balance'                  => 0.05,
                    'penalty_status'           => 'pending',
                    'generated_by_process_oid' => null,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ];
            }
        }

        // Group C — 124 days: Feb 16 to Jun 19
        $start   = Carbon::parse('2026-02-16');
        $end     = Carbon::parse('2026-06-19');
        $baseDue = Carbon::parse('2026-02-15');

        $dates = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->copy();
        }

        foreach ($groupC as $id) {
            $oid = $m[$id];
            foreach ($dates as $date) {
                $rows[] = [
                    'member_oid'               => $oid,
                    'campaign_oid'             => $campaignOid,
                    'period_year'              => 2026,
                    'period_month'             => $date->month,
                    'penalty_date'             => $date->toDateString(),
                    'days_overdue'             => max(1, (int) $baseDue->diffInDays($date)),
                    'daily_rate_snapshot'      => 0.0500,
                    'amount'                   => 0.05,
                    'amount_paid'              => 0.00,
                    'balance'                  => 0.05,
                    'penalty_status'           => 'pending',
                    'generated_by_process_oid' => null,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('penalties')->insert($chunk);
        }

        $this->command->line('  ' . count($rows) . ' penalty records inserted.');
    }

    // ─────────────────────────────────────────────────────────────
    // TRANSACTIONS  (datos correctos de producción)
    // ─────────────────────────────────────────────────────────────
    private function seedTransactions(array $m, int $campaignOid): float
    {
        // ref = original user id (1-12); status false = cancelled
        $data = [
            // January — initial batch (all 12 members)
            ['ref' =>  1, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' =>  2, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' =>  3, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' =>  4, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' =>  5, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' =>  6, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' =>  7, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' =>  8, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' =>  9, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' => 10, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' => 11, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            ['ref' => 12, 'amount' => 1.00, 'desc' => 'Pago cuota navidad enero 2026',     'status' => true,  'created' => '2026-01-15 12:00:00', 'updated' => '2026-01-15 12:00:00'],
            // February
            ['ref' =>  1, 'amount' => 1.00, 'desc' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'created' => '2026-02-20 19:59:27', 'updated' => '2026-02-20 19:59:27'],
            ['ref' => 12, 'amount' => 1.15, 'desc' => 'Pago manual - Pedro Guerrero',      'status' => true,  'created' => '2026-02-20 20:06:26', 'updated' => '2026-02-20 20:06:26'],
            ['ref' =>  6, 'amount' => 1.15, 'desc' => 'Pago manual - Samara Guerrero',     'status' => true,  'created' => '2026-02-20 20:06:36', 'updated' => '2026-02-20 20:06:36'],
            ['ref' =>  3, 'amount' => 1.15, 'desc' => 'Pago manual - Katherine Cedeño',    'status' => true,  'created' => '2026-02-20 20:06:44', 'updated' => '2026-02-20 20:06:44'],
            ['ref' =>  5, 'amount' => 1.05, 'desc' => 'Pago manual - Dolores Posligua',    'status' => true,  'created' => '2026-02-20 20:08:09', 'updated' => '2026-02-20 20:08:09'],
            ['ref' => 10, 'amount' => 1.05, 'desc' => 'Pago manual - Dereck Cedeño',       'status' => true,  'created' => '2026-02-20 20:08:25', 'updated' => '2026-02-20 20:08:25'],
            ['ref' =>  4, 'amount' => 1.10, 'desc' => 'Pago manual - Luis Palemon Cedeño', 'status' => true,  'created' => '2026-02-20 20:08:55', 'updated' => '2026-02-20 20:08:55'],
            ['ref' =>  8, 'amount' => 1.00, 'desc' => 'Pago manual - Jean Molina',         'status' => true,  'created' => '2026-02-20 20:09:11', 'updated' => '2026-02-20 20:09:11'],
            // March
            ['ref' =>  3, 'amount' => 1.00, 'desc' => 'Pago manual - Katherine Cedeño',    'status' => true,  'created' => '2026-03-15 17:37:17', 'updated' => '2026-03-15 17:37:17'],
            ['ref' =>  6, 'amount' => 1.00, 'desc' => 'Pago manual - Samara Guerrero',     'status' => true,  'created' => '2026-03-15 17:37:25', 'updated' => '2026-03-15 17:37:25'],
            ['ref' => 12, 'amount' => 1.00, 'desc' => 'Pago manual - Pedro Guerrero',      'status' => true,  'created' => '2026-03-15 17:37:31', 'updated' => '2026-03-15 17:37:31'],
            ['ref' =>  5, 'amount' => 1.00, 'desc' => 'Pago manual - Dolores Posligua',    'status' => true,  'created' => '2026-03-15 17:37:46', 'updated' => '2026-03-15 17:37:46'],
            ['ref' => 10, 'amount' => 1.00, 'desc' => 'Pago manual - Dereck Cedeño',       'status' => true,  'created' => '2026-03-15 17:37:52', 'updated' => '2026-03-15 17:37:52'],
            ['ref' =>  1, 'amount' => 1.00, 'desc' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'created' => '2026-03-15 17:38:52', 'updated' => '2026-03-15 17:38:52'],
            ['ref' =>  4, 'amount' => 1.70, 'desc' => 'Pago manual - Luis Palemon Cedeño', 'status' => true,  'created' => '2026-03-29 01:48:50', 'updated' => '2026-03-29 01:48:50'],
            ['ref' =>  8, 'amount' => 3.10, 'desc' => 'Pago manual - Jean Molina',         'status' => true,  'created' => '2026-03-31 18:35:31', 'updated' => '2026-03-31 18:35:31'],
            // April
            ['ref' =>  8, 'amount' => 0.10, 'desc' => 'Pago manual - Jean Molina',         'status' => true,  'created' => '2026-04-03 02:52:59', 'updated' => '2026-04-03 02:52:59'],
            ['ref' =>  6, 'amount' => 1.00, 'desc' => 'Pago manual - Samara Guerrero',     'status' => true,  'created' => '2026-04-16 05:08:16', 'updated' => '2026-04-16 05:08:16'],
            ['ref' =>  5, 'amount' => 1.00, 'desc' => 'Pago manual - Dolores Posligua',    'status' => true,  'created' => '2026-04-16 05:08:25', 'updated' => '2026-04-16 05:08:25'],
            ['ref' =>  3, 'amount' => 1.00, 'desc' => 'Pago manual - Katherine Cedeño',    'status' => true,  'created' => '2026-04-16 05:08:31', 'updated' => '2026-04-16 05:08:31'],
            ['ref' => 12, 'amount' => 1.00, 'desc' => 'Pago manual - Pedro Guerrero',      'status' => true,  'created' => '2026-04-16 05:08:42', 'updated' => '2026-04-16 05:08:42'],
            ['ref' => 10, 'amount' => 1.00, 'desc' => 'Pago manual - Dereck Cedeño',       'status' => true,  'created' => '2026-04-16 05:08:48', 'updated' => '2026-04-16 05:08:48'],
            ['ref' =>  1, 'amount' => 1.00, 'desc' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'created' => '2026-04-16 05:08:59', 'updated' => '2026-04-16 05:08:59'],
            ['ref' =>  4, 'amount' => 1.25, 'desc' => 'Pago manual - Luis Palemon Cedeño', 'status' => true,  'created' => '2026-04-20 22:39:59', 'updated' => '2026-04-20 22:39:59'],
            // May
            ['ref' =>  8, 'amount' => 1.25, 'desc' => 'Pago manual - Jean Molina',         'status' => true,  'created' => '2026-05-06 17:30:38', 'updated' => '2026-05-06 17:30:38'],
            ['ref' =>  8, 'amount' => 1.00, 'desc' => 'Pago manual - Jean Molina',         'status' => false, 'created' => '2026-05-06 17:30:47', 'updated' => '2026-06-18 07:08:14'],  // cancelled
            ['ref' =>  1, 'amount' => 1.05, 'desc' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'created' => '2026-05-16 16:36:42', 'updated' => '2026-05-16 16:36:42'],
            ['ref' =>  8, 'amount' => 1.85, 'desc' => 'Pago manual - Jean Molina',         'status' => true,  'created' => '2026-05-16 17:05:22', 'updated' => '2026-05-16 17:05:22'],
            ['ref' =>  3, 'amount' => 1.05, 'desc' => 'Pago manual - Katherine Cedeño',    'status' => true,  'created' => '2026-05-17 00:41:15', 'updated' => '2026-05-17 00:41:15'],
            ['ref' => 12, 'amount' => 1.05, 'desc' => 'Pago manual - Pedro Guerrero',      'status' => true,  'created' => '2026-05-17 00:41:39', 'updated' => '2026-05-17 00:41:39'],
            ['ref' =>  6, 'amount' => 1.05, 'desc' => 'Pago manual - Samara Guerrero',     'status' => true,  'created' => '2026-05-17 00:41:59', 'updated' => '2026-05-17 00:41:59'],
            ['ref' =>  5, 'amount' => 1.05, 'desc' => 'Pago manual - Dolores Posligua',    'status' => true,  'created' => '2026-05-17 04:38:28', 'updated' => '2026-05-17 04:38:28'],
            ['ref' => 10, 'amount' => 1.05, 'desc' => 'Pago manual - Dereck Cedeño',       'status' => true,  'created' => '2026-05-17 04:38:45', 'updated' => '2026-05-17 04:38:45'],
            ['ref' =>  4, 'amount' => 1.15, 'desc' => 'Pago manual - Luis Palemon Cedeño', 'status' => true,  'created' => '2026-05-18 15:08:42', 'updated' => '2026-05-18 15:08:42'],
            // June
            ['ref' =>  1, 'amount' => 1.00, 'desc' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'created' => '2026-06-15 19:27:02', 'updated' => '2026-06-15 19:27:02'],
            ['ref' =>  3, 'amount' => 1.00, 'desc' => 'Pago manual - Katherine Cedeño',    'status' => true,  'created' => '2026-06-15 19:27:26', 'updated' => '2026-06-15 19:27:26'],
            ['ref' =>  6, 'amount' => 1.00, 'desc' => 'Pago manual - Samara Guerrero',     'status' => true,  'created' => '2026-06-15 19:27:38', 'updated' => '2026-06-15 19:27:38'],
            ['ref' => 12, 'amount' => 1.00, 'desc' => 'Pago manual - Pedro Guerrero',      'status' => true,  'created' => '2026-06-15 19:27:54', 'updated' => '2026-06-15 19:27:54'],
            ['ref' =>  5, 'amount' => 1.00, 'desc' => 'Pago manual - Dolores Posligua',    'status' => true,  'created' => '2026-06-15 19:28:08', 'updated' => '2026-06-15 19:28:08'],
            ['ref' => 10, 'amount' => 1.00, 'desc' => 'Pago manual - Dereck Cedeño',       'status' => true,  'created' => '2026-06-15 19:28:23', 'updated' => '2026-06-15 19:28:23'],
        ];

        $collected = 0.0;
        $count     = 0;

        foreach ($data as $row) {
            DB::table('transactions')->insert([
                'transaction_type' => 'income',
                'member_oid'       => $m[(string) $row['ref']],
                'campaign_oid'     => $campaignOid,
                'amount'           => $row['amount'],
                'description'      => $row['desc'],
                'transaction_date' => substr($row['created'], 0, 10),
                'status'           => $row['status'],
                'notes'            => null,
                'reference'        => null,
                'created_by_oid'   => 0,
                'updated_by_oid'   => 0,
                'created_at'       => $row['created'],
                'updated_at'       => $row['updated'],
            ]);

            if ($row['status']) {
                $collected = round($collected + $row['amount'], 2);
            }
            $count++;
        }

        $this->command->line("  {$count} transactions inserted. Active collected: \${$collected}");
        return $collected;
    }
}
