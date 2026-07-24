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
        Schema::table('box_products', function (Blueprint $table) {
            if (Schema::hasColumn('box_products', 'required_count')) {
                $table->dropColumn('required_count');
            }
            $table->json('title')->nullable()->after('box_id');
            $table->boolean('is_required')->default(false)->after('title');
            $table->integer('max_count')->default(1)->after('is_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('box_products', function (Blueprint $table) {
            $table->dropColumn(['title', 'is_required', 'max_count']);
            $table->tinyInteger('required_count')->default(0)->after('product_id');
        });
    }
};

