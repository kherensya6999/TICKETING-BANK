<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('skill_level')->default(1)->comment('1-5');
            $table->integer('current_ticket_count')->default(0);
            $table->integer('max_concurrent_tickets')->default(5);
            $table->boolean('is_available')->default(true);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['team_id', 'user_id']);
            $table->index(['team_id', 'is_available', 'current_ticket_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
