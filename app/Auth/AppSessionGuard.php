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

namespace App\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;

class AppSessionGuard extends SessionGuard
{
    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * The default Laravel implementation returns `remember_<guard>_<sha1(SessionGuard::class)>`,
     * which is identical for every Laravel install. When two installs share a domain, the
     * "remember me" cookie set by one would clobber the other's and sign that user out.
     */
    public function getRecallerName()
    {
        return parent::getRecallerName().'_'.cookie_suffix();
    }

    /**
     * Get the currently authenticated user.
     *
     * The `is_active` flag is only checked when credentials are verified at login, so a
     * deactivated user would keep browsing on an existing session or be silently signed
     * back in by their "remember me" cookie. Both paths funnel through here, so this is
     * the single place where a deactivated account gets dropped.
     */
    public function user(): ?Authenticatable
    {
        $user = parent::user();

        if ($user && ! $user->is_active) {
            $this->clearUserDataFromStorage();

            $this->user = null;
            $this->loggedOut = true;

            return null;
        }

        return $user;
    }
}
