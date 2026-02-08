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
        Schema::table('apartments', function (Blueprint $table) {
            $table->decimal('base_price', 8, 2)->default(0);
            $table->decimal('extra_guest_price_2', 8, 2)->default(0);
            $table->decimal('extra_guest_price_3', 8, 2)->default(0);
            $table->decimal('extra_guest_price_4', 8, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn([
                'base_price',
                'extra_guest_price_2',
                'extra_guest_price_3',
                'extra_guest_price_4',
            ]);
        });
    }
};
