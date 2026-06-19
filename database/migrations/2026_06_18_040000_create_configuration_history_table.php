<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuration_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('configuration_oid');
            $table->string('config_key', 100);       // snapshot
            $table->string('old_value', 500)->nullable();
            $table->string('new_value', 500);
            $table->string('data_type', 20)->nullable(); // snapshot
            $table->text('change_reason')->nullable();
            $table->unsignedBigInteger('changed_by_oid');
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index('configuration_oid');
            $table->index('changed_by_oid');
            $table->index('changed_at');
            $table->index('config_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration_history');
    }
};
