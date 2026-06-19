<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('previous_penalties_balance', 10, 2)->nullable()->after('campaign_oid');
            $table->decimal('new_penalties_balance',      10, 2)->nullable()->after('previous_penalties_balance');
            $table->decimal('previous_fees_balance',      10, 2)->nullable()->after('new_penalties_balance');
            $table->decimal('new_fees_balance',           10, 2)->nullable()->after('previous_fees_balance');
            $table->decimal('applied_to_penalties',       10, 2)->default(0)->after('new_fees_balance');
            $table->decimal('applied_to_fees',            10, 2)->default(0)->after('applied_to_penalties');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'previous_penalties_balance',
                'new_penalties_balance',
                'previous_fees_balance',
                'new_fees_balance',
                'applied_to_penalties',
                'applied_to_fees',
            ]);
        });
    }
};
