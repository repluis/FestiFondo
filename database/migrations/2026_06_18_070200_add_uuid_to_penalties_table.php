<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('penalties', 'uuid')) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
            Schema::table('penalties', function (Blueprint $table) {
                $table->uuid('uuid')
                      ->default(DB::raw('gen_random_uuid()'))
                      ->unique()
                      ->after('oid');
            });
            DB::statement('UPDATE penalties SET uuid = gen_random_uuid() WHERE uuid IS NULL;');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penalties', 'uuid')) {
            Schema::table('penalties', function (Blueprint $table) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }
    }
};
