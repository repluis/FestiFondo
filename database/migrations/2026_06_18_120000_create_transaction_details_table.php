<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_receipt_oid');
            $table->unsignedBigInteger('member_oid');            // denormalized
            $table->string('detail_type', 20);                   // penalty|monthly_fee
            $table->string('reference_type', 50);                // penalties|monthly_fees
            $table->unsignedBigInteger('reference_oid');         // polymorphic
            $table->decimal('amount_applied', 10, 2);
            $table->unsignedInteger('applied_order');            // 1=first penalty, 2=oldest fee…
            $table->timestamps();

            $table->index('payment_receipt_oid');
            $table->index('member_oid');
            $table->index(['reference_type', 'reference_oid']);
            $table->index('applied_order');
        });

        DB::statement('ALTER TABLE transaction_details ADD CONSTRAINT chk_td_amount_applied CHECK (amount_applied > 0);');
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
