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
        Schema::table('currencies', function (Blueprint $table) {
            $table->decimal('rate_per_point', 18, 2)->default(0);
            $table->decimal('points_per_currency', 18, 2)->default(0);
            $table->unsignedInteger('minimum_usable_points')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn(['rate_per_point', 'points_per_currency', 'minimum_usable_points']);
        });
    }
};
