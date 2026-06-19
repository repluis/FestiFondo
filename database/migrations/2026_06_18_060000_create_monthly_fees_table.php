<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_oid');
            $table->smallInteger('period_year');
            $table->smallInteger('period_month');              // 1-12
            $table->date('due_date');
            $table->decimal('amount', 10, 2);                  // snapshot at generation time
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);     // amount - amount_paid (app-maintained)
            $table->string('fee_status', 20)->default('pending'); // pending|partial|paid|cancelled
            $table->unsignedBigInteger('generated_by_process_oid')->nullable();
            $table->unsignedBigInteger('cancelled_by_oid')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->unique(['member_oid', 'period_year', 'period_month']); // anti-dup: one fee per member per month
            $table->index('member_oid');
            $table->index(['period_year', 'period_month']);
            $table->index('fee_status');
            $table->index('due_date');
        });

        // PostgreSQL: enforce valid month range
        DB::statement('ALTER TABLE monthly_fees ADD CONSTRAINT chk_period_month CHECK (period_month BETWEEN 1 AND 12);');
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_fees');
    }
};
