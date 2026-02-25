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

use App\Models\Contract;
use App\Models\Customer;
use App\Models\File;
use App\Models\Project;
use App\Models\Sale;
use App\Traits\HandlesFileUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractsController extends Controller
{
    use HandlesFileUploads;

    public function index(Request $request)
    {
        $query = Contract::with('customer');

        $q = $request->query('q');
        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('title', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $type = $request->query('type');
        if ($type) {
            $query->where('type', $type);
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $contracts = $query->paginate(25)->withQueryString();

        return view('pages.contracts', [
            'contracts' => $contracts,
            'q' => $q,
            'status' => $status,
            'type' => $type,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function create(Request $request)
    {
        $contract = new Contract();
        
        if ($request->has('customer_id')) {
            $contract->customer_id = $request->query('customer_id');
        }
        
        return view('pages.contracts-edit', [
            'contract' => $contract,
            'customers' => Customer::toOptions(),
            'projects' => Project::toOptions(),
            'sales' => Sale::toOptions(),
            'uploadLimits' => File::getUploadLimits(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $contract = Contract::create($request->all());

        $this->handleFileUploads($request, $contract);

        return redirect(route('contracts.show', $contract->id))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Contract $contract)
    {
        return view('pages.contracts-show', [
            'contract' => $contract->load(['customer', 'project', 'sale', 'files']),
            'uploadLimits' => File::getUploadLimits(),
        ]);
    }

    public function edit(Request $request, Contract $contract)
    {
        return view('pages.contracts-edit', [
            'contract' => $contract->load('files'),
            'customers' => Customer::toOptions(),
            'projects' => Project::toOptions(),
            'sales' => Sale::toOptions(),
            'uploadLimits' => File::getUploadLimits(),
        ]);
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'title' => 'required|min:2',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $contract->fill($request->all());
        $contract->save();

        $this->handleFileUploads($request, $contract);

        return redirect(route('contracts.edit', $contract->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Contract $contract)
    {
        foreach ($contract->files as $file) {
            $file->deleteFromStorage();
        }

        $contract->delete();

        return redirect(route('contracts'))->with('success', __('record_deleted_message'));
    }

    public function uploadFiles(Request $request, Contract $contract)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:' . (File::getUploadLimits()['max_file_size'] / 1024),
        ]);

        $this->handleFileUploads($request, $contract);

        return redirect(route('contracts.show', $contract->id))->with('success', __('files_uploaded_message'));
    }

    public function downloadFile(Request $request, Contract $contract, File $file)
    {
        if ($file->fileable_id !== $contract->id || $file->fileable_type !== Contract::class) {
            abort(404);
        }

        if (!$file->existsInStorage()) {
            return back()->with('error', __('file_not_found'));
        }

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function deleteFile(Request $request, Contract $contract, File $file)
    {
        if ($file->fileable_id !== $contract->id || $file->fileable_type !== Contract::class) {
            abort(404);
        }

        $file->deleteFromStorage();
        $file->delete();

        return redirect(route('contracts.show', $contract->id))->with('success', __('file_deleted_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:contracts,id',
        ]);

        $contracts = Contract::whereIn('id', $request->input('ids'))->get();

        foreach ($contracts as $contract) {
            foreach ($contract->files as $file) {
                $file->deleteFromStorage();
            }
            $contract->delete();
        }

        return redirect(route('contracts'))->with('success', __('records_deleted_message'));
    }
}
