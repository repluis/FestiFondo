<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old unique: (member_oid, period_year, period_month)
        Schema::table('monthly_fees', function (Blueprint $table) {
            $table->dropUnique('monthly_fees_member_oid_period_year_period_month_unique');
        });

        // Add new unique: (member_oid, campaign_oid, period_year, period_month)
        DB::statement('
            CREATE UNIQUE INDEX monthly_fees_member_campaign_period_unique
            ON monthly_fees (member_oid, COALESCE(campaign_oid, 0), period_year, period_month)
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS monthly_fees_member_campaign_period_unique');

        Schema::table('monthly_fees', function (Blueprint $table) {
            $table->unique(['member_oid', 'period_year', 'period_month']);
        });
    }
};
