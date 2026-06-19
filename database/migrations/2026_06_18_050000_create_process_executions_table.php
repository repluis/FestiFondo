<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_executions', function (Blueprint $table) {
            $table->id();
            $table->string('process_name', 100);
            $table->string('process_key', 150)->unique(); // e.g. fees_penalties_2025_06 — idempotency key
            $table->date('execution_date');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('execution_status', 20)->default('running'); // running|completed|failed|cancelled
            $table->unsignedBigInteger('triggered_by_oid');
            $table->integer('members_processed')->default(0);
            $table->integer('fees_generated')->default(0);
            $table->integer('penalties_generated')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('process_name');
            $table->index('execution_date');
            $table->index('execution_status');
            $table->unique(['process_name', 'execution_date']); // anti-dup: one run per process per day
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_executions');
    }
};
