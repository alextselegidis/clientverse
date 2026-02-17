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

use App\Enums\ProjectStatusEnum;
use App\Models\Customer;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Traits\HandlesFileUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectsController extends Controller
{
    use HandlesFileUploads;

    public function index(Request $request)
    {
        $query = Project::with('customer');

        $q = $request->query('q');
        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('name', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $projects = $query->cursorPaginate(25);

        return view('pages.projects', [
            'projects' => $projects,
            'q' => $q,
            'status' => $status,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.projects-edit', [
            'project' => new Project(),
            'customers' => Customer::toOptions(),
            'users' => User::all(),
            'uploadLimits' => File::getUploadLimits(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $project = Project::create($request->all());

        if ($request->has('members')) {
            $project->members()->sync($request->input('members', []));
        }

        $this->handleFileUploads($request, $project);

        return redirect(route('projects.show', $project->id))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Project $project)
    {
        return view('pages.projects-show', [
            'project' => $project->load(['customer', 'milestones', 'members', 'contracts', 'files']),
            'uploadLimits' => File::getUploadLimits(),
        ]);
    }

    public function edit(Request $request, Project $project)
    {
        return view('pages.projects-edit', [
            'project' => $project->load(['members', 'files']),
            'customers' => Customer::toOptions(),
            'users' => User::all(),
            'uploadLimits' => File::getUploadLimits(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|min:2',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $project->fill($request->all());
        $project->save();

        if ($request->has('members')) {
            $project->members()->sync($request->input('members', []));
        }

        $this->handleFileUploads($request, $project);

        return redirect(route('projects.edit', $project->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Project $project)
    {
        foreach ($project->files as $file) {
            $file->deleteFromStorage();
        }

        $project->delete();

        return redirect(route('projects'))->with('success', __('record_deleted_message'));
    }

    public function uploadFiles(Request $request, Project $project)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:' . (File::getUploadLimits()['max_file_size'] / 1024),
        ]);

        $this->handleFileUploads($request, $project);

        return redirect(route('projects.show', $project->id))->with('success', __('files_uploaded_message'));
    }

    public function downloadFile(Request $request, Project $project, File $file)
    {
        if ($file->fileable_id !== $project->id || $file->fileable_type !== Project::class) {
            abort(404);
        }

        if (!$file->existsInStorage()) {
            return back()->with('error', __('file_not_found'));
        }

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function deleteFile(Request $request, Project $project, File $file)
    {
        if ($file->fileable_id !== $project->id || $file->fileable_type !== Project::class) {
            abort(404);
        }

        $file->deleteFromStorage();
        $file->delete();

        return redirect(route('projects.show', $project->id))->with('success', __('file_deleted_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:projects,id',
        ]);

        $projects = Project::whereIn('id', $request->input('ids'))->get();

        foreach ($projects as $project) {
            foreach ($project->files as $file) {
                $file->deleteFromStorage();
            }
            $project->delete();
        }

        return redirect(route('projects'))->with('success', __('records_deleted_message'));
    }
}
