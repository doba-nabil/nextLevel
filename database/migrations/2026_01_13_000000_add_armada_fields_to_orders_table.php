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
            $table->string('armada_id')->nullable()->after('payment_response');
            $table->string('armada_header')->nullable()->after('armada_id');
            $table->text('armada_link')->nullable()->after('armada_header');
            $table->text('armada_qr')->nullable()->after('armada_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['armada_id', 'armada_header', 'armada_link', 'armada_qr']);
        });
    }
};
