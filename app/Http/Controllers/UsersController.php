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

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $query = User::query();

        $q = $request->query('q');

        if ($q) {
            $query->where('name', 'like', '%' . $q . '%');
        }

        $sort = $request->query('sort');
        $direction = $request->query('direction');

        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $users = $query->cursorPaginate(25);

        return view('pages.users', [
            'users' => $users,
            'q' => $q,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        $request->validate([
            'name' => 'required',
        ]);

        $payload = $request->all();

        $user = User::create([
            'name' => $payload['name'],
            'email' => 'new-user-' . strtolower(Str::random(5)) . '@example.org',
            'password' => Hash::make(Str::random(8)),
        ]);

        return redirect(route('setup.users.edit', ['user' => $user->id]));
        // return redirect(request()->fullUrlWithoutQuery('create'));
    }

    public function edit(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        return view('pages.users-edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $request->validate([
            'name' => 'required|min:2',
            'email' => 'required|min:2',
            'password' => 'nullable|min:8|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'nullable|min:8',
        ]);

        $payload = $request->input();
        $payload['is_active'] = $request->has('is_active');

        if (empty($payload['password'])) {
            unset($payload['password'], $payload['password_confirmation']);
        }

        if (
            $user->id === $request->user()->id &&
            $user->isAdmin() &&
            $payload['role'] !== RoleEnum::ADMIN->value &&
            User::where('role', RoleEnum::ADMIN->value)->count() === 1
        ) {
            return back()->with('error', __('cannot_deactivate_last_admin'));
        }

        // Prevent deactivating self
        if ($user->id === $request->user()->id && !$payload['is_active']) {
            return back()->with('error', __('cannot_deactivate_self'));
        }

        // Prevent deactivating last admin
        if (
            $user->isAdmin() &&
            !$payload['is_active'] &&
            User::where('role', RoleEnum::ADMIN->value)->where('is_active', true)->count() === 1
        ) {
            return back()->with('error', __('cannot_deactivate_last_admin'));
        }

        $user->fill($payload);

        $user->save();

        return redirect(route('setup.users.edit', $user->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, User $user)
    {
        Gate::authorize('delete', $user);

        if ($user->id === request()->user()->id) {
            return redirect()->route('setup.users')->with('error', __('cannotDeleteCurrentUser'));
        }

        // Check if user is an admin
        if ($user->isAdmin()) {
            if (User::where('role', RoleEnum::ADMIN->value)->count() <= 1) {
                return back()->with('error', __('cannot_deactivate_last_admin'));
            }
        }

        $user->delete();

        return redirect(route('setup.users'))->with('success', __('record_deleted_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $currentUserId = $request->user()->id;
        $ids = collect($request->input('ids'))->filter(fn($id) => $id != $currentUserId);

        $adminCount = User::where('role', RoleEnum::ADMIN->value)->count();
        $adminsToDelete = User::whereIn('id', $ids)->where('role', RoleEnum::ADMIN->value)->count();

        if ($adminCount - $adminsToDelete < 1) {
            return redirect(route('setup.users'))->with('error', __('cannot_deactivate_last_admin'));
        }

        User::whereIn('id', $ids)->delete();

        return redirect(route('setup.users'))->with('success', __('records_deleted_message'));
    }
}
