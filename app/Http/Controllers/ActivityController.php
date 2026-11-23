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

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('user')->latest('id');

        $userId = $request->query('user_id');
        $type = $request->query('type');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($type) {
            $query->where('subject_type', $type);
        }

        return view('pages.activity', [
            'activities' => $query->paginate(50)->withQueryString(),
            'users' => User::orderBy('name')->pluck('name', 'id'),
            'userId' => $userId,
            'type' => $type,
        ]);
    }
}
