<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppConfig extends Model
{
    protected $table = 'app_configs';

    protected $fillable = ['name', 'config'];

    public const CACHE_KEY_DECODED = 'app_config_decoded';
    public const CACHE_TTL = 300;

    public static function getMain(): ?self
    {
        return self::where('name', 'main')->first();
    }

    public static function getRawJson(): ?string
    {
        return self::getMain()?->config;
    }

    public static function getDecoded(): ?array
    {
        $raw = self::getRawJson();
        if ($raw === null) {
            return null;
        }
        return json_decode($raw, true) ?? null;
    }

    public static function getDecodedCached(): array
    {
        return Cache::remember(self::CACHE_KEY_DECODED, self::CACHE_TTL, function () {
            return self::getDecoded() ?? [];
        });
    }
}
