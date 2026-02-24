<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'pending_webhook' to payment_transactions.status enum.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN status ENUM('initiated', 'pending', 'pending_webhook', 'success', 'failed', 'refunded') DEFAULT 'initiated' COMMENT 'Payment status'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally migrate existing pending_webhook back to pending before reverting enum
        DB::table('payment_transactions')->where('status', 'pending_webhook')->update(['status' => 'pending']);
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN status ENUM('initiated', 'pending', 'success', 'failed', 'refunded') DEFAULT 'initiated' COMMENT 'Payment status'");
    }
};
