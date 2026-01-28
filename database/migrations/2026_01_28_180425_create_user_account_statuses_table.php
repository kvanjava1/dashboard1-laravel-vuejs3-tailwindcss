<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_account_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'not_activated', 'active', 'banned'
            $table->string('display_name'); // e.g., 'Not Activated', 'Active', 'Banned'
            $table->string('color')->nullable(); // Optional color for UI
            $table->boolean('is_active')->default(true); // Whether this status is available for new assignments
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_account_statuses');
    }
};
