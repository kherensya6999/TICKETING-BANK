<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('created_by')->nullable(); // Super Admin ID
            $table->timestamp('expires_at');
            $table->timestamp('registered_at')->nullable(); // Kapan token dipakai
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_invitations');
    }
};