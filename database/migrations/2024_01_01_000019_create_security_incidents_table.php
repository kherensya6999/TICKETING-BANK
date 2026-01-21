<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->unique();
            $table->string('incident_number', 50)->unique();
            $table->enum('incident_classification', [
                'MALWARE', 'PHISHING', 'DDOS', 'DATA_BREACH', 'UNAUTHORIZED_ACCESS',
                'INSIDER_THREAT', 'PHYSICAL_SECURITY', 'SOCIAL_ENGINEERING', 'OTHER'
            ]);
            $table->enum('attack_vector', ['EMAIL', 'WEB', 'NETWORK', 'PHYSICAL', 'MOBILE', 'CLOUD', 'OTHER']);
            $table->enum('confidentiality_impact', ['NONE', 'LOW', 'MEDIUM', 'HIGH'])->default('NONE');
            $table->enum('integrity_impact', ['NONE', 'LOW', 'MEDIUM', 'HIGH'])->default('NONE');
            $table->enum('availability_impact', ['NONE', 'LOW', 'MEDIUM', 'HIGH'])->default('NONE');
            $table->enum('investigation_status', ['NOT_STARTED', 'IN_PROGRESS', 'UNDER_REVIEW', 'COMPLETED', 'CLOSED'])->default('NOT_STARTED');
            $table->timestamp('detected_at');
            $table->timestamp('contained_at')->nullable();
            $table->timestamp('eradicated_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->boolean('forensic_evidence_collected')->default(false);
            $table->string('evidence_storage_location')->nullable();
            $table->text('detection_method')->nullable();
            $table->json('affected_assets')->nullable();
            $table->text('root_cause_category')->nullable();
            $table->text('root_cause_description')->nullable();
            $table->text('immediate_actions_taken')->nullable();
            $table->text('remediation_actions')->nullable();
            $table->text('preventive_measures')->nullable();
            $table->boolean('requires_regulatory_reporting')->default(false);
            $table->json('regulatory_bodies_notified')->nullable();
            $table->timestamp('customers_notified_at')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->boolean('post_incident_review_completed')->default(false);
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->index(['incident_classification', 'investigation_status']);
            $table->index('detected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_incidents');
    }
};
