<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('audit_logs', 'oid')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.audit_logs ALTER COLUMN oid SET NOT NULL;');
            DB::statement('ALTER TABLE public.audit_logs ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
            DB::statement("SELECT setval('audit_logs_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.audit_logs));");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE public.audit_logs ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
            DB::statement('ALTER TABLE public.audit_logs ALTER COLUMN oid DROP NOT NULL;');
        }

        if (Schema::hasColumn('audit_logs', 'oid')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
