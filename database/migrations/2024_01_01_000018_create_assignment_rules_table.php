<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name', 100);
            $table->enum('rule_type', ['AUTO_ASSIGN', 'ROUTE_TO_TEAM', 'SET_PRIORITY', 'SET_SLA'])->default('AUTO_ASSIGN');
            $table->json('conditions')->comment('JSON conditions: category_id, priority, keywords, etc');
            $table->unsignedBigInteger('assign_to_team_id')->nullable();
            $table->unsignedBigInteger('assign_to_user_id')->nullable();
            $table->integer('priority_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('assign_to_team_id')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('assign_to_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['is_active', 'priority_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_rules');
    }
};
