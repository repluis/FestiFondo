<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('member_balances', 'oid')) {
            Schema::table('member_balances', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.member_balances ALTER COLUMN oid SET NOT NULL;');
            DB::statement('ALTER TABLE public.member_balances ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
            DB::statement("SELECT setval('member_balances_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.member_balances));");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.member_balances ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
            DB::statement('ALTER TABLE public.member_balances ALTER COLUMN oid DROP NOT NULL;');
        }

        if (Schema::hasColumn('member_balances', 'oid')) {
            Schema::table('member_balances', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
