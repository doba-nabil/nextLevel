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
            $table->integer('stock')->default(0)->after('is_active');
            $table->integer('max_order_quantity')->default(0)->after('stock'); // 0 means no limit
            $table->integer('low_stock_threshold')->default(5)->after('max_order_quantity');
            $table->boolean('low_stock_notified')->default(false)->after('low_stock_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock', 'max_order_quantity']);
        });
    }
};
