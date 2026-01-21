<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_threat_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incident_id');
            $table->unsignedBigInteger('threat_id');
            $table->decimal('confidence_score', 3, 2)->default(0.00)->comment('0.00-1.00');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('incident_id')->references('id')->on('security_incidents')->onDelete('cascade');
            $table->foreign('threat_id')->references('id')->on('threat_intelligence')->onDelete('restrict');
            $table->unique(['incident_id', 'threat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_threat_mapping');
    }
};
