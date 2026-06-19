<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('member_balances', 'status')) {
            Schema::table('member_balances', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('uuid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('member_balances', 'status')) {
            Schema::table('member_balances', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
