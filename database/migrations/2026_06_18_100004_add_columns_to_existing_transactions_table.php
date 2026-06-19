<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'type')) {
                $table->string('type', 20)->default('income')->after('status');
            }
            if (!Schema::hasColumn('transactions', 'member_oid')) {
                $table->bigInteger('member_oid')->nullable()->after('type');
            }
            if (!Schema::hasColumn('transactions', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('member_oid');
            }
            if (!Schema::hasColumn('transactions', 'description')) {
                $table->string('description', 255)->default('')->after('amount');
            }
            if (!Schema::hasColumn('transactions', 'reference')) {
                $table->string('reference', 100)->nullable()->after('description');
            }
            if (!Schema::hasColumn('transactions', 'transaction_date')) {
                $table->date('transaction_date')->nullable()->after('reference');
            }
            if (!Schema::hasColumn('transactions', 'notes')) {
                $table->text('notes')->nullable()->after('transaction_date');
            }
            if (!Schema::hasColumn('transactions', 'created_by_oid')) {
                $table->bigInteger('created_by_oid')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('transactions', 'updated_by_oid')) {
                $table->bigInteger('updated_by_oid')->nullable()->after('created_by_oid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $cols = ['type','member_oid','amount','description','reference',
                     'transaction_date','notes','created_by_oid','updated_by_oid'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
