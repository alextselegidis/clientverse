{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */
--}}
{{-- The department picker on ticket forms; hidden until an admin has set up departments. --}}
@php($departments = \App\Models\Department::options())
@if($departments)
    <div class="{{ $class ?? 'mb-3' }}">
        <label for="department_id" class="form-label">{{ __('department') }}</label>
        <select id="department_id" name="department_id" class="form-select">
            <option value="">-</option>
            @foreach($departments as $id => $name)
                <option value="{{ $id }}" @selected(old('department_id', $selected ?? null) == $id)>{{ $name }}</option>
            @endforeach
        </select>
        @error('department_id')
        <span class="form-text text-danger">{{ $message }}</span>
        @enderror
    </div>
@endif
