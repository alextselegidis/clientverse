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
        <input type="text" name="items[{{ $index }}][description]" class="form-control" maxlength="255" list="item-catalog"
               value="{{ $line['description'] ?? '' }}" aria-label="{{ __('description') }}">
    </td>
    <td>
        <input type="number" name="items[{{ $index }}][quantity]" class="form-control" step="0.01" min="0"
               value="{{ $line['quantity'] ?? '' }}" aria-label="{{ __('quantity') }}">
    </td>
    <td>
        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control" step="0.01"
               value="{{ $line['unit_price'] ?? '' }}" aria-label="{{ __('unit_price') }}">
    </td>
    <td>
        <input type="number" name="items[{{ $index }}][tax_rate]" class="form-control" step="0.01" min="0" max="100"
               value="{{ $line['tax_rate'] ?? '' }}" aria-label="{{ __('tax') }}">
    </td>
</tr>
