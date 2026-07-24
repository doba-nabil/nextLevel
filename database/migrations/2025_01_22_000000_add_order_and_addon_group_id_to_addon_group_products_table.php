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
        Schema::table('addon_group_products', function (Blueprint $table) {
            $table->unsignedInteger('order')->default(0)->after('type');
            $table->foreignId('addon_group_id')->nullable()->after('addon_id')->constrained('addon_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addon_group_products', function (Blueprint $table) {
            $table->dropForeign(['addon_group_id']);
            $table->dropColumn(['order', 'addon_group_id']);
        });
    }
};

