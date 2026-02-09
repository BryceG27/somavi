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
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('cancelled_by')->nullable()->after('status');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('total_paid');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->string('stripe_refund_id')->nullable()->after('stripe_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'cancelled_by',
                'refund_amount',
                'refunded_at',
                'stripe_refund_id',
            ]);
        });
    }
};
