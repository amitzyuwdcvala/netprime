<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Make payment_gateway_id nullable so we can store manual / free
     * subscriptions that are not tied to any real gateway.
     */
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            // Drop the existing foreign key first so we can alter the column
            $table->dropForeign(['payment_gateway_id']);
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->uuid('payment_gateway_id')->nullable()->change();
            $table->foreign('payment_gateway_id')
                ->references('id')
                ->on('payment_gateways')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['payment_gateway_id']);
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->uuid('payment_gateway_id')->nullable(false)->change();
            $table->foreign('payment_gateway_id')
                ->references('id')
                ->on('payment_gateways')
                ->restrictOnDelete();
        });
    }
};

