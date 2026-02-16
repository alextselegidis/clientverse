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

use App\Models\Customer;
use App\Models\File;
use App\Models\Tag;
use App\Traits\HandlesFileUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomersController extends Controller
{
    use HandlesFileUploads;

    public function index(Request $request)
    {
        $query = Customer::query();

        $q = $request->query('q');

        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('company', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%")
                    ->orWhere('website', 'like', "%{$q}%");
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

        $customers = $query->cursorPaginate(25);

        return view('pages.customers', [
            'customers' => $customers,
            'q' => $q,
            'status' => $status,
            'type' => $type,
            'tags' => Tag::toOptions(),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.customers-edit', [
            'customer' => new Customer(),
            'tags' => Tag::toOptions(),
            'uploadLimits' => File::getUploadLimits(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $customer = Customer::create($request->all());

        if ($request->has('tags')) {
            $customer->tags()->sync($request->input('tags', []));
        }

        $this->handleFileUploads($request, $customer);

        return redirect(route('customers.show', ['customer' => $customer->id]))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Customer $customer)
    {
        return view('pages.customers-show', [
            'customer' => $customer->load(['contacts', 'projects', 'contracts', 'sales', 'tags', 'files']),
            'uploadLimits' => File::getUploadLimits(),
        ]);
    }

    public function edit(Request $request, Customer $customer)
    {
        return view('pages.customers-edit', [
            'customer' => $customer->load(['tags', 'files']),
            'tags' => Tag::toOptions(),
            'uploadLimits' => File::getUploadLimits(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|min:2',
            'email' => 'nullable|email',
        ]);

        $customer->fill($request->input());
        $customer->save();

        if ($request->has('tags')) {
            $customer->tags()->sync($request->input('tags', []));
        }

        $this->handleFileUploads($request, $customer);

        return redirect(route('customers.edit', $customer->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Customer $customer)
    {
        foreach ($customer->files as $file) {
            $file->deleteFromStorage();
        }

        $customer->delete();

        return redirect(route('customers'))->with('success', __('record_deleted_message'));
    }

    public function uploadFiles(Request $request, Customer $customer)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:' . (File::getUploadLimits()['max_file_size'] / 1024),
        ]);

        $this->handleFileUploads($request, $customer);

        return redirect(route('customers.show', $customer->id))->with('success', __('files_uploaded_message'));
    }

    public function downloadFile(Request $request, Customer $customer, File $file)
    {
        if ($file->fileable_id !== $customer->id || $file->fileable_type !== Customer::class) {
            abort(404);
        }

        if (!$file->existsInStorage()) {
            return back()->with('error', __('file_not_found'));
        }

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function deleteFile(Request $request, Customer $customer, File $file)
    {
        if ($file->fileable_id !== $customer->id || $file->fileable_type !== Customer::class) {
            abort(404);
        }

        $file->deleteFromStorage();
        $file->delete();

        return redirect(route('customers.show', $customer->id))->with('success', __('file_deleted_message'));
    }
}
