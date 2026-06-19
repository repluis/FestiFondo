<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_oid');
            $table->smallInteger('period_year');
            $table->smallInteger('period_month');              // 1-12
            $table->date('penalty_date');                      // day the penalty was generated
            $table->integer('days_overdue');
            $table->decimal('daily_rate_snapshot', 10, 4);    // rate at generation time
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);    // app-maintained
            $table->string('penalty_status', 20)->default('pending'); // pending|partial|paid|waived|adjusted
            $table->unsignedBigInteger('generated_by_process_oid')->nullable();
            $table->timestamps();

            $table->unique(['member_oid', 'penalty_date']); // anti-dup: one penalty per member per day
            $table->index('member_oid');
            $table->index('penalty_date');
            $table->index('penalty_status');
            $table->index(['period_year', 'period_month']);
        });

        DB::statement('ALTER TABLE penalties ADD CONSTRAINT chk_penalties_month CHECK (period_month BETWEEN 1 AND 12);');
        DB::statement('ALTER TABLE penalties ADD CONSTRAINT chk_days_overdue CHECK (days_overdue >= 1);');
    }

    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
