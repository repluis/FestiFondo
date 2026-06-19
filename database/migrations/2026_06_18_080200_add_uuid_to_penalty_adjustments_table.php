<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('penalty_adjustments', 'uuid')) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
            Schema::table('penalty_adjustments', function (Blueprint $table) {
                $table->uuid('uuid')
                      ->default(DB::raw('gen_random_uuid()'))
                      ->unique()
                      ->after('oid');
            });
            DB::statement('UPDATE penalty_adjustments SET uuid = gen_random_uuid() WHERE uuid IS NULL;');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penalty_adjustments', 'uuid')) {
            Schema::table('penalty_adjustments', function (Blueprint $table) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }
    }
};
