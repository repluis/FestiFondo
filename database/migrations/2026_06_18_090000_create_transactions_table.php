<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_oid');
            // fee_payment|penalty_payment|fee_adjustment|penalty_adjustment|reversal|refund
            $table->string('transaction_type', 30);
            $table->string('direction', 10); // debit (member owes more) | credit (payment/reduction)
            $table->decimal('amount', 10, 2);
            $table->string('reference_type', 50)->nullable(); // monthly_fees|penalties|payment_receipts|penalty_adjustments
            $table->unsignedBigInteger('reference_oid')->nullable(); // polymorphic FK
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->unsignedBigInteger('processed_by_oid');
            $table->timestamps();

            $table->index('member_oid');
            $table->index('transaction_type');
            $table->index('direction');
            $table->index('transaction_date');
            $table->index(['reference_type', 'reference_oid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
