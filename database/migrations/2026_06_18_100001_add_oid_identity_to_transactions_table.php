<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if oid is already an IDENTITY column
        $isIdentity = DB::selectOne("
            SELECT 1 FROM information_schema.columns
            WHERE table_name = 'transactions'
              AND column_name = 'oid'
              AND is_identity = 'YES'
        ");
        if ($isIdentity) {
            return;
        }

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
        if (Schema::hasColumn('transactions', 'oid')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
