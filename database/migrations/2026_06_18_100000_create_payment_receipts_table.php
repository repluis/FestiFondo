<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_oid');
            $table->string('receipt_number', 50)->unique();    // unique receipt identifier
            $table->date('payment_date');
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method', 30)->default('cash'); // cash|bank_transfer|check|other
            $table->string('payment_reference', 150)->nullable();   // bank transaction ref
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('received_by_oid');
            $table->boolean('is_reversed')->default(false);
            $table->unsignedBigInteger('reversed_by_oid')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->timestamps();

            $table->index('member_oid');
            $table->index('payment_date');
            $table->index('is_reversed');
            $table->index('receipt_number');
        });

        DB::statement('ALTER TABLE payment_receipts ADD CONSTRAINT chk_total_amount CHECK (total_amount > 0);');
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
