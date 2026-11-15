<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('secret_santa_exclusions')) {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');

        Schema::create('secret_santa_exclusions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->default(DB::raw('gen_random_uuid()'))->unique();
            $table->boolean('status')->default(true);
            $table->bigInteger('campaign_oid');
            $table->bigInteger('giver_oid');     // a esta persona…
            $table->bigInteger('receiver_oid');  // …no le puede tocar esta
            $table->bigInteger('created_by_oid')->nullable();
            $table->timestamps();

            $table->unique(['campaign_oid', 'giver_oid', 'receiver_oid']);
            $table->index('campaign_oid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secret_santa_exclusions');
    }
};
