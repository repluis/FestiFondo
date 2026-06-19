<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transaction_details', 'oid')) {
            Schema::table('transaction_details', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        DB::statement('ALTER TABLE public.transaction_details ALTER COLUMN oid SET NOT NULL;');
        DB::statement('ALTER TABLE public.transaction_details ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
        DB::statement("SELECT setval('transaction_details_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.transaction_details));");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE public.transaction_details ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
        DB::statement('ALTER TABLE public.transaction_details ALTER COLUMN oid DROP NOT NULL;');

        if (Schema::hasColumn('transaction_details', 'oid')) {
            Schema::table('transaction_details', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
