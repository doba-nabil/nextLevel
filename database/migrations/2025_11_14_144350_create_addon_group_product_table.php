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
        Schema::create('addon_group_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_group_id')->constrained('addon_groups')->cascadeOnDelete();
            $table->integer('max_quantity')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate relationships
            $table->unique(['product_id', 'addon_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_group_product');
    }
};
