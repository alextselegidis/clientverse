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

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnnouncementsController extends Controller
{
    public function index()
    {
        return view('pages.announcements', ['announcements' => Announcement::latest()->paginate(25)]);
    }

    public function create()
    {
        return view('pages.announcements-edit', ['announcement' => new Announcement(['audience' => 'staff'])]);
    }

    public function store(Request $request)
    {
        Announcement::create($this->validateAnnouncement($request));

        return redirect(route('setup.announcements'))->with('success', __('record_saved_message'));
    }

    public function edit(Announcement $announcement)
    {
        return view('pages.announcements-edit', ['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validateAnnouncement($request));

        return redirect(route('setup.announcements'))->with('success', __('record_saved_message'));
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect(route('setup.announcements'))->with('success', __('record_deleted_message'));
    }

    private function validateAnnouncement(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:20000',
            'audience' => ['required', Rule::in(array_keys(Announcement::audiences()))],
            'visible_until' => 'nullable|date',
        ]);
    }
}
