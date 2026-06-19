<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Src\Transactions\Application\DTOs\DTOApplyPaymentRequest;
use Src\Transactions\Application\Services\TransactionService;

class TransactionsSeeder extends Seeder
{
    public function run(TransactionService $service): void
    {
        $campaignOid = DB::table('fund_raisings')->where('name', 'recaudacion_navidad_2026')->value('oid');

        // identification (original user id) → member oid
        $memberMap = DB::table('members')
            ->pluck('oid', 'identification')
            ->toArray();

        // ref in data = original user id (1–12); status false = cancelled after creation
        $transactions = [
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  2, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  3, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  4, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  5, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  6, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  7, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  8, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  9, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' => 10, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' => 11, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' => 12, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',    'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago manual - Luis Antonio Cedeño','status' => true,  'date' => '2026-02-20'],
            ['ref' => 12, 'amount' => 1.15, 'notes' => 'Pago manual - Pedro Guerrero',     'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  6, 'amount' => 1.15, 'notes' => 'Pago manual - Samara Guerrero',    'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  3, 'amount' => 1.15, 'notes' => 'Pago manual - Katherine Cedeño',   'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  5, 'amount' => 1.05, 'notes' => 'Pago manual - Dolores Posligua',   'status' => true,  'date' => '2026-02-20'],
            ['ref' => 10, 'amount' => 1.05, 'notes' => 'Pago manual - Dereck Cedeño',      'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  4, 'amount' => 1.10, 'notes' => 'Pago manual - Luis Palemon Cedeño','status' => true,  'date' => '2026-02-20'],
            ['ref' =>  8, 'amount' => 1.00, 'notes' => 'Pago manual - Jean Molina',        'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  3, 'amount' => 1.00, 'notes' => 'Pago manual - Katherine Cedeño',   'status' => true,  'date' => '2026-03-15'],
            ['ref' =>  6, 'amount' => 1.00, 'notes' => 'Pago manual - Samara Guerrero',    'status' => true,  'date' => '2026-03-15'],
            ['ref' => 12, 'amount' => 1.00, 'notes' => 'Pago manual - Pedro Guerrero',     'status' => true,  'date' => '2026-03-15'],
            ['ref' =>  5, 'amount' => 1.00, 'notes' => 'Pago manual - Dolores Posligua',   'status' => true,  'date' => '2026-03-15'],
            ['ref' => 10, 'amount' => 1.00, 'notes' => 'Pago manual - Dereck Cedeño',      'status' => true,  'date' => '2026-03-15'],
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago manual - Luis Antonio Cedeño','status' => true,  'date' => '2026-03-15'],
            ['ref' =>  4, 'amount' => 1.70, 'notes' => 'Pago manual - Luis Palemon Cedeño','status' => true,  'date' => '2026-03-29'],
            ['ref' =>  8, 'amount' => 3.10, 'notes' => 'Pago manual - Jean Molina',        'status' => true,  'date' => '2026-03-31'],
            ['ref' =>  8, 'amount' => 0.10, 'notes' => 'Pago manual - Jean Molina',        'status' => true,  'date' => '2026-04-03'],
            ['ref' =>  6, 'amount' => 1.00, 'notes' => 'Pago manual - Samara Guerrero',    'status' => true,  'date' => '2026-04-16'],
            ['ref' =>  5, 'amount' => 1.00, 'notes' => 'Pago manual - Dolores Posligua',   'status' => true,  'date' => '2026-04-16'],
            ['ref' =>  3, 'amount' => 1.00, 'notes' => 'Pago manual - Katherine Cedeño',   'status' => true,  'date' => '2026-04-16'],
            ['ref' => 12, 'amount' => 1.00, 'notes' => 'Pago manual - Pedro Guerrero',     'status' => true,  'date' => '2026-04-16'],
            ['ref' => 10, 'amount' => 1.00, 'notes' => 'Pago manual - Dereck Cedeño',      'status' => true,  'date' => '2026-04-16'],
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago manual - Luis Antonio Cedeño','status' => true,  'date' => '2026-04-16'],
            ['ref' =>  4, 'amount' => 1.25, 'notes' => 'Pago manual - Luis Palemon Cedeño','status' => true,  'date' => '2026-04-20'],
            ['ref' =>  8, 'amount' => 1.25, 'notes' => 'Pago manual - Jean Molina',        'status' => true,  'date' => '2026-05-06'],
            ['ref' =>  8, 'amount' => 1.00, 'notes' => 'Pago manual - Jean Molina',        'status' => true,  'date' => '2026-05-06'],  // id 38
            ['ref' =>  1, 'amount' => 1.05, 'notes' => 'Pago manual - Luis Antonio Cedeño','status' => true,  'date' => '2026-05-16'],
            ['ref' =>  8, 'amount' => 1.85, 'notes' => 'Pago manual - Jean Molina',        'status' => true,  'date' => '2026-05-16'],
            ['ref' =>  3, 'amount' => 1.05, 'notes' => 'Pago manual - Katherine Cedeño',   'status' => true,  'date' => '2026-05-17'],
            ['ref' => 12, 'amount' => 1.05, 'notes' => 'Pago manual - Pedro Guerrero',     'status' => true,  'date' => '2026-05-17'],
            ['ref' =>  6, 'amount' => 1.05, 'notes' => 'Pago manual - Samara Guerrero',    'status' => true,  'date' => '2026-05-17'],
            ['ref' =>  5, 'amount' => 1.05, 'notes' => 'Pago manual - Dolores Posligua',   'status' => true,  'date' => '2026-05-17'],
            ['ref' => 10, 'amount' => 1.05, 'notes' => 'Pago manual - Dereck Cedeño',      'status' => true,  'date' => '2026-05-17'],
            ['ref' =>  4, 'amount' => 1.15, 'notes' => 'Pago manual - Luis Palemon Cedeño','status' => true,  'date' => '2026-05-18'],  // id 46
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago manual - Luis Antonio Cedeño','status' => true,  'date' => '2026-06-15'],
            ['ref' =>  3, 'amount' => 1.00, 'notes' => 'Pago manual - Katherine Cedeño',   'status' => true,  'date' => '2026-06-15'],
            ['ref' =>  6, 'amount' => 1.00, 'notes' => 'Pago manual - Samara Guerrero',    'status' => true,  'date' => '2026-06-15'],
            ['ref' => 12, 'amount' => 1.00, 'notes' => 'Pago manual - Pedro Guerrero',     'status' => true,  'date' => '2026-06-15'],
            ['ref' =>  5, 'amount' => 1.00, 'notes' => 'Pago manual - Dolores Posligua',   'status' => true,  'date' => '2026-06-15'],
            ['ref' => 10, 'amount' => 1.00, 'notes' => 'Pago manual - Dereck Cedeño',      'status' => true,  'date' => '2026-06-15'],
            ['ref' =>  4, 'amount' => 0.01, 'notes' => 'Pago manual - Luis Palemon Cedeño','status' => false, 'date' => '2026-06-17'],  // id 53
            ['ref' =>  2, 'amount' => 0.01, 'notes' => 'Pago manual - Shirley Cedeño',     'status' => false, 'date' => '2026-06-17'],  // id 54
            ['ref' =>  8, 'amount' => 0.01, 'notes' => 'Pago manual - Jean Molina',        'status' => false, 'date' => '2026-06-17'],  // id 55
        ];

        $created = 0;
        $cancelled = 0;

        foreach ($transactions as $row) {
            $memberOid = $memberMap[(string) $row['ref']];

            $dto = new DTOApplyPaymentRequest(
                memberOid:       $memberOid,
                campaignOid:     $campaignOid,
                amount:          $row['amount'],
                transactionDate: $row['date'],
                notes:           $row['notes'],
                createdByOid:    0,
            );

            $result = $service->applyPayment($dto);
            $created++;

            if ($row['status'] === false) {
                $service->cancel($result['transaction_uuid'], 0);
                $cancelled++;
                $this->command->line("  [cancelled] {$row['notes']} — {$result['transaction_uuid']}");
            } else {
                $this->command->line("  [created]   {$row['notes']} — \${$row['amount']}");
            }
        }

        $this->command->info("Done: {$created} transactions created, {$cancelled} cancelled.");
    }
}
