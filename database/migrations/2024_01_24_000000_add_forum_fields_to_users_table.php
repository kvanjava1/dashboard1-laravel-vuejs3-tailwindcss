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
            // Profile image
            $table->string('profile_image')->nullable()->after('email_verified_at');

            // Profile fields
            $table->string('username')->unique()->nullable()->after('profile_image');
            $table->text('bio')->nullable()->after('username');
            $table->date('date_of_birth')->nullable()->after('bio');
            $table->string('location')->nullable()->after('date_of_birth');

            // Moderation fields
            $table->boolean('is_banned')->default(false)->after('location');
            $table->text('ban_reason')->nullable()->after('is_banned');
            $table->timestamp('banned_until')->nullable()->after('ban_reason');

            // Preferences
            $table->string('timezone')->default('UTC')->after('ban_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_image',
                'username',
                'bio',
                'date_of_birth',
                'location',
                'is_banned',
                'ban_reason',
                'banned_until',
                'forum_preferences',
                'timezone'
            ]);
        });
    }
};