<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threat_intelligence', function (Blueprint $table) {
            $table->id();
            $table->string('threat_name', 255);
            $table->enum('threat_type', ['MALWARE', 'PHISHING', 'EXPLOIT', 'VULNERABILITY', 'IOC', 'OTHER']);
            $table->text('description')->nullable();
            $table->json('indicators_of_compromise')->nullable()->comment('IOCs: IPs, domains, hashes, etc');
            $table->json('mitre_attack_techniques')->nullable();
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('MEDIUM');
            $table->string('source', 100)->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['threat_type', 'severity', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('threat_intelligence');
    }
};
