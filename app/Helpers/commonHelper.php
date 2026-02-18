<?php

if (!function_exists('get_setting')) {
    /**
     * Get a setting value by key.
     */
    function get_setting(string $key, $default = null)
    {
        $setting = \App\Models\Setting::first();
        if ($setting && isset($setting->$key)) {
            return $setting->$key;
        }
        return $default;
    }
}

if (!function_exists('is_active_route')) {
    /**
     * Check if a route segment is active.
     */
    function is_active_route(?string $segment, int $position = 2): string
    {
        return request()->segment($position) === $segment ? 'active' : '';
    }
}

if (!function_exists('format_date')) {
    /**
     * Format a date string.
     */
    function format_date($date, string $format = 'd M Y'): string
    {
        if (empty($date)) return '';
        return \Carbon\Carbon::parse($date)->format($format);
    }
}
