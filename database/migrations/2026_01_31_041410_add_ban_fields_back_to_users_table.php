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
        Schema::table('users', function (Blueprint $table) {

            $table->string('profile_image')->nullable()->after('email_verified_at');
            $table->boolean('is_banned')->default(false)->after('location');
            $table->boolean('is_active')->default(false)->after('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
