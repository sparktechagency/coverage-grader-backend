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
        Schema::table('insurance_providers', function (Blueprint $table) {
            $table->unsignedInteger('review_count')->default(0)->after('status');
            $table->decimal('avg_overall_rating', 2, 1)->default(0.0)->after('review_count');
            $table->json('avg_scores')->nullable()->after('avg_overall_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurance_providers', function (Blueprint $table) {
            $table->dropColumn(['review_count', 'avg_overall_rating', 'avg_scores']);
        });
    }
};
