<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name', 100);
            $table->string('category_code', 20)->unique();
            $table->enum('default_priority', ['LOW', 'MEDIUM', 'HIGH', 'URGENT', 'CRITICAL'])->default('MEDIUM');
            $table->unsignedBigInteger('default_sla_id')->nullable();
            $table->unsignedBigInteger('default_team_id')->nullable();
            $table->boolean('is_security_related')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('default_sla_id')->references('id')->on('sla_policies')->onDelete('set null');
            $table->foreign('default_team_id')->references('id')->on('teams')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
    }
};
