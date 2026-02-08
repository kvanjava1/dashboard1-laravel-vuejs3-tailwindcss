<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->unsignedBigInteger('cover_id')->nullable(); // Foreign key to media table
            $table->integer('item_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
