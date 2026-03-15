<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ConfigController extends Controller
{
    private const CACHE_KEY = 'app_config_admin';
    private const DEFAULT_CONFIG = <<<'JSON'
{
  "app": {
    "maintenance": false,
    "update": {
      "showDialog": true,
      "versionCode": 9,
      "updateLink": "https://netprime.store"
    },
    "package": {
      "name": "com.king.moja",
      "version": 305,
      "checkHash": "92941177CB0FFA4CBCAC7AE20E300C9B530EA46572D97F1B41D7BD8C9F0D6EAA"
    }
  },
  "api": {
    "main": "https://panel.watchkaroabhi.com/api/3/",
    "adult": "https://movies07prime.com/",
    "apiKey": "qNhKLJiZVyoKdi9NCQGz8CIGrpUijujE",
    "timeApi": "https://netprime.store/time.php",
    "tmdb": {
      "key": "0ed481381ba182ca8b83749968dd418f",
      "url": "https://api.themoviedb.org/3/"
    }
  },
  "security": {
    "dnsProtection": false,
    "dnsServers": [
      "x-oisd.freedns.controld.com",
      "dns-family.adguard.com",
      "dns.adguard.com",
      "security-filter-dns.cleanbrowsing.org"
    ],
    "serverUserAgent": "dooflix"
  },
  "features": {
    "movies": true,
    "adultVideos": true,
    "freeVideos": {
      "enabled": true,
      "limit": 5
    },
    "premium": {
      "enabled": true,
      "timerEnabled": true
    },
    "firebaseLinks": false,
    "showPanelContent": true
  },
  "ads": {
    "interstitialEnabled": false,
    "showDialogBeforeAd": false,
    "forwardClickAd": false,
    "firstTimer": 120000,
    "secondTimer": 300000,
    "randomTimer": 3000
  },
  "payments": {
    "enabled": false,
    "razorpayKey": "rzp_live_85bPXDNYXPmDwK",
    "plans": [
      { "id": 1, "name": "Monthly",   "price": 99,  "days": 30  },
      { "id": 2, "name": "3 Months",  "price": 249, "days": 90  },
      { "id": 3, "name": "6 Months",  "price": 399, "days": 180 },
      { "id": 4, "name": "Yearly",    "price": 599, "days": 365 }
    ]
  },
  "engagement": {
    "oneSignalId": "3c821ddb-646f-42c9-8aa1-a7e761c31441",
    "joinUs": "https://t.me/NetPrime_Official_App",
    "contactEmail": "zoominfotechh@gmail.com"
  },
  "external": {
    "quiz": {
      "enabled": false,
      "link": "https://437.mark.qureka.com/intro/question"
    },
    "custom": {
      "enabled": false,
      "link": "https://437.mark.qureka.com/intro/question",
      "nativeBanner": [
        "https://raw.githubusercontent.com/123pratik456/publicApi/refs/heads/main/Atmegame%20GIF%20(1).gif"
      ]
    }
  }
}
JSON;

    public function index()
    {
        $record = AppConfig::getMain();
        $raw    = $record ? $record->config : self::DEFAULT_CONFIG;

        $decoded = json_decode($raw, true);
        $json = $decoded
            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : $raw;

        return view('admin.config.index', [
            'json'      => $json,
            'viewData'  => ['title' => 'App Config'],
        ]);
    }

    public function save(Request $request)
    {
        $raw = trim($request->input('config', ''));

        if (empty($raw)) {
            return back()->withErrors(['config' => 'Config cannot be empty.'])->withInput();
        }

        json_decode($raw);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()
                ->withErrors(['config' => 'Invalid JSON: ' . json_last_error_msg()])
                ->withInput();
        }

        AppConfig::updateOrCreate(['name' => 'main'], ['config' => $raw]);
        Cache::forget('app_config_main');
        Cache::forget(AppConfig::CACHE_KEY_DECODED);

        return back()->with('success', 'Config saved successfully.');
    }

    public static function clearCache(): void
    {
        Cache::forget('app_config_main');
        Cache::forget(\App\Models\AppConfig::CACHE_KEY_DECODED);
    }
}
