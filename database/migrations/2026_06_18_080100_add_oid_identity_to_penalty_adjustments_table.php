<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('penalty_adjustments', 'oid')) {
            Schema::table('penalty_adjustments', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.penalty_adjustments ALTER COLUMN oid SET NOT NULL;');
            DB::statement('ALTER TABLE public.penalty_adjustments ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
            DB::statement("SELECT setval('penalty_adjustments_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.penalty_adjustments));");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.penalty_adjustments ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
            DB::statement('ALTER TABLE public.penalty_adjustments ALTER COLUMN oid DROP NOT NULL;');
        }

        if (Schema::hasColumn('penalty_adjustments', 'oid')) {
            Schema::table('penalty_adjustments', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
