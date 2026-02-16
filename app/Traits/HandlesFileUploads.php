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

namespace App\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait HandlesFileUploads
{
    /**
     * Handle file uploads for a model.
     */
    protected function handleFileUploads(Request $request, Model $model): void
    {
        if (!$request->hasFile('files')) {
            return;
        }

        $allowedMimeTypes = File::$allowedMimeTypes;
        $allowedExtensions = File::$allowedExtensions;

        foreach ($request->file('files') as $file) {
            // Validate MIME type
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                continue;
            }

            // Validate extension
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedExtensions)) {
                continue;
            }

            // Generate unique filename
            $storedName = Str::uuid() . '.' . $extension;

            // Store file
            $file->storeAs('files', $storedName, 'local');

            // Create database record
            File::create([
                'fileable_id' => $model->id,
                'fileable_type' => get_class($model),
                'uploaded_by' => $request->user()->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $storedName,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
