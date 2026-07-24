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
        Schema::table('addon_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('addon_groups', 'active')) {
                $table->boolean('active')->default(1)->after('max_items');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addon_groups', function (Blueprint $table) {
            if (Schema::hasColumn('addon_groups', 'active')) {
                $table->dropColumn('active');
            }
        });
    }
};
