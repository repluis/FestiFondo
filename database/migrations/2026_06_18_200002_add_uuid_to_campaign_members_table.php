<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('campaign_members', 'uuid')) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
            Schema::table('campaign_members', function (Blueprint $table) {
                $table->uuid('uuid')
                      ->default(DB::raw('gen_random_uuid()'))
                      ->unique()
                      ->after('oid');
            });
            DB::statement('UPDATE campaign_members SET uuid = gen_random_uuid() WHERE uuid IS NULL;');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('campaign_members', 'uuid')) {
            Schema::table('campaign_members', function (Blueprint $table) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }
    }
};
