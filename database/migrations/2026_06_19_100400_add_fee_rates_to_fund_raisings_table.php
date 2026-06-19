<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_raisings', function (Blueprint $table) {
            if (!Schema::hasColumn('fund_raisings', 'monthly_fee_amount')) {
                $table->decimal('monthly_fee_amount', 10, 2)->default(1.00)->after('target_amount');
            }
            if (!Schema::hasColumn('fund_raisings', 'daily_penalty_rate')) {
                $table->decimal('daily_penalty_rate', 8, 4)->default(0.0500)->after('monthly_fee_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fund_raisings', function (Blueprint $table) {
            $table->dropColumn(['monthly_fee_amount', 'daily_penalty_rate']);
        });
    }
};
