<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'previous_total_paid')) {
                $table->decimal('previous_total_paid', 10, 2)->nullable()->after('applied_to_fees');
            }
            if (!Schema::hasColumn('transactions', 'new_total_paid')) {
                $table->decimal('new_total_paid', 10, 2)->nullable()->after('previous_total_paid');
            }
        });

        $memberOids = DB::table('transactions')
            ->where('transaction_type', 'income')
            ->where('status', true)
            ->distinct()
            ->pluck('member_oid');

        foreach ($memberOids as $memberOid) {
            $rows = DB::table('transactions')
                ->where('member_oid', $memberOid)
                ->where('transaction_type', 'income')
                ->where('status', true)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get(['id', 'amount']);

            $running = 0.0;
            foreach ($rows as $row) {
                $previous = $running;
                $running  = round($running + (float) $row->amount, 2);

                DB::table('transactions')->where('id', $row->id)->update([
                    'previous_total_paid' => $previous,
                    'new_total_paid'      => $running,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['previous_total_paid', 'new_total_paid']);
        });
    }
};
