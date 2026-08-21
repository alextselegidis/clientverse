<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
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

if (!function_exists('cookie_suffix')) {
    /**
     * A short fingerprint that is unique to this installation.
     *
     * Several Clientverse installs can share one domain. Browsers key cookies by
     * name, path and domain only, so two installs writing the same cookie name at
     * path "/" overwrite each other and signing in to one signs you out of the
     * other. Every cookie name the application controls carries this suffix.
     *
     * It is derived from APP_KEY as well as APP_URL because APP_URL is often left
     * at its default, or is identical for two installs living under different
     * paths of the same host. APP_KEY is always unique per install. The value is a
     * truncated hash behind a domain separator, so the cookie name reveals nothing
     * usable about the key.
     */
    function cookie_suffix(): string
    {
        static $suffix = null;

        return $suffix ??= substr(
            sha1('clientverse-cookie|' . env('APP_KEY', '') . '|' . env('APP_URL', '')),
            0,
            8
        );
    }
}
