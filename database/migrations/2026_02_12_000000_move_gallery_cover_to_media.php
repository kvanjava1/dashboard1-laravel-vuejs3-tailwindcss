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
        // Add is_cover to media
        Schema::table('media', function (Blueprint $table) {
            $table->boolean('is_cover')->default(false)->after('gallery_id');
            $table->index('is_cover');
        });

        // Backfill is_cover using existing galleries.cover_id (if present) and common filename patterns,
        // then remove cover_id from galleries.
        if (Schema::hasTable('galleries') && Schema::hasTable('media')) {
            // If galleries.cover_id exists, mark referenced media as cover
            try {
                $rows = \Illuminate\Support\Facades\DB::table('galleries')
                    ->select('id', 'cover_id')
                    ->whereNotNull('cover_id')
                    ->get();

                foreach ($rows as $r) {
                    if ($r->cover_id) {
                        \Illuminate\Support\Facades\DB::table('media')->where('id', $r->cover_id)->update(['is_cover' => true]);
                    }
                }
            } catch (\Exception $e) {
                // ignore backfill errors
            }

            // Also mark typical 1200x900 variant files as cover where applicable
            try {
                \Illuminate\Support\Facades\DB::table('media')
                    ->where('filename', 'like', '%/1200x900/%')
                    ->update(['is_cover' => true]);
            } catch (\Exception $e) {
                // ignore
            }
        }

        Schema::table('galleries', function (Blueprint $table) {
            if (Schema::hasColumn('galleries', 'cover_id')) {
                // Drop foreign key if it exists (silently ignore if not)
                try {
                    $table->dropForeign(['cover_id']);
                } catch (\Exception $e) {
                    // ignore
                }

                $table->dropColumn('cover_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add cover_id to galleries
        Schema::table('galleries', function (Blueprint $table) {
            $table->unsignedBigInteger('cover_id')->nullable()->after('is_public');
        });

        // Remove is_cover from media
        Schema::table('media', function (Blueprint $table) {
            if (Schema::hasColumn('media', 'is_cover')) {
                $table->dropIndex(['is_cover']);
                $table->dropColumn('is_cover');
            }
        });
    }
};