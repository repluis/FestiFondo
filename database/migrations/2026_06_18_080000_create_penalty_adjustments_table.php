<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penalty_oid');
            $table->unsignedBigInteger('member_oid');          // denormalized for fast queries
            $table->string('adjustment_type', 20);             // add|reduce|waive|correct|discount|forgive
            $table->decimal('previous_balance', 10, 2);
            $table->decimal('adjustment_amount', 10, 2);       // negative = reduction
            $table->decimal('new_balance', 10, 2);
            $table->text('reason');
            $table->unsignedBigInteger('adjusted_by_oid');
            $table->timestamp('adjusted_at');
            $table->timestamps();

            $table->index('penalty_oid');
            $table->index('member_oid');
            $table->index('adjusted_by_oid');
            $table->index('adjusted_at');
            $table->index('adjustment_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_adjustments');
    }
};
