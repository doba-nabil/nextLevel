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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('discount_amount'); // 'wallet', 'myfatoorah', 'mixed'
            $table->decimal('wallet_amount', 10, 2)->default(0)->after('payment_method');
            $table->decimal('gateway_amount', 10, 2)->default(0)->after('wallet_amount');
            $table->string('payment_status')->default('pending')->after('gateway_amount'); // 'pending', 'paid', 'failed'
            $table->string('payment_id')->nullable()->after('payment_status'); // MyFatoorah payment ID
            $table->text('payment_response')->nullable()->after('payment_id'); // Store payment gateway response
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'wallet_amount', 'gateway_amount', 'payment_status', 'payment_id', 'payment_response']);
        });
    }
};
