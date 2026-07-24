<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_item_id')->nullable()->after('order_id');
            $table->json('meta')->nullable()->after('total');
            $table->foreign('parent_item_id')->references('id')->on('order_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['parent_item_id']);
            $table->dropColumn(['parent_item_id', 'meta']);
        });
    }
};


