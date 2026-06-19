<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_receipt_oid');
            $table->unsignedBigInteger('monthly_fee_oid');
            $table->unsignedBigInteger('member_oid');           // denormalized
            $table->decimal('amount_applied', 10, 2);
            $table->timestamps();

            $table->unique(['payment_receipt_oid', 'monthly_fee_oid']); // anti-dup
            $table->index('payment_receipt_oid');
            $table->index('monthly_fee_oid');
            $table->index('member_oid');
        });

        DB::statement('ALTER TABLE fee_payments ADD CONSTRAINT chk_fee_amount_applied CHECK (amount_applied > 0);');
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
