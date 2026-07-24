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
        Schema::table('locations', function (Blueprint $table) {
            $table->decimal('shipping_fee_near', 10, 2)->default(0)->after('type');
            $table->decimal('shipping_fee_far', 10, 2)->default(0)->after('shipping_fee_near');
            $table->decimal('min_order_near', 10, 2)->default(0)->after('shipping_fee_far');
            $table->decimal('min_order_far', 10, 2)->default(0)->after('min_order_near');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['shipping_fee_near', 'shipping_fee_far', 'min_order_near', 'min_order_far']);
        });
    }
};
