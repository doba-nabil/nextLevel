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
        Schema::create('branch_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->enum('from_day', [
                'sunday','monday','tuesday','wednesday','thursday','friday','saturday'
            ]);
            $table->enum('to_day', [
                'sunday','monday','tuesday','wednesday','thursday','friday','saturday'
            ]);

            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_working_hours');
    }
};
