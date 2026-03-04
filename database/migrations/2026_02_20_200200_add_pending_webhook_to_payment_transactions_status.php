<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'pending_webhook' to payment_transactions.status enum (MySQL only).
     * SQLite does not support MODIFY COLUMN / ENUM; Laravel stores enum as string so no change needed.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN status ENUM('initiated', 'pending', 'pending_webhook', 'success', 'failed', 'refunded') DEFAULT 'initiated' COMMENT 'Payment status'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::table('payment_transactions')->where('status', 'pending_webhook')->update(['status' => 'pending']);
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN status ENUM('initiated', 'pending', 'success', 'failed', 'refunded') DEFAULT 'initiated' COMMENT 'Payment status'");
    }
};
