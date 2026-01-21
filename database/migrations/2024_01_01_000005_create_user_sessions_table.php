<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('session_token', 500);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            
            // PERBAIKAN: Menambahkan nullable() untuk menghindari error Invalid Default Value
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_activity_at')->useCurrent(); // Default ke waktu sekarang
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_active']);
            $table->index('session_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};