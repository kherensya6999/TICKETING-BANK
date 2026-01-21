<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_subcategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('subcategory_name', 100);
            $table->string('subcategory_code', 20);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('ticket_categories')->onDelete('cascade');
            $table->unique(['category_id', 'subcategory_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_subcategories');
    }
};
