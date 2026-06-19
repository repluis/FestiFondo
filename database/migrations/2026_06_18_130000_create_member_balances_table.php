<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_oid')->unique(); // one row per member

            // Fees
            $table->decimal('total_fees_charged', 12, 2)->default(0);
            $table->decimal('total_fees_paid', 12, 2)->default(0);
            $table->decimal('fees_balance', 12, 2)->default(0);          // charged - paid

            // Penalties
            $table->decimal('total_penalties_charged', 12, 2)->default(0);
            $table->decimal('total_penalties_paid', 12, 2)->default(0);
            $table->decimal('penalties_balance', 12, 2)->default(0);

            // Totals
            $table->decimal('total_balance', 12, 2)->default(0);          // fees_balance + penalties_balance

            // Last payment snapshot
            $table->date('last_payment_date')->nullable();
            $table->decimal('last_payment_amount', 10, 2)->nullable();

            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->index('total_balance');
            $table->index('last_payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_balances');
    }
};
