<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('config_key', 100)->unique();
            $table->string('config_value', 500);
            $table->string('data_type', 20); // decimal, integer, boolean, string
            $table->string('description', 300)->nullable();
            $table->boolean('is_editable')->default(true);
            $table->unsignedBigInteger('created_by_oid')->nullable();
            $table->unsignedBigInteger('updated_by_oid')->nullable();
            $table->timestamps();

            $table->index('config_key');
            $table->index('is_editable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_configurations');
    }
};
