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
        Schema::table('users', function (Blueprint $table) {
            // Track which admin manually created the user (nullable for app-registered users)
            $table->unsignedBigInteger('added_by')->nullable()->after('video_click_count');
            $table->index('added_by', 'idx_users_added_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_added_by');
            $table->dropColumn('added_by');
        });
    }
};

