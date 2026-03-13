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
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class ExtendRememberSession
{
    /**
     * Extend the session lifetime for users who logged in with "remember me"
     * so that their session never expires.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rememberCookieName = Auth::guard('web')->getRecallerName();

        if ($request->cookies->has($rememberCookieName)) {
            $rememberDuration = Config::get('auth.guards.web.remember', 52560000);

            Config::set('session.lifetime', $rememberDuration);
        }

        return $next($request);
    }
}
