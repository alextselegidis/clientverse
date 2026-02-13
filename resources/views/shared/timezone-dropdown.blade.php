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

<select name="{{$name}}" id="{{$id}}" class="form-select" {{$required ? 'required': ''}}" style="{{$style ?? ''}}">
    @foreach (DateTimeZone::listIdentifiers() as $tz)
        <option value="{{ $tz }}" {{ $value === $tz ? 'selected' : '' }}>
            {{ $tz }}
        </option>
    @endforeach
</select>
