<?php

use App\Models\User;
use Database\Seeders\FundRaisingSeeder;
use Database\Seeders\FullStateSeeder;
use Database\Seeders\MembersSeeder;
use Database\Seeders\TransactionsSeeder;
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
        User::create([
            'name'              => 'Test User',
            'email'             => 'test@example.com',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $log[] = 'admin user created';

        app(MembersSeeder::class)->run(app(MembersService::class));
        $log[] = 'members seeded';

        app(FundRaisingSeeder::class)->run(app(FundRaisingService::class));
        $log[] = 'fund raising seeded';

        app(TransactionsSeeder::class)->run(app(TransactionService::class));
        $log[] = 'transactions seeded';

        app(FullStateSeeder::class)->run();
        $log[] = 'full state seeded';

        return response()->json(['success' => true, 'log' => $log]);
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage(), 'log' => $log], 500);
    }
});
