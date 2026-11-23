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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (!function_exists('sort_link')) {
    function sort_link($column, $label): string
    {
        $direction = request('sort') === $column && request('direction') === 'asc' ? 'desc' : 'asc';
        $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction]);
        $icon = '<i class="bi ' . ($direction === 'asc' ? 'bi-caret-up' : 'bi-caret-down') . ' ms-2"></i>';
        return '<a href="' . $url . '">' . $label . $icon . '</a>';
    }
}

if (!function_exists('sort_query')) {
    /**
     * Apply a ?sort=&direction= pair from the query string to a list query.
     *
     * Both values arrive straight from the URL, so they are checked here rather
     * than in every list controller: an unknown column is ignored and the query
     * keeps its own order, and so is a direction that is not asc or desc, which
     * Eloquent would otherwise reject with an exception. Columns the model hides
     * - the password hash, the remember token - are not sortable either, since
     * ordering by one of them leaks its content a comparison at a time.
     */
    function sort_query($query, ?string $sort, ?string $direction)
    {
        if (empty($sort)) {
            return $query;
        }

        $model = $query->getModel();

        // The column list comes from information_schema, the slowest query on most list pages; it only
        // changes with a migration, and a migration comes with a new version.
        $columns = Cache::rememberForever(
            'columns:' . $model->getTable() . ':' . config('app.version'),
            fn () => Schema::getColumnListing($model->getTable()),
        );
        $sortable = array_diff($columns, $model->getHidden());

        if (!in_array($sort, $sortable, true)) {
            return $query;
        }

        $direction = strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';

        // The list views mark the sorted column from request('sort'), so a controller's default sort shows too.
        request()->merge(['sort' => $sort, 'direction' => $direction]);

        return $query->orderBy($sort, $direction);
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

            app()->forgetInstance('settings');

            return null;
        }

        return app('settings')[$key] ?? $default;
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

if (!function_exists('format_minutes')) {
    /**
     * 135 => "2h 15m". Used for logged time.
     */
    function format_minutes(int $minutes): string
    {
        return intdiv($minutes, 60) . 'h ' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . 'm';
    }
}

if (!function_exists('email_template')) {
    /**
     * The subject and body for emailing a document ("invoice", "estimate" or
     * "contract"), with {placeholders} filled in. Admins edit the templates under
     * Setup; the defaults below apply until they do.
     */
    function email_template(string $type, array $values): array
    {
        $defaults = [
            'subject' => __('email_template_' . $type . '_subject'),
            'body' => __('email_template_' . $type . '_body'),
        ];

        $replace = collect($values)->mapWithKeys(fn ($value, $key) => ['{' . $key . '}' => (string) $value])->all();

        return [
            'subject' => strtr(setting($type . '_email_subject') ?: $defaults['subject'], $replace),
            'body' => strtr(setting($type . '_email_body') ?: $defaults['body'], $replace),
        ];
    }
}
