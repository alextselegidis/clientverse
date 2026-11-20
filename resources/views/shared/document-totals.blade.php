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
{{-- Totals of an invoice or estimate. Pass 'payments' => true to add amount paid and balance due. --}}
<dl class="row ms-auto {{ $class ?? '' }}" style="max-width: 320px;">
    <dt class="col-6 fw-normal text-muted">{{ __('subtotal') }}</dt>
    <dd class="col-6 text-end mb-1">{{ format_currency($document->subtotal, $document->currency) }}</dd>
    <dt class="col-6 fw-normal text-muted">{{ __('tax') }}</dt>
    <dd class="col-6 text-end mb-1">{{ format_currency($document->tax, $document->currency) }}</dd>
    <dt class="col-6">{{ __('total') }}</dt>
    <dd class="col-6 text-end fw-bold mb-1">{{ format_currency($document->total, $document->currency) }}</dd>
    @if($payments ?? false)
        <dt class="col-6 fw-normal text-muted">{{ __('amount_paid') }}</dt>
        <dd class="col-6 text-end mb-1">{{ format_currency($document->amount_paid - $document->credited, $document->currency) }}</dd>
        @if($document->credited > 0)
            <dt class="col-6 fw-normal text-muted">{{ __('credited') }}</dt>
            <dd class="col-6 text-end mb-1">{{ format_currency($document->credited, $document->currency) }}</dd>
        @endif
        <dt class="col-6">{{ __('balance_due') }}</dt>
        <dd class="col-6 text-end fw-bold mb-0">{{ format_currency($document->balance, $document->currency) }}</dd>
    @endif
</dl>
