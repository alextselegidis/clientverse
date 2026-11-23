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

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentsController extends Controller
{
    public function index()
    {
        return view('pages.departments', [
            'departments' => Department::withCount('tickets')->orderBy('name')->paginate(25),
        ]);
    }

    public function create()
    {
        return view('pages.departments-edit', ['department' => new Department()]);
    }

    public function store(Request $request)
    {
        Department::create($this->validateDepartment($request));

        return redirect(route('setup.departments'))->with('success', __('record_saved_message'));
    }

    public function edit(Department $department)
    {
        return view('pages.departments-edit', ['department' => $department]);
    }

    public function update(Request $request, Department $department)
    {
        $department->update($this->validateDepartment($request, $department));

        return redirect(route('setup.departments'))->with('success', __('record_saved_message'));
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect(route('setup.departments'))->with('success', __('record_deleted_message'));
    }

    private function validateDepartment(Request $request, ?Department $department = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department)],
        ]);
    }
}
