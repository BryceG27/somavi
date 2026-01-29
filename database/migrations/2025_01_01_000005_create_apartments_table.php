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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('name_it');
            $table->string('name_en')->nullable();
            $table->string('address_it');
            $table->string('address_en')->nullable();
            $table->string('hero_kicker_it')->nullable();
            $table->string('hero_kicker_en')->nullable();
            $table->string('hero_headline_it')->nullable();
            $table->string('hero_headline_en')->nullable();
            $table->text('hero_body_it')->nullable();
            $table->text('hero_body_en')->nullable();
            $table->string('hero_primary_cta_it')->nullable();
            $table->string('hero_primary_cta_en')->nullable();
            $table->string('hero_secondary_cta_it')->nullable();
            $table->string('hero_secondary_cta_en')->nullable();
            $table->unsignedInteger('rooms_count');
            $table->unsignedInteger('beds_count');
            $table->unsignedInteger('bathrooms_count')->nullable();
            $table->unsignedInteger('max_guests');
            $table->string('check_in_text')->nullable();
            $table->string('check_out_text')->nullable();
            $table->text('description_it')->nullable();
            $table->text('description_en')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('whatsapp_url')->nullable();
            $table->string('airbnb_url')->nullable();
            $table->string('booking_url')->nullable();
            $table->string('airbnb_api_key')->nullable();
            $table->string('booking_api_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
