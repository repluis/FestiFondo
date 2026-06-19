<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Copy any data from 'type' into 'transaction_type' where transaction_type is empty
        if (Schema::hasColumn('transactions', 'type') && Schema::hasColumn('transactions', 'transaction_type')) {
            DB::statement("UPDATE transactions SET transaction_type = type WHERE transaction_type IS NULL OR transaction_type = ''");
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        // Ensure member_oid column exists (was added as 'member_oid' in migration 100004)
        // No action needed — already added.
    }

    public function down(): void
    {
        if (!Schema::hasColumn('transactions', 'type')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('type', 20)->nullable()->after('status');
            });
        }
    }
};
