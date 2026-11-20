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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'category', 'body', 'is_public'];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        // The slug is set once, so a public link keeps working after the title changes.
        static::creating(function (Article $article) {
            $base = Str::slug($article->title) ?: 'article';
            $slug = $base;

            for ($i = 2; self::withTrashed()->where('slug', $slug)->exists(); $i++) {
                $slug = $base . '-' . $i;
            }

            $article->slug = $slug;
        });
    }

    /**
     * The body as HTML. Raw HTML in the Markdown is escaped and unsafe links are
     * dropped, so an article cannot inject script into the public page.
     */
    public function getBodyHtmlAttribute(): HtmlString
    {
        return new HtmlString(Str::markdown($this->body, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]));
    }

    public static function categories(): array
    {
        return self::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
    }
}
