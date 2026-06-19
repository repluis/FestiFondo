<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The pre-existing transactions table has legacy columns that may be NOT NULL.
        // Our module doesn't use them, so we make them nullable to avoid constraint violations.
        $legacyCols = ['direction', 'reference_type', 'reference_oid', 'processed_by_oid'];

        foreach ($legacyCols as $col) {
            if (Schema::hasColumn('transactions', $col)) {
                DB::statement("ALTER TABLE transactions ALTER COLUMN {$col} DROP NOT NULL");
            }
        }
    }

    public function down(): void
    {
        // Cannot reliably restore NOT NULL constraints on legacy columns without knowing
        // which ones were originally NOT NULL.
    }
};
