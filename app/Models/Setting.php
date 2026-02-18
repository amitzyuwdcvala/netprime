<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'key',
        'value',
        'enable_google_admob_id',
        'app_update_id',
        'enable_quereka_link',
        'enable_startapp_id',
        'enable_appnext_id',
        'enable_applovin_id',
        'enable_ad_colony_id',
        'enable_chartboost_id',
        'created_by',
    ];

    protected $casts = [
        'enable_google_admob_id' => 'integer',
        'app_update_id' => 'integer',
        'enable_quereka_link' => 'integer',
        'enable_startapp_id' => 'integer',
        'enable_appnext_id' => 'integer',
        'enable_applovin_id' => 'integer',
        'enable_ad_colony_id' => 'integer',
        'enable_chartboost_id' => 'integer',
        'created_by' => 'integer',
    ];
}
