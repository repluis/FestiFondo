<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'oid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        // PostgreSQL-specific: NOT NULL and IDENTITY column setup
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.users ALTER COLUMN oid SET NOT NULL;');
            DB::statement('ALTER TABLE public.users ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
            DB::statement("SELECT setval('users_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.users));");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.users ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
            DB::statement('ALTER TABLE public.users ALTER COLUMN oid DROP NOT NULL;');
        }

        if (Schema::hasColumn('users', 'oid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
