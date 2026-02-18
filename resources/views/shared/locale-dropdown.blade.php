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
        // English variants
        'en' => 'English',
        'en-US' => 'English (United States)',
        'en-GB' => 'English (United Kingdom)',
        'en-AU' => 'English (Australia)',
        'en-CA' => 'English (Canada)',
        'en-IE' => 'English (Ireland)',
        'en-NZ' => 'English (New Zealand)',
        'en-ZA' => 'English (South Africa)',
        'en-IN' => 'English (India)',
        // German variants
        'de' => 'German (Deutsch)',
        'de-DE' => 'German (Germany)',
        'de-AT' => 'German (Austria)',
        'de-CH' => 'German (Switzerland)',
        // Spanish variants
        'es' => 'Spanish (Español)',
        'es-ES' => 'Spanish (Spain)',
        'es-MX' => 'Spanish (Mexico)',
        'es-AR' => 'Spanish (Argentina)',
        'es-CO' => 'Spanish (Colombia)',
        'es-CL' => 'Spanish (Chile)',
        // French variants
        'fr' => 'French (Français)',
        'fr-FR' => 'French (France)',
        'fr-CA' => 'French (Canada)',
        'fr-BE' => 'French (Belgium)',
        'fr-CH' => 'French (Switzerland)',
        // Portuguese variants
        'pt' => 'Portuguese (Português)',
        'pt-BR' => 'Portuguese (Brazil)',
        'pt-PT' => 'Portuguese (Portugal)',
        // Italian
        'it' => 'Italian (Italiano)',
        'it-IT' => 'Italian (Italy)',
        'it-CH' => 'Italian (Switzerland)',
        // Dutch variants
        'nl' => 'Dutch (Nederlands)',
        'nl-NL' => 'Dutch (Netherlands)',
        'nl-BE' => 'Dutch (Belgium)',
        // Chinese variants
        'zh' => 'Chinese (中文)',
        'zh-CN' => 'Chinese Simplified (简体中文)',
        'zh-TW' => 'Chinese Traditional (繁體中文)',
        'zh-HK' => 'Chinese (Hong Kong)',
        // Other major languages
        'ja' => 'Japanese (日本語)',
        'ko' => 'Korean (한국어)',
        'ru' => 'Russian (Русский)',
        'ar' => 'Arabic (العربية)',
        'ar-SA' => 'Arabic (Saudi Arabia)',
        'ar-EG' => 'Arabic (Egypt)',
        'ar-AE' => 'Arabic (UAE)',
        'hi' => 'Hindi (हिन्दी)',
        'tr' => 'Turkish (Türkçe)',
        'pl' => 'Polish (Polski)',
        'sv' => 'Swedish (Svenska)',
        'sv-SE' => 'Swedish (Sweden)',
        'da' => 'Danish (Dansk)',
        'fi' => 'Finnish (Suomi)',
        'no' => 'Norwegian (Norsk)',
        'nb' => 'Norwegian Bokmål',
        'nn' => 'Norwegian Nynorsk',
        'el' => 'Greek (Ελληνικά)',
        'cs' => 'Czech (Čeština)',
        'sk' => 'Slovak (Slovenčina)',
        'hu' => 'Hungarian (Magyar)',
        'ro' => 'Romanian (Română)',
        'bg' => 'Bulgarian (Български)',
        'uk' => 'Ukrainian (Українська)',
        'he' => 'Hebrew (עברית)',
        'th' => 'Thai (ไทย)',
        'vi' => 'Vietnamese (Tiếng Việt)',
        'id' => 'Indonesian (Bahasa Indonesia)',
        'ms' => 'Malay (Bahasa Melayu)',
        'tl' => 'Filipino (Tagalog)',
    ];
@endphp
<select name="{{ $name }}" id="{{ $id }}" class="form-select" {{ !empty($required) ? 'required' : '' }}>
    @foreach($locales as $code => $label)
        <option value="{{ $code }}" {{ (old($name, $value ?? '') == $code) ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
