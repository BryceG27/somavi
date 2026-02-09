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
            $table->dropColumn([
                'payment_provider',
                'payment_plan',
                'payment_status',
                'deposit_percent',
                'deposit_amount',
                'balance_amount',
                'balance_due_at',
                'payment_locale',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'refund_amount',
                'refunded_at',
                'stripe_refund_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('payment_provider')->default('stripe')->after('is_paid');
            $table->string('payment_plan')->default('full')->after('payment_provider');
            $table->string('payment_status')->default('pending')->after('payment_plan');
            $table->decimal('deposit_percent', 5, 2)->default(0)->after('payment_status');
            $table->decimal('deposit_amount', 10, 2)->default(0)->after('deposit_percent');
            $table->decimal('balance_amount', 10, 2)->default(0)->after('deposit_amount');
            $table->date('balance_due_at')->nullable()->after('balance_amount');
            $table->string('payment_locale', 5)->nullable()->after('balance_due_at');
            $table->string('stripe_checkout_session_id')->nullable()->after('payment_locale');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('total_paid');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->string('stripe_refund_id')->nullable()->after('stripe_payment_intent_id');
        });
    }
};
