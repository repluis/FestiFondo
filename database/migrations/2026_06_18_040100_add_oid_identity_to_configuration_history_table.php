<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('configuration_history', 'oid')) {
            Schema::table('configuration_history', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.configuration_history ALTER COLUMN oid SET NOT NULL;');
            DB::statement('ALTER TABLE public.configuration_history ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
            DB::statement("SELECT setval('configuration_history_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.configuration_history));");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.configuration_history ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
            DB::statement('ALTER TABLE public.configuration_history ALTER COLUMN oid DROP NOT NULL;');
        }

        if (Schema::hasColumn('configuration_history', 'oid')) {
            Schema::table('configuration_history', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
