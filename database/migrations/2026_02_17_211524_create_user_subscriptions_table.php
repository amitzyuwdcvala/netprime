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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('android_id')->index();
            $table->foreign('android_id')->references('android_id')->on('users')->onDelete('cascade');
            $table->foreignUuid('plan_id')->constrained('subscription_plans');
            $table->foreignUuid('payment_gateway_id')->constrained('payment_gateways');

            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_payment_id')->nullable();

            $table->decimal('paid_amount', 10, 2);
            $table->integer('days');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired'])->default('active');

            $table->timestamps();

            // Performance indexes
            $table->index(['android_id', 'status'], 'idx_user_subscriptions_android_status');
            $table->index(['status', 'end_date'], 'idx_user_subscriptions_status_end_date');
            $table->index('end_date', 'idx_user_subscriptions_end_date');
            $table->index('created_at', 'idx_user_subscriptions_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
