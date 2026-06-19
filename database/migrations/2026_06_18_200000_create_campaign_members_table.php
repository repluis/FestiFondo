<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campaign_members')) {
            return;
        }

        Schema::create('campaign_members', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('campaign_oid');
            $table->bigInteger('member_oid');
            $table->date('enrolled_at');
            $table->bigInteger('created_by_oid')->nullable();
            $table->bigInteger('updated_by_oid')->nullable();
            $table->timestamps();

            $table->unique(['campaign_oid', 'member_oid']);
            $table->index('campaign_oid');
            $table->index('member_oid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_members');
    }
};
