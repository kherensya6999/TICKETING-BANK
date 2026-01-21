<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_escalations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->integer('escalation_level')->default(1);
            $table->unsignedBigInteger('escalated_from_user_id')->nullable();
            $table->unsignedBigInteger('escalated_to_user_id')->nullable();
            $table->unsignedBigInteger('escalated_to_team_id')->nullable();
            $table->text('escalation_reason');
            $table->enum('escalation_type', ['SLA_BREACH', 'MANUAL', 'PRIORITY_UPGRADE', 'CUSTOMER_REQUEST'])->default('SLA_BREACH');
            $table->timestamp('escalated_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('escalated_from_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('escalated_to_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('escalated_to_team_id')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('acknowledged_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['ticket_id', 'escalation_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_escalations');
    }
};
