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
<tr>
    <td>
        <input type="text" name="questions[{{ $index }}][text]" class="form-control" maxlength="500"
               value="{{ $question['text'] ?? '' }}" aria-label="{{ __('question') }}">
    </td>
    <td data-label="{{ __('type') }}">
        <select name="questions[{{ $index }}][type]" class="form-select" aria-label="{{ __('type') }}">
            @foreach(\App\Models\Survey::TYPES as $type)
                <option value="{{ $type }}" @selected(($question['type'] ?? 'text') === $type)>{{ __('question_type_' . $type) }}</option>
            @endforeach
        </select>
    </td>
    <td data-label="{{ __('options') }}">
        <textarea name="questions[{{ $index }}][options]" class="form-control" rows="2"
                  aria-label="{{ __('options') }}">{{ is_array($question['options'] ?? null) ? implode("\n", $question['options']) : ($question['options'] ?? '') }}</textarea>
    </td>
</tr>
