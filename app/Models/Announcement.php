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

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $fillable = ['title', 'body', 'audience', 'visible_until'];

    protected $casts = [
        'visible_until' => 'date',
    ];

    /**
     * Announcements still up today for "staff" or "customers", newest first.
     */
    public function scopeShownTo($query, string $audience)
    {
        return $query->whereIn('audience', [$audience, 'everyone'])
            ->where(fn ($query) => $query->whereNull('visible_until')->orWhereDate('visible_until', '>=', today()))
            ->latest();
    }

    /**
     * The body as HTML, with raw HTML escaped like knowledge base articles.
     */
    public function getBodyHtmlAttribute(): HtmlString
    {
        return new HtmlString(Str::markdown($this->body, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]));
    }

    public function isExpired(): bool
    {
        return $this->visible_until?->lt(today()) ?? false;
    }

    public static function audiences(): array
    {
        return [
            'staff' => __('staff'),
            'customers' => __('customers'),
            'everyone' => __('everyone'),
        ];
    }
}
