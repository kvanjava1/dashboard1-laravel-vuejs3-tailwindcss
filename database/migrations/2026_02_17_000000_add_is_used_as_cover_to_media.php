<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->boolean('is_used_as_cover')->default(false)->after('is_cover');
            $table->index('is_used_as_cover');
        });

        // Backfill: mark current 'is_cover' rows as 'is_used_as_cover' so behaviour is preserved
        DB::table('media')->where('is_cover', true)->update(['is_used_as_cover' => true]);

        // If using PostgreSQL, add a partial unique index to guarantee one selected cover per gallery
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX media_gallery_used_cover_unique ON media (gallery_id) WHERE is_used_as_cover');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS media_gallery_used_cover_unique');
        }

        Schema::table('media', function (Blueprint $table) {
            if (Schema::hasColumn('media', 'is_used_as_cover')) {
                $table->dropIndex(['is_used_as_cover']);
                $table->dropColumn('is_used_as_cover');
            }
        });
    }
};