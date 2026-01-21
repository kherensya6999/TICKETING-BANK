<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_sla_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->unique();
            $table->unsignedBigInteger('sla_policy_id');
            $table->timestamp('first_response_target_at')->nullable();
            $table->timestamp('first_response_actual_at')->nullable();
            $table->boolean('first_response_breached')->default(false);
            $table->timestamp('resolution_target_at')->nullable();
            $table->timestamp('resolution_actual_at')->nullable();
            $table->boolean('resolution_breached')->default(false);
            $table->enum('overall_sla_status', ['ON_TIME', 'AT_RISK', 'BREACHED'])->default('ON_TIME');
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('sla_policy_id')->references('id')->on('sla_policies')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_sla_tracking');
    }
};
