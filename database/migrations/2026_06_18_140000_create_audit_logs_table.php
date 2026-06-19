<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type', 100); // table: members, system_configurations, etc.
            $table->unsignedBigInteger('auditable_oid');
            $table->uuid('auditable_uuid')->nullable();          // public ID snapshot
            $table->string('event', 30);                         // created|updated|deleted|restored|custom
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('user_oid')->nullable();  // null = system action
            $table->string('user_email', 150)->nullable();       // snapshot — survives user deletion
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_oid']);
            $table->index('auditable_uuid');
            $table->index('event');
            $table->index('user_oid');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
