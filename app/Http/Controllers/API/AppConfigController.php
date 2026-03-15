<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Support\Facades\Cache;

class AppConfigController extends Controller
{
    private const CACHE_KEY = 'app_config_main';
    private const CACHE_TTL = 300;

    public function show()
    {
        $json = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return AppConfig::getRawJson();
        });

        if ($json === null) {
            return response()->json(['error' => 'Config not set'], 404);
        }

        $decoded = json_decode($json, true);
        if ($decoded === null) {
            return response()->json(['error' => 'Config is invalid JSON'], 500);
        }

        return response()->json($decoded);
    }
}
