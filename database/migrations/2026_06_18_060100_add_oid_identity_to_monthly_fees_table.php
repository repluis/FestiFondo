<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('monthly_fees', 'oid')) {
            Schema::table('monthly_fees', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.monthly_fees ALTER COLUMN oid SET NOT NULL;');
            DB::statement('ALTER TABLE public.monthly_fees ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
            DB::statement("SELECT setval('monthly_fees_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.monthly_fees));");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.monthly_fees ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
            DB::statement('ALTER TABLE public.monthly_fees ALTER COLUMN oid DROP NOT NULL;');
        }

        if (Schema::hasColumn('monthly_fees', 'oid')) {
            Schema::table('monthly_fees', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
