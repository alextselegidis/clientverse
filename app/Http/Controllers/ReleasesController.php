<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Release;
use Illuminate\Http\Request;

class ReleasesController extends Controller
{
    public function index(Request $request)
    {
        $query = Release::query();

        $q = $request->query('q');
        if ($q) {
            $query->where('version', 'like', "%$q%");
        }

        $sort = $request->query('sort');
        $direction = $request->query('direction');
        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $releases = $query->cursorPaginate(25);

        return view('pages.releases', compact('releases', 'q'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'version' => 'required',
        ]);

        $release = Release::create($request->all());

        return redirect(route('releases.edit', $release->id));
    }

    public function show(Release $release)
    {
        return view('pages.releases-show', compact('release'));
    }

    public function edit(Release $release)
    {
        $projectOptions = Project::toOptions();

        return view('pages.releases-edit', compact('release', 'projectOptions'));
    }

    public function update(Request $request, Release $release)
    {
        $request->validate([
            'version' => 'required|min:1',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $release->fill($request->all());
        $release->save();

        return redirect(route('releases.edit', $release->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Release $release)
    {
        $release->delete();

        return redirect()->back()->with('success', __('record_deleted_message'));
    }
}
