<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_raisings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('target_amount', 12, 2);
            $table->decimal('collected_amount', 12, 2)->default(0); // app-maintained
            $table->date('start_date');
            $table->date('end_date')->nullable();
            // draft|active|completed|cancelled
            $table->string('fund_raising_status', 20)->default('draft');
            $table->unsignedBigInteger('created_by_oid')->nullable();
            $table->unsignedBigInteger('updated_by_oid')->nullable();
            $table->timestamps();

            $table->index('fund_raising_status');
            $table->index('start_date');
            $table->index('end_date');
        });

        DB::statement('ALTER TABLE fund_raisings ADD CONSTRAINT chk_fr_target CHECK (target_amount > 0);');
        DB::statement('ALTER TABLE fund_raisings ADD CONSTRAINT chk_fr_dates CHECK (end_date IS NULL OR end_date >= start_date);');
        DB::statement("ALTER TABLE fund_raisings ADD CONSTRAINT chk_fr_status CHECK (fund_raising_status IN ('draft','active','completed','cancelled'));");
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_raisings');
    }
};
