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

    /**
     * Company details printed on documents, reminders, online payments and the
     * email templates.
     */
    private const BILLING_SETTINGS = [
        'company_name', 'company_address',
        'invoice_email_subject', 'invoice_email_body',
        'estimate_email_subject', 'estimate_email_body',
        'proposal_email_subject', 'proposal_email_body',
        'contract_email_subject', 'contract_email_body',
        'subscription_email_subject', 'subscription_email_body',
        'send_reminders', 'contract_reminder_days', 'staff_renewal_reminders', 'staff_renewal_days',
        'invoice_overdue_email_subject', 'invoice_overdue_email_body',
        'estimate_expiring_email_subject', 'estimate_expiring_email_body',
        'proposal_expiring_email_subject', 'proposal_expiring_email_body',
        'contract_expiring_email_subject', 'contract_expiring_email_body',
        'stripe_secret_key', 'stripe_webhook_secret',
        'paypal_client_id', 'paypal_secret', 'paypal_sandbox',
    ];

    public function billing(Request $request)
    {
        Gate::authorize('viewAny', Setting::class);

        return view('pages.settings-billing', [
            'values' => collect(self::BILLING_SETTINGS)->mapWithKeys(fn ($name) => [$name => setting($name)])->all(),
        ]);
    }

    public function updateBilling(Request $request)
    {
        Gate::authorize('update', Setting::class);

        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            '*_email_subject' => 'nullable|string|max:255',
            '*_email_body' => 'nullable|string|max:5000',
            'send_reminders' => 'nullable|boolean',
            'contract_reminder_days' => 'nullable|integer|min:1|max:365',
            'staff_renewal_reminders' => 'nullable|boolean',
            'staff_renewal_days' => ['nullable', 'string', 'max:50', 'regex:/^\s*\d{1,3}(\s*,\s*\d{1,3})*\s*$/'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255', 'regex:/^(sk|rk)_(test|live)_/'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255', 'regex:/^whsec_/'],
            'paypal_client_id' => 'nullable|string|max:255|required_with:paypal_secret',
            'paypal_secret' => 'nullable|string|max:255|required_with:paypal_client_id',
            'paypal_sandbox' => 'nullable|boolean',
        ]);

        setting(collect(self::BILLING_SETTINGS)->mapWithKeys(fn ($name) => [$name => $validated[$name] ?? ''])->all());

        return redirect(route('setup.billing'))->with('success', __('record_saved_message'));
    }
}
