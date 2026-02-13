{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Simple Bookmark Manager
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://github.com/alextselegidis/clientverse
 * ---------------------------------------------------------------------------- */
--}}

@php
    $locales = [
        'en' => 'English',
        'de' => 'German (Deutsch)',
        'es' => 'Spanish (Español)',
        'fr' => 'French (Français)',
        'it' => 'Italian (Italiano)',
        'pt' => 'Portuguese (Português)',
        'nl' => 'Dutch (Nederlands)',
        'ru' => 'Russian (Русский)',
        'zh' => 'Chinese (中文)',
        'ja' => 'Japanese (日本語)',
        'ko' => 'Korean (한국어)',
        'ar' => 'Arabic (العربية)',
        'hi' => 'Hindi (हिन्दी)',
        'tr' => 'Turkish (Türkçe)',
        'pl' => 'Polish (Polski)',
        'sv' => 'Swedish (Svenska)',
        'da' => 'Danish (Dansk)',
        'fi' => 'Finnish (Suomi)',
        'no' => 'Norwegian (Norsk)',
        'el' => 'Greek (Ελληνικά)',
    ];
@endphp
<select name="{{ $name }}" id="{{ $id }}" class="form-select" {{ !empty($required) ? 'required' : '' }}>
    @foreach($locales as $code => $label)
        <option value="{{ $code }}" {{ (old($name, $value ?? '') == $code) ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
