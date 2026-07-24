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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_pickup')->default(false)->after('active');
            $table->boolean('is_trending')->default(false)->after('is_pickup');
            $table->boolean('is_new_plates')->default(false)->after('is_trending');
            $table->boolean('is_home')->default(false)->after('is_new_plates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_pickup', 'is_trending', 'is_new_plates', 'is_home']);
        });
    }
};

