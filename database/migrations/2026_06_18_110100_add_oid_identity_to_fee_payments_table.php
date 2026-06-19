<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('fee_payments', 'oid')) {
            Schema::table('fee_payments', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.fee_payments ALTER COLUMN oid SET NOT NULL;');
            DB::statement('ALTER TABLE public.fee_payments ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
            DB::statement("SELECT setval('fee_payments_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.fee_payments));");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.fee_payments ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
            DB::statement('ALTER TABLE public.fee_payments ALTER COLUMN oid DROP NOT NULL;');
        }

        if (Schema::hasColumn('fee_payments', 'oid')) {
            Schema::table('fee_payments', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
