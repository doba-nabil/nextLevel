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
            if (!Schema::hasColumn('addon_groups', 'is_selection_mandatory')) {
                $table->boolean('is_selection_mandatory')->default(0)->after('active');
            }
            if (!Schema::hasColumn('addon_groups', 'max_selections')) {
                $table->integer('max_selections')->nullable()->after('is_selection_mandatory');
            }
            if (!Schema::hasColumn('addon_groups', 'min_selections')) {
                $table->integer('min_selections')->nullable()->after('max_selections');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addon_groups', function (Blueprint $table) {
            if (Schema::hasColumn('addon_groups', 'min_selections')) {
                $table->dropColumn('min_selections');
            }
            if (Schema::hasColumn('addon_groups', 'max_selections')) {
                $table->dropColumn('max_selections');
            }
            if (Schema::hasColumn('addon_groups', 'is_selection_mandatory')) {
                $table->dropColumn('is_selection_mandatory');
            }
        });
    }
};


