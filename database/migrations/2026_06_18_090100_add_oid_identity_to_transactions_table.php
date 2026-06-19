<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transactions', 'oid')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        DB::statement('ALTER TABLE public.transactions ALTER COLUMN oid SET NOT NULL;');
        DB::statement('ALTER TABLE public.transactions ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
        DB::statement("SELECT setval('transactions_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.transactions));");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE public.transactions ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
        DB::statement('ALTER TABLE public.transactions ALTER COLUMN oid DROP NOT NULL;');

        if (Schema::hasColumn('transactions', 'oid')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
