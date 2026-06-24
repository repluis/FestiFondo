<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('campaign_members', 'oid')) {
            Schema::table('campaign_members', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.campaign_members ALTER COLUMN oid SET NOT NULL;');
            DB::statement('ALTER TABLE public.campaign_members ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
            DB::statement("SELECT setval('campaign_members_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.campaign_members));");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.campaign_members ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
        }
        if (Schema::hasColumn('campaign_members', 'oid')) {
            Schema::table('campaign_members', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
