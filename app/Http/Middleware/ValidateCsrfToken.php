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

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;
use Symfony\Component\HttpFoundation\Cookie;

class ValidateCsrfToken extends Middleware
{
    /**
     * The CSRF cookie is named "XSRF-TOKEN" in every Laravel install, so two
     * installs sharing a domain overwrite each other's. Namespace it the same way
     * the session and "remember me" cookies are namespaced.
     */
    public static function cookieName(): string
    {
        return 'XSRF-TOKEN-'.cookie_suffix();
    }

    /**
     * Create a new CSRF cookie under this installation's own name.
     */
    protected function newCookie($request, $config)
    {
        return new Cookie(
            static::cookieName(),
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false
        );
    }
}
