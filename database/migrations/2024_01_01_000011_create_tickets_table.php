<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 50)->unique();
            $table->enum('ticket_type', ['INCIDENT', 'REQUEST', 'PROBLEM', 'CHANGE'])->default('INCIDENT');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->enum('status', ['NEW', 'ASSIGNED', 'IN_PROGRESS', 'PENDING', 'RESOLVED', 'CLOSED', 'CANCELLED'])->default('NEW');
            $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH', 'URGENT', 'CRITICAL'])->default('MEDIUM');
            $table->unsignedBigInteger('sla_id')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->string('subject', 255);
            $table->text('description');
            $table->boolean('is_security_incident')->default(false);
            $table->boolean('is_sla_breached')->default(false);
            $table->boolean('is_escalated')->default(false);
            $table->integer('escalation_level')->default(0);
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('resolution_duration')->nullable()->comment('Minutes');
            $table->enum('resolution_status', ['RESOLVED', 'WORKAROUND', 'CANNOT_REPRODUCE', 'DUPLICATE'])->nullable();
            $table->text('resolution_summary')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('actions_taken')->nullable();
            $table->text('preventive_measures')->nullable();
            $table->integer('satisfaction_rating')->nullable()->comment('1-5');
            $table->text('satisfaction_feedback')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('ticket_categories')->onDelete('restrict');
            $table->foreign('subcategory_id')->references('id')->on('ticket_subcategories')->onDelete('set null');
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('sla_id')->references('id')->on('sla_policies')->onDelete('set null');

            $table->index(['status', 'priority']);
            $table->index(['requester_id', 'status']);
            $table->index(['assigned_to_id', 'status']);
            $table->index(['is_security_incident', 'status']);
            $table->index('due_date');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
