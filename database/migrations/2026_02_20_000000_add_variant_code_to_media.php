<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * variant_code groups all size variants (original, 1200x900, 400x300)
     * of a single image upload together. All rows from the same upload
     * share the same variant_code (a UUID).
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('variant_code', 36)->nullable()->after('gallery_id');
            $table->index('variant_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (Schema::hasColumn('media', 'variant_code')) {
                $table->dropIndex(['variant_code']);
                $table->dropColumn('variant_code');
            }
        });
    }
};
