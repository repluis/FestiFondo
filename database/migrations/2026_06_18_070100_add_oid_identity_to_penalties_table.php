<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('penalties', 'oid')) {
            Schema::table('penalties', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.penalties ALTER COLUMN oid SET NOT NULL;');
            DB::statement('ALTER TABLE public.penalties ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
            DB::statement("SELECT setval('penalties_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.penalties));");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.penalties ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
            DB::statement('ALTER TABLE public.penalties ALTER COLUMN oid DROP NOT NULL;');
        }

        if (Schema::hasColumn('penalties', 'oid')) {
            Schema::table('penalties', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
