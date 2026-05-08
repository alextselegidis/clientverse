<?php

/* ----------------------------------------------------------------------------
 * Timecrack - Time Tracking Application
 *
 * @package     Timecrack
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://github.com/alextselegidis/timecrack
 * ---------------------------------------------------------------------------- */

namespace App\Auth;

use Illuminate\Auth\SessionGuard;

class AppSessionGuard extends SessionGuard
{
    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * The default Laravel implementation returns `remember_<guard>_<sha1(SessionGuard::class)>`,
     * which is identical for every Laravel install. When two installs share a domain, the
     * "remember me" cookie set by one would clobber/log out the other. We suffix the cookie
     * with a short hash of APP_URL so each install has its own cookie.
     */
    public function getRecallerName()
    {
        return parent::getRecallerName().'_'.substr(sha1((string) config('app.url')), 0, 8);
    }
}
