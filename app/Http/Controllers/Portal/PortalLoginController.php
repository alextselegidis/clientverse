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

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Sign-in for customer contacts. Reuses the staff login's rate limiting; only
 * the guard, the view and the destination differ.
 */
class PortalLoginController extends LoginController
{
    public function index()
    {
        return view('pages.portal-login');
    }

    public function perform(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        $credentials = $request->only('email', 'password') + ['has_portal_access' => true];

        if (!Auth::guard('contact')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended(route('portal'));
    }

    public function logout(Request $request)
    {
        Auth::guard('contact')->logout();

        // A staff session in the same browser stays signed in, so only the token is renewed.
        $request->session()->regenerateToken();

        return redirect(route('portal.login'));
    }

    public function throttleKey(Request $request): string
    {
        return 'portal|' . parent::throttleKey($request);
    }
}
