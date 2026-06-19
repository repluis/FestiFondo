<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old unique: (member_oid, penalty_date)
        Schema::table('penalties', function (Blueprint $table) {
            $table->dropUnique('penalties_member_oid_penalty_date_unique');
        });

        // Add new unique: (member_oid, campaign_oid, penalty_date)
        DB::statement('
            CREATE UNIQUE INDEX penalties_member_campaign_date_unique
            ON penalties (member_oid, COALESCE(campaign_oid, 0), penalty_date)
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS penalties_member_campaign_date_unique');

        Schema::table('penalties', function (Blueprint $table) {
            $table->unique(['member_oid', 'penalty_date']);
        });
    }
};
