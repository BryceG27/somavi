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
            $table->text('airbnb_ical_url')->nullable()->after('airbnb_url');
            $table->text('booking_ical_url')->nullable()->after('booking_url');
            $table->text('vrbo_ical_url')->nullable()->after('vrbo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn([
                'airbnb_ical_url',
                'booking_ical_url',
                'vrbo_ical_url',
            ]);
        });
    }
};
