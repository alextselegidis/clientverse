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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class AccountController extends Controller
{
    public function index()
    {
        $tokens = auth()->user()->tokens;
        return view('pages.account', compact('tokens'));
    }
    public function update(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();
        return back()->with('success', __('record_saved_message'));
    }

    public function createToken(Request $request)
    {
        $request->validate([
            'token_name' => 'required|string|max:255',
        ]);

        $token = auth()->user()->createToken($request->token_name);

        return back()->with('new_token', $token->plainTextToken)->with('success', __('api_token_created'));
    }

    public function deleteToken(Request $request, $tokenId)
    {
        auth()->user()->tokens()->where('id', $tokenId)->delete();

        return back()->with('success', __('api_token_deleted'));
    }
}
