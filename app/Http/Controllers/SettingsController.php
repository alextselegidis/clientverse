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

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Setting::class);

        return view('pages.settings', [
            'defaultLocale' => setting('default_locale'),
            'defaultTimezone' => setting('default_timezone'),
            'defaultCurrency' => setting('default_currency') ?? 'USD',
        ]);
    }

    public function update(Request $request)
    {
        Gate::authorize('update', Setting::class);

        $request->validate([
            'default_locale' => 'required',
            'default_timezone' => 'required',
            'default_currency' => 'required',
        ]);

        setting([
            'default_locale' => $request->input('default_locale'),
            'default_timezone' => $request->input('default_timezone'),
            'default_currency' => $request->input('default_currency'),
        ]);

        return redirect(route('setup.localization'))->with('success', __('record_saved_message'));
    }
}
