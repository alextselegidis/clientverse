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
{{-- The Created and Modified cells of a list row; see shared.timestamp-headers. --}}
<td class="d-none d-{{ $tier ?? 'xl' }}-table-cell text-nowrap text-muted">{{ $record->created_at?->format('M d, Y') }}</td>
<td class="d-none d-{{ $tier ?? 'xl' }}-table-cell text-nowrap text-muted">{{ $record->updated_at?->format('M d, Y') }}</td>
