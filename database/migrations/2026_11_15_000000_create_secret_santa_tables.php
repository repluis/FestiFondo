<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');

        if (!Schema::hasTable('secret_santa_draws')) {
            Schema::create('secret_santa_draws', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->default(DB::raw('gen_random_uuid()'))->unique();
                $table->boolean('status')->default(true);
                $table->bigInteger('campaign_oid');
                $table->bigInteger('created_by_oid')->nullable();
                $table->timestamps();

                $table->index('campaign_oid');
            });
        }

        if (!Schema::hasTable('secret_santa_assignments')) {
            Schema::create('secret_santa_assignments', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->default(DB::raw('gen_random_uuid()'))->unique();
                $table->boolean('status')->default(true);
                $table->foreignId('draw_id')->constrained('secret_santa_draws')->cascadeOnDelete();
                $table->bigInteger('giver_oid');
                $table->bigInteger('receiver_oid');
                $table->string('token', 64)->unique();
                $table->timestamp('viewed_at')->nullable();
                $table->timestamps();

                $table->unique(['draw_id', 'giver_oid']);
                $table->unique(['draw_id', 'receiver_oid']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('secret_santa_assignments');
        Schema::dropIfExists('secret_santa_draws');
    }
};
