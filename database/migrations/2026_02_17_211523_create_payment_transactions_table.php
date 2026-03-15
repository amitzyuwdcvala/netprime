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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('android_id')->index()->references('android_id')->on('users')->onDelete('cascade')->nullable();

            $table->uuid('plan_id')->index()->references('id')->on('subscription_plans')->onDelete('cascade')->nullable();
            $table->uuid('payment_gateway_id')->index()->references('id')->on('payment_gateways')->onDelete('restrict')->nullable();

            $table->string('transaction_id', 50)->unique()->comment('Our internal transaction ID');
            $table->string('gateway_order_id', 100)->nullable()->comment('Gateway order ID (Razorpay order_id, PhonePe merchantTransactionId, etc)');
            $table->string('gateway_payment_id', 100)->nullable()->comment('Gateway payment ID after successful payment');
            $table->string('gateway_signature', 255)->nullable()->comment('Payment signature for verification');

            $table->decimal('amount', 10, 2)->comment('Payment amount in INR');
            $table->string('currency', 3)->default('INR');

            $table->enum('status', ['initiated', 'pending', 'pending_webhook', 'success', 'failed', 'refunded'])->default('initiated')->comment('Payment status');

            $table->string('payment_method', 50)->nullable()->comment('UPI, card, netbanking, wallet');
            $table->string('card_last4', 4)->nullable()->comment('Last 4 digits of card');
            $table->string('card_network', 20)->nullable()->comment('Visa, Mastercard, etc');
            $table->string('upi_id', 100)->nullable()->comment('UPI ID if payment via UPI');

            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->string('error_source', 50)->nullable()->comment('Gateway, bank, user');

            $table->json('gateway_response')->nullable()->comment('Full gateway response for debugging');
            $table->json('metadata')->nullable()->comment('Additional data like IP, device info');

            $table->timestamp('paid_at')->nullable()->comment('When payment was successful');
            $table->timestamp('failed_at')->nullable()->comment('When payment failed');
            $table->timestamps();

            // Performance indexes
            $table->index('transaction_id', 'idx_payment_transactions_transaction_id');
            $table->index('gateway_order_id', 'idx_payment_transactions_gateway_order');
            $table->index('gateway_payment_id', 'idx_payment_transactions_gateway_payment');
            $table->index(['android_id', 'status'], 'idx_payment_transactions_android_status');
            $table->index(['payment_gateway_id', 'status'], 'idx_payment_transactions_gateway_status');
            $table->index(['status', 'created_at'], 'idx_payment_transactions_status_created');
            $table->index('created_at', 'idx_payment_transactions_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
