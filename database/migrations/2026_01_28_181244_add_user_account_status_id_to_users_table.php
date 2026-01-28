<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_account_status_id')->nullable()->after('status');
            $table->foreign('user_account_status_id')->references('id')->on('user_account_statuses');
        });

        // Populate the new column based on existing status values
        DB::statement("
            UPDATE users 
            SET user_account_status_id = (
                SELECT id FROM user_account_statuses 
                WHERE name = users.status 
                LIMIT 1
            )
            WHERE status IS NOT NULL
        ");

        // Make the column not nullable after populating
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_account_status_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['user_account_status_id']);
            $table->dropColumn('user_account_status_id');
        });
    }
};
