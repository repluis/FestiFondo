<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('penalties', 'campaign_oid')) {
            Schema::table('penalties', function (Blueprint $table) {
                $table->unsignedBigInteger('campaign_oid')->nullable()->after('member_oid');
                $table->index('campaign_oid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penalties', 'campaign_oid')) {
            Schema::table('penalties', function (Blueprint $table) {
                $table->dropIndex(['campaign_oid']);
                $table->dropColumn('campaign_oid');
            });
        }
    }
};
