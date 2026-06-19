<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('audit_logs', 'status')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('uuid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('audit_logs', 'status')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
