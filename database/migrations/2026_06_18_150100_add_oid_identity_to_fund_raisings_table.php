<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('fund_raisings', 'oid')) {
            Schema::table('fund_raisings', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }
        DB::statement('ALTER TABLE public.fund_raisings ALTER COLUMN oid SET NOT NULL;');
        DB::statement('ALTER TABLE public.fund_raisings ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
        DB::statement("SELECT setval('fund_raisings_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.fund_raisings));");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE public.fund_raisings ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
        DB::statement('ALTER TABLE public.fund_raisings ALTER COLUMN oid DROP NOT NULL;');
        if (Schema::hasColumn('fund_raisings', 'oid')) {
            Schema::table('fund_raisings', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
