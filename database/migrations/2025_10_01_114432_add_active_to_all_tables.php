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
        foreach ($this->getTables() as $table) {
            if (!Schema::hasColumn($table, 'active')) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->boolean('active')->default(true);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->getTables() as $table) {
            if (Schema::hasColumn($table, 'active')) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->dropColumn('active');
                });
            }
        }
    }

    private function getTables(): array
    {
        return [
            'addons',
            'addon_groups',
            'branches',
            'categories',
            'coupons',
            'locations',
            'pages',
            'products',
            'product_definitions'
        ];
    }
};
