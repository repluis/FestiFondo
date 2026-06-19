<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            return;
        }
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);             // 'income' | 'expense'
            $table->bigInteger('member_oid')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->string('description', 255);
            $table->string('reference', 100)->nullable();
            $table->date('transaction_date')->index();
            $table->text('notes')->nullable();
            $table->bigInteger('created_by_oid')->nullable();
            $table->bigInteger('updated_by_oid')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
