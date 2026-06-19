<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_raisings', function (Blueprint $table) {
            if (!Schema::hasColumn('fund_raisings', 'due_day')) {
                $table->unsignedTinyInteger('due_day')->default(15)->after('daily_penalty_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fund_raisings', function (Blueprint $table) {
            $table->dropColumn('due_day');
        });
    }
};
