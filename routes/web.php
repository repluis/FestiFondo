<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Src\FundRaising\Application\Services\FundRaisingService;
use Src\Members\Application\Services\MembersService;
use Src\Transactions\Application\Services\TransactionService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/run-seeds', function (Request $request) {
    $token = env('SEED_TOKEN');

    if (! $token || $request->query('token') !== $token) {
        return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
    }

    if (DB::table('members')->count() > 0) {
        return response()->json(['success' => false, 'error' => 'Already seeded'], 409);
    }

    $log = [];

    try {
        // 1. Admin user
        User::create([
            'name'              => 'Test User',
            'email'             => 'test@example.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $log[] = 'admin user created';

        // 2. Members
        $membersData = [
            ['id' => 2,  'name' => 'Shirley Cedeño',      'email' => 'shirley.cedeno@familia.com'],
            ['id' => 3,  'name' => 'Katherine Cedeño',    'email' => 'katherine.cedeno@familia.com'],
            ['id' => 4,  'name' => 'Luis Palemon Cedeño', 'email' => 'luis.palemon.cedeno@familia.com'],
            ['id' => 5,  'name' => 'Dolores Posligua',    'email' => 'dolores.posligua@familia.com'],
            ['id' => 6,  'name' => 'Samara Guerrero',     'email' => 'samara.guerrero@familia.com'],
            ['id' => 7,  'name' => 'Ahilany Palma',       'email' => 'ahilany.palma@familia.com'],
            ['id' => 8,  'name' => 'Jean Molina',         'email' => 'jean.molina@familia.com'],
            ['id' => 9,  'name' => 'Jose Palma',          'email' => 'jose.palma@familia.com'],
            ['id' => 10, 'name' => 'Dereck Cedeño',       'email' => 'dereck.cedeno@familia.com'],
            ['id' => 11, 'name' => 'Jeremy Alava',        'email' => 'jeremy.alava@familia.com'],
            ['id' => 12, 'name' => 'Pedro Guerrero',      'email' => 'pedro.guerrero@familia.com'],
            ['id' => 1,  'name' => 'Luis Antonio Cedeño', 'email' => 'luis.antonio.cedeno@familia.com'],
        ];

        $membersService = app(MembersService::class);
        foreach ($membersData as $data) {
            $parts    = explode(' ', $data['name']);
            $lastName = array_pop($parts);
            $membersService->create(new \Src\Members\Application\DTOs\DTOCreateMembersRequest(
                identification: (string) $data['id'],
                firstName:      implode(' ', $parts),
                lastName:       $lastName,
                email:          $data['email'],
                phone:          null,
                address:        null,
                notes:          null,
                joinedAt:       '2026-01-15',
                createdByOid:   0,
            ));
        }
        $log[] = count($membersData) . ' members created';

        // 3. Fund raising
        $memberOids = DB::table('members')->orderBy('oid')->pluck('oid')->toArray();
        $fundRaisingService = app(FundRaisingService::class);
        $fundRaisingService->create(new \Src\FundRaising\Application\DTOs\DTOCreateFundRaisingRequest(
            name:         'recaudacion_navidad_2026',
            description:  'Recaudación de fondos para Navidad 2026',
            targetAmount: 1200.00,
            startDate:    '2026-01-15',
            endDate:      '2026-12-25',
            createdByOid: 0,
            memberOids:   $memberOids,
        ));
        DB::table('fund_raisings')->where('name', 'recaudacion_navidad_2026')->update([
            'fund_raising_status' => 'active',
            'monthly_fee_amount'  => 1.00,
            'daily_penalty_rate'  => 0.05,
            'due_day'             => 15,
            'collected_amount'    => 0.00,
        ]);
        $log[] = 'fund raising created and activated';

        // 4. Transactions
        $campaignOid = DB::table('fund_raisings')->where('name', 'recaudacion_navidad_2026')->value('oid');
        $memberMap   = DB::table('members')->pluck('oid', 'identification')->toArray();

        $transactions = [
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  2, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  3, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  4, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  5, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  6, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  7, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  8, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  9, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' => 10, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' => 11, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' => 12, 'amount' => 1.00, 'notes' => 'Pago cuota navidad enero 2026',     'status' => true,  'date' => '2026-01-15'],
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'date' => '2026-02-20'],
            ['ref' => 12, 'amount' => 1.15, 'notes' => 'Pago manual - Pedro Guerrero',      'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  6, 'amount' => 1.15, 'notes' => 'Pago manual - Samara Guerrero',     'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  3, 'amount' => 1.15, 'notes' => 'Pago manual - Katherine Cedeño',    'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  5, 'amount' => 1.05, 'notes' => 'Pago manual - Dolores Posligua',    'status' => true,  'date' => '2026-02-20'],
            ['ref' => 10, 'amount' => 1.05, 'notes' => 'Pago manual - Dereck Cedeño',       'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  4, 'amount' => 1.10, 'notes' => 'Pago manual - Luis Palemon Cedeño', 'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  8, 'amount' => 1.00, 'notes' => 'Pago manual - Jean Molina',         'status' => true,  'date' => '2026-02-20'],
            ['ref' =>  3, 'amount' => 1.00, 'notes' => 'Pago manual - Katherine Cedeño',    'status' => true,  'date' => '2026-03-15'],
            ['ref' =>  6, 'amount' => 1.00, 'notes' => 'Pago manual - Samara Guerrero',     'status' => true,  'date' => '2026-03-15'],
            ['ref' => 12, 'amount' => 1.00, 'notes' => 'Pago manual - Pedro Guerrero',      'status' => true,  'date' => '2026-03-15'],
            ['ref' =>  5, 'amount' => 1.00, 'notes' => 'Pago manual - Dolores Posligua',    'status' => true,  'date' => '2026-03-15'],
            ['ref' => 10, 'amount' => 1.00, 'notes' => 'Pago manual - Dereck Cedeño',       'status' => true,  'date' => '2026-03-15'],
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'date' => '2026-03-15'],
            ['ref' =>  4, 'amount' => 1.70, 'notes' => 'Pago manual - Luis Palemon Cedeño', 'status' => true,  'date' => '2026-03-29'],
            ['ref' =>  8, 'amount' => 3.10, 'notes' => 'Pago manual - Jean Molina',         'status' => true,  'date' => '2026-03-31'],
            ['ref' =>  8, 'amount' => 0.10, 'notes' => 'Pago manual - Jean Molina',         'status' => true,  'date' => '2026-04-03'],
            ['ref' =>  6, 'amount' => 1.00, 'notes' => 'Pago manual - Samara Guerrero',     'status' => true,  'date' => '2026-04-16'],
            ['ref' =>  5, 'amount' => 1.00, 'notes' => 'Pago manual - Dolores Posligua',    'status' => true,  'date' => '2026-04-16'],
            ['ref' =>  3, 'amount' => 1.00, 'notes' => 'Pago manual - Katherine Cedeño',    'status' => true,  'date' => '2026-04-16'],
            ['ref' => 12, 'amount' => 1.00, 'notes' => 'Pago manual - Pedro Guerrero',      'status' => true,  'date' => '2026-04-16'],
            ['ref' => 10, 'amount' => 1.00, 'notes' => 'Pago manual - Dereck Cedeño',       'status' => true,  'date' => '2026-04-16'],
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'date' => '2026-04-16'],
            ['ref' =>  4, 'amount' => 1.25, 'notes' => 'Pago manual - Luis Palemon Cedeño', 'status' => true,  'date' => '2026-04-20'],
            ['ref' =>  8, 'amount' => 1.25, 'notes' => 'Pago manual - Jean Molina',         'status' => true,  'date' => '2026-05-06'],
            ['ref' =>  8, 'amount' => 1.00, 'notes' => 'Pago manual - Jean Molina',         'status' => true,  'date' => '2026-05-06'],
            ['ref' =>  1, 'amount' => 1.05, 'notes' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'date' => '2026-05-16'],
            ['ref' =>  8, 'amount' => 1.85, 'notes' => 'Pago manual - Jean Molina',         'status' => true,  'date' => '2026-05-16'],
            ['ref' =>  3, 'amount' => 1.05, 'notes' => 'Pago manual - Katherine Cedeño',    'status' => true,  'date' => '2026-05-17'],
            ['ref' => 12, 'amount' => 1.05, 'notes' => 'Pago manual - Pedro Guerrero',      'status' => true,  'date' => '2026-05-17'],
            ['ref' =>  6, 'amount' => 1.05, 'notes' => 'Pago manual - Samara Guerrero',     'status' => true,  'date' => '2026-05-17'],
            ['ref' =>  5, 'amount' => 1.05, 'notes' => 'Pago manual - Dolores Posligua',    'status' => true,  'date' => '2026-05-17'],
            ['ref' => 10, 'amount' => 1.05, 'notes' => 'Pago manual - Dereck Cedeño',       'status' => true,  'date' => '2026-05-17'],
            ['ref' =>  4, 'amount' => 1.15, 'notes' => 'Pago manual - Luis Palemon Cedeño', 'status' => true,  'date' => '2026-05-18'],
            ['ref' =>  1, 'amount' => 1.00, 'notes' => 'Pago manual - Luis Antonio Cedeño', 'status' => true,  'date' => '2026-06-15'],
            ['ref' =>  3, 'amount' => 1.00, 'notes' => 'Pago manual - Katherine Cedeño',    'status' => true,  'date' => '2026-06-15'],
            ['ref' =>  6, 'amount' => 1.00, 'notes' => 'Pago manual - Samara Guerrero',     'status' => true,  'date' => '2026-06-15'],
            ['ref' => 12, 'amount' => 1.00, 'notes' => 'Pago manual - Pedro Guerrero',      'status' => true,  'date' => '2026-06-15'],
            ['ref' =>  5, 'amount' => 1.00, 'notes' => 'Pago manual - Dolores Posligua',    'status' => true,  'date' => '2026-06-15'],
            ['ref' => 10, 'amount' => 1.00, 'notes' => 'Pago manual - Dereck Cedeño',       'status' => true,  'date' => '2026-06-15'],
            ['ref' =>  4, 'amount' => 0.01, 'notes' => 'Pago manual - Luis Palemon Cedeño', 'status' => false, 'date' => '2026-06-17'],
            ['ref' =>  2, 'amount' => 0.01, 'notes' => 'Pago manual - Shirley Cedeño',      'status' => false, 'date' => '2026-06-17'],
            ['ref' =>  8, 'amount' => 0.01, 'notes' => 'Pago manual - Jean Molina',         'status' => false, 'date' => '2026-06-17'],
        ];

        $transactionService = app(TransactionService::class);
        $txCreated = 0;
        $txCancelled = 0;
        foreach ($transactions as $row) {
            $result = $transactionService->applyPayment(new \Src\Transactions\Application\DTOs\DTOApplyPaymentRequest(
                memberOid:       $memberMap[(string) $row['ref']],
                campaignOid:     $campaignOid,
                amount:          $row['amount'],
                transactionDate: $row['date'],
                notes:           $row['notes'],
                createdByOid:    0,
            ));
            $txCreated++;
            if ($row['status'] === false) {
                $transactionService->cancel($result['transaction_uuid'], 0);
                $txCancelled++;
            }
        }
        $log[] = "{$txCreated} transactions created, {$txCancelled} cancelled";

        // 5. Full state (monthly fees + penalties)
        $groupA = ['1','3','5','6','10','12'];
        $groupB = ['4','8'];
        $groupC = ['2','7','9','11'];
        $months = [
            ['year' => 2026, 'month' => 1, 'due' => '2026-01-15'],
            ['year' => 2026, 'month' => 2, 'due' => '2026-02-15'],
            ['year' => 2026, 'month' => 3, 'due' => '2026-03-15'],
            ['year' => 2026, 'month' => 4, 'due' => '2026-04-15'],
            ['year' => 2026, 'month' => 5, 'due' => '2026-05-15'],
            ['year' => 2026, 'month' => 6, 'due' => '2026-06-15'],
        ];
        $now  = now()->toDateTimeString();
        $fees = [];
        foreach ($months as $mo) {
            foreach ($memberMap as $identification => $oid) {
                $isPaid = match (true) {
                    in_array($identification, $groupA)                       => true,
                    in_array($identification, $groupB) && $mo['month'] <= 5  => true,
                    in_array($identification, $groupC) && $mo['month'] === 1 => true,
                    default                                                  => false,
                };
                $fees[] = [
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
        foreach (array_chunk($fees, 50) as $chunk) {
            DB::table('monthly_fees')->insert($chunk);
        }
        $log[] = count($fees) . ' monthly fees inserted';

        $penalties = [];
        foreach ($groupB as $id) {
            $oid = $memberMap[$id];
            for ($day = 16; $day <= 19; $day++) {
                $penalties[] = [
                    'member_oid' => $oid, 'campaign_oid' => $campaignOid,
                    'period_year' => 2026, 'period_month' => 6,
                    'penalty_date' => "2026-06-{$day}", 'days_overdue' => $day - 15,
                    'daily_rate_snapshot' => 0.05, 'amount' => 0.05,
                    'amount_paid' => 0.00, 'balance' => 0.05,
                    'penalty_status' => 'pending', 'generated_by_process_oid' => null,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }
        $start   = \Carbon\Carbon::parse('2026-02-16');
        $end     = \Carbon\Carbon::parse('2026-06-19');
        $baseDue = \Carbon\Carbon::parse('2026-02-15');
        $dates   = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates[] = $d->copy();
        }
        foreach ($groupC as $id) {
            $oid = $memberMap[$id];
            foreach ($dates as $date) {
                $penalties[] = [
                    'member_oid' => $oid, 'campaign_oid' => $campaignOid,
                    'period_year' => 2026, 'period_month' => $date->month,
                    'penalty_date' => $date->toDateString(),
                    'days_overdue' => max(1, (int) $baseDue->diffInDays($date)),
                    'daily_rate_snapshot' => 0.05, 'amount' => 0.05,
                    'amount_paid' => 0.00, 'balance' => 0.05,
                    'penalty_status' => 'pending', 'generated_by_process_oid' => null,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($penalties, 100) as $chunk) {
            DB::table('penalties')->insert($chunk);
        }
        $log[] = count($penalties) . ' penalties inserted';

        $collected = DB::table('transactions')
            ->where('campaign_oid', $campaignOid)
            ->where('status', true)
            ->sum('amount');
        DB::table('fund_raisings')->where('oid', $campaignOid)
            ->update(['collected_amount' => round($collected, 2)]);
        $log[] = "collected_amount updated → \${$collected}";

        return response()->json(['success' => true, 'log' => $log]);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage(), 'log' => $log], 500);
    }
});
