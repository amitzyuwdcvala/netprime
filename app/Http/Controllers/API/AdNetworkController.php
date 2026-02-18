<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Services\Admin\SettingServices;
use Illuminate\Http\Request;

class AdNetworkController extends Controller
{
    use ApiResponses;

    public $settingServices;

    public function __construct(SettingServices $settingServices)
    {
        $this->settingServices = $settingServices;
    }

    /**
     * Get Ad Settings for Mobile App.
     */
    public function get_ads_settings()
    {
        try {
            $settings = \App\Models\Setting::all();

            return $this->okResponse([
                'message' => 'Ad settings fetched successfully',
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('get_ads_settings_error', [
                'message' => $e->getMessage(),
            ]);
            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }
}
