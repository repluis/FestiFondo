<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('process_executions', 'status')) {
            Schema::table('process_executions', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('uuid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('process_executions', 'status')) {
            Schema::table('process_executions', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
