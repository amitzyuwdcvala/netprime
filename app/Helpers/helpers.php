<?php

use Illuminate\Support\Str;

if (!function_exists('generateUniqueSlug')) {
    /**
     * Generate a unique slug for a given model.
     */
    function generateUniqueSlug($name, $model, $column = 'slug', $separator = '-', $increment = 0)
    {
        $slug = Str::of($name)->slug($separator);

        if ($increment > 0) {
            $slug .= $separator . $increment;
        }

        $existingSlugs = $model::where($column, 'like', $slug . '%')->pluck($column)->toArray();

        if (!in_array($slug, $existingSlugs)) {
            return $slug;
        }

        return generateUniqueSlug($name, $model, $column, $separator, ++$increment);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format amount as Indian Rupee.
     */
    function format_currency(float $amount): string
    {
        return '₹' . number_format($amount, 2);
    }
}

if (!function_exists('get_active_guard')) {
    /**
     * Get the currently active guard name.
     */
    function get_active_guard(): ?string
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (auth()->guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }
}
