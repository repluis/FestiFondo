<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('members', 'oid')) {
            Schema::table('members', function (Blueprint $table) {
                $table->bigInteger('oid')->nullable()->after('id');
            });
        }

        DB::statement('ALTER TABLE public.members ALTER COLUMN oid SET NOT NULL;');
        DB::statement('ALTER TABLE public.members ALTER COLUMN oid ADD GENERATED ALWAYS AS IDENTITY;');
        DB::statement("SELECT setval('members_oid_seq', (SELECT COALESCE(MAX(oid), 1) FROM public.members));");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE public.members ALTER COLUMN oid DROP IDENTITY IF EXISTS;');
        DB::statement('ALTER TABLE public.members ALTER COLUMN oid DROP NOT NULL;');

        if (Schema::hasColumn('members', 'oid')) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('oid');
            });
        }
    }
};
