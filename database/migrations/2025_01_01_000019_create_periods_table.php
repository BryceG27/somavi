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
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('base_price', 8, 2)->default(0);
            $table->decimal('extra_guest_price_2', 8, 2)->default(0);
            $table->decimal('extra_guest_price_3', 8, 2)->default(0);
            $table->decimal('extra_guest_price_4', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['apartment_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
