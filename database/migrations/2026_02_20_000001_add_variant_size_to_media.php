<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * variant_size stores the size label for each media variant row.
     * Possible values: 'original', '1200x900', '400x300'.
     * This replaces fragile filename-based pattern matching
     * (e.g. str_contains(filename, '/1200x900/')).
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('variant_size', 20)->nullable()->after('variant_code');
            $table->index('variant_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (Schema::hasColumn('media', 'variant_size')) {
                $table->dropIndex(['variant_size']);
                $table->dropColumn('variant_size');
            }
        });
    }
};
