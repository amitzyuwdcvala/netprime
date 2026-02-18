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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->nullable();

            $table->integer('enable_google_admob_id')->default(0);
            $table->string('google_admob_app_open_id')->nullable();
            $table->string('google_admob_banner_id')->nullable();
            $table->string('google_admob_interstitial_id')->nullable();
            $table->string('google_admob_native_id')->nullable();
            $table->string('google_admob_rewarded_video_id')->nullable();

            $table->integer('app_update_id')->default(0);
            $table->string('google_admob_click_count_id')->nullable();
            $table->string('app_under_maintenance_id')->nullable();
            $table->string('google_back_adon_off_id')->nullable();
            $table->string('app_exit_screen_onoff_id')->nullable();
            
            $table->integer('enable_quereka_link')->default(0);
            $table->string('quereka_link')->nullable();
            
            $table->integer('enable_startapp_id')->default(0);
            $table->string('startapp_id')->nullable();
            
            $table->integer('enable_appnext_id')->default(0);
            $table->string('appnext_id_1')->nullable();
            $table->string('appnext_id_2')->nullable();
            $table->string('appnext_id_3')->nullable();
            
            $table->integer('enable_applovin_id')->default(0);
            $table->string('applovin_id_1')->nullable();
            $table->string('applovin_id_2')->nullable();
            $table->string('applovin_id_3')->nullable();
            
            $table->integer('enable_ad_colony_id')->default(0);
            $table->string('ad_colony_id_1')->nullable();
            $table->string('ad_colony_id_2')->nullable();
            $table->string('ad_colony_id_3')->nullable();
            
            $table->integer('enable_chartboost_id')->default(0);
            $table->string('chartboost_id_1')->nullable();
            $table->string('chartboost_id_2')->nullable();
            $table->string('chartboost_id_3')->nullable();
            
            $table->integer('created_by')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
