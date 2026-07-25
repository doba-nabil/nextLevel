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
        Schema::table('product_branches', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('status');
            $table->integer('max_order_quantity')->default(0)->after('stock');
            $table->integer('low_stock_threshold')->default(5)->after('max_order_quantity');
            $table->boolean('low_stock_notified')->default(false)->after('low_stock_threshold');
            $table->boolean('track_stock')->default(false);
        });

        // Copy existing stock and max_order_quantity from products table to product_branches
        $products = \DB::table('products')->select('id', 'stock', 'max_order_quantity')->get();
        foreach ($products as $product) {
            \DB::table('product_branches')
                ->where('product_id', $product->id)
                ->update([
                    'stock' => $product->stock ?? 0,
                    'max_order_quantity' => $product->max_order_quantity ?? 0,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_branches', function (Blueprint $table) {
            $table->dropColumn(['stock', 'max_order_quantity', 'low_stock_threshold', 'low_stock_notified', 'track_stock']);
        });
    }
};
