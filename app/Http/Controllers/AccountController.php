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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            // Taking over an account is a matter of changing the address the reset
            // link goes to, or the password itself, so both ask for the current
            // password: a borrowed session on its own is not enough.
            'current_password' => [
                'nullable',
                Rule::requiredIf(
                    fn() => $request->input('email') !== $user->email || filled($request->input('password'))
                ),
                'current_password',
            ],
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        $changedPassword = !empty($validated['password']);

        if ($changedPassword) {
            $user->password = Hash::make($validated['password']);

            // A fresh recaller invalidates this user's other "remember me" logins.
            $user->remember_token = Str::random(60);
        }

        $user->save();

        if ($changedPassword) {
            $user->tokens()->delete();

            $this->forgetOtherSessions($request, $user);
        }

        return back()->with('success', __('record_saved_message'));
    }

    /**
     * Drop the user's other browser sessions after a password change.
     *
     * Only the database driver keeps sessions somewhere they can be deleted per
     * user; with the file driver the other sessions stay valid until they expire,
     * which is one more reason the application ships with the database one.
     */
    private function forgetOtherSessions(Request $request, User $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();
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
