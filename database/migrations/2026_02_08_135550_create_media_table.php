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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->nullable()->constrained('galleries')->onDelete('cascade');
            $table->string('filename');
            $table->string('extension')->default('webp');
            $table->string('mime_type')->default('image/webp');
            $table->bigInteger('size');
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->index('gallery_id');
            $table->index('uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
