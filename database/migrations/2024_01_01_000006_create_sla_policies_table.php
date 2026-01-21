<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name', 100);
            $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH', 'URGENT', 'CRITICAL']);
            $table->integer('first_response_target')->comment('Minutes');
            $table->integer('resolution_target')->comment('Minutes');
            $table->boolean('business_hours_only')->default(false);
            $table->time('business_hours_start')->nullable();
            $table->time('business_hours_end')->nullable();
            $table->json('business_days')->nullable()->comment('Array of day numbers: 1=Monday, 7=Sunday');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['priority', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
