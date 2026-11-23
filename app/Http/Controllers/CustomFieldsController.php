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

use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomFieldsController extends Controller
{
    public function index()
    {
        return view('pages.custom-fields', [
            'fields' => CustomField::orderBy('applies_to')->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('pages.custom-fields-edit', [
            'field' => new CustomField(['applies_to' => 'customers', 'type' => 'text']),
        ]);
    }

    public function store(Request $request)
    {
        CustomField::create($this->validateField($request));

        return redirect(route('setup.custom-fields'))->with('success', __('record_saved_message'));
    }

    public function edit(CustomField $customField)
    {
        return view('pages.custom-fields-edit', ['field' => $customField]);
    }

    public function update(Request $request, CustomField $customField)
    {
        // The records already hold values for this field, so it stays on the same kind of record.
        $customField->update(collect($this->validateField($request))->except('applies_to')->all());

        return redirect(route('setup.custom-fields'))->with('success', __('record_saved_message'));
    }

    public function destroy(CustomField $customField)
    {
        $customField->delete();

        return redirect(route('setup.custom-fields'))->with('success', __('record_deleted_message'));
    }

    private function validateField(Request $request): array
    {
        // The choices are typed one per line.
        $request->merge(['options' => collect(preg_split('/\R/', (string) $request->input('options')))
            ->map(fn ($option) => trim($option))->filter()->unique()->values()->all()]);

        $data = $request->validate([
            'applies_to' => ['required', Rule::in(array_keys(CustomField::appliesToOptions()))],
            'label' => 'required|string|max:255',
            'type' => ['required', Rule::in(array_keys(CustomField::types()))],
            'options' => 'array|required_if:type,select|max:100',
            'options.*' => 'string|max:255',
            'is_required' => 'boolean',
            'position' => 'nullable|integer|min:0|max:9999',
        ]);

        $data['options'] = $data['type'] === 'select' ? $data['options'] : null;
        $data['is_required'] = $request->boolean('is_required');
        $data['position'] = (int) ($data['position'] ?? 0);

        return $data;
    }
}
