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

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

/**
 * The customer-facing contract page. There is no login: the route's `signed`
 * middleware rejects any URL that was not produced by Contract::signingUrl().
 */
class PublicContractsController extends Controller
{
    public function show(Contract $contract)
    {
        return view('pages.public-contract', ['contract' => $contract->load('customer')]);
    }

    public function sign(Request $request, Contract $contract)
    {
        if ($contract->signed_at) {
            return back();
        }

        $data = $request->validate([
            'signer_name' => 'required|string|max:255',
            'agree' => 'accepted',
        ]);

        $contract->sign($data['signer_name'], $request->ip());

        return back()->with('success', __('contract_signed_message'));
    }
}
