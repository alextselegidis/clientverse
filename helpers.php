<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Simple Bookmark Manager
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://github.com/alextselegidis/clientverse
 * ---------------------------------------------------------------------------- */

use App\Models\Setting;

if (!function_exists('sort_link')) {
    function sort_link($column, $label): string
    {
        $direction = request('sort') === $column && request('direction') === 'asc' ? 'desc' : 'asc';
        $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction]);
        $icon = '<i class="bi ' . ($direction === 'asc' ? 'bi-caret-up' : 'bi-caret-down') . ' ms-2"></i>';
        return '<a href="' . $url . '">' . $label . $icon . '</a>';
    }
}

if (!function_exists('setting')) {
    function setting(array|string|null $key = null, mixed $default = null): mixed
    {
        if (empty($key)) {
            throw new InvalidArgumentException('The $key argument cannot be empty.');
        }

        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $setting = Setting::query()->where('name', $name)->first();

                if (empty($setting)) {
                    $setting = new Setting([
                        'name' => $name,
                    ]);
                }

                $setting->value = $value;

                $setting->save();
            }

            return null;
        }

        $setting = Setting::query()->where('name', $key)->first() ?? null;

        return $setting->value ?? $default;
    }
}

if (!function_exists('default_currency')) {
    function default_currency(): string
    {
        return setting('default_currency') ?? 'USD';
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float|int|null $amount, ?string $currency = null): string
    {
        if ($amount === null) {
            return '-';
        }
        
        $currency = $currency ?? default_currency();
        
        return $currency . ' ' . number_format($amount, 2);
    }
}
