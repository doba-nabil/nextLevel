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
            $table->string('phone_code', 10)->nullable()->after('type')->comment('Phone country code like +966, +1');
            $table->string('code', 2)->nullable()->after('phone_code')->comment('ISO 3166-1 alpha-2 country code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['phone_code', 'code']);
        });
    }
};

