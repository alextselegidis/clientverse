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
{{-- Print view of an invoice or estimate: $document, $title, $backUrl, $dates (label => date). --}}
{{-- Public links pass $backUrl = null, $respondUrl when an estimate or proposal can be accepted or declined, --}}
{{-- and $payUrls (gateway => url or null) when an invoice can be paid online. --}}
{{-- A proposal also prints its subject and content. --}}
{{-- A standalone page; the browser's "Save as PDF" turns it into the PDF, so no PDF library is needed. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('shared.head')
    @if(!$backUrl)
        <meta name="robots" content="noindex">
    @endif
    <style>
        @page { margin: 16mm; }
        @media print { body { background: #fff !important; } .document-sheet { box-shadow: none !important; padding: 0 !important; } }
    </style>
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 860px;">
    <div class="d-flex justify-content-end gap-2 mb-3 d-print-none">
        @if($backUrl)
            <a href="{{ $backUrl }}" class="btn btn-light border">{{ __('back') }}</a>
        @endif
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer me-2"></i>{{ __('print_or_save_pdf') }}
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-print-none">{{ session('success') }}</div>
    @endif
    {{-- Stripe sends the customer back here with paid=1; the webhook may land a moment later. --}}
    @if(!$backUrl && request('paid'))
        <div class="alert alert-success d-print-none">{{ __('online_payment_received_message') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-print-none">{{ session('error') }}</div>
    @endif

    @if($respondUrl ?? null)
        <form action="{{ $respondUrl }}" method="POST" class="d-flex justify-content-end gap-2 mb-3 d-print-none">
            @csrf
            <button type="submit" name="decision" value="declined" class="btn btn-light border">{{ __('decline') }}</button>
            <button type="submit" name="decision" value="accepted" class="btn btn-primary">{{ $acceptLabel ?? __('accept_estimate') }}</button>
        </form>
    @endif

    @if(array_filter($payUrls ?? []))
        <div class="d-flex flex-wrap justify-content-end gap-2 mb-3 d-print-none">
            @if($payUrls['stripe'] ?? null)
                <form action="{{ $payUrls['stripe'] }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-credit-card me-2"></i>{{ __('pay_amount', ['amount' => format_currency($document->balance, $document->currency)]) }}
                    </button>
                </form>
            @endif
            @if($payUrls['paypal'] ?? null)
                <form action="{{ $payUrls['paypal'] }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-light border">
                        <i class="bi bi-paypal me-2"></i>{{ __('pay_with_paypal') }}
                    </button>
                </form>
            @endif
        </div>
    @endif

    <div class="document-sheet bg-white shadow-sm rounded-3 p-5">
        <div class="d-flex justify-content-between align-items-start mb-5">
            <div>
                <h1 class="h3 mb-1">{{ $title }}</h1>
                <div class="text-muted">{{ $document->number }}</div>
                @if($document->subject)
                    <div class="fw-medium mt-2">{{ $document->subject }}</div>
                @endif
            </div>
            <div class="text-end">
                <div class="fw-bold">{{ setting('company_name') ?: config('app.name') }}</div>
                @if($address = setting('company_address'))
                    <div class="small text-muted">{!! nl2br(e($address)) !!}</div>
                @endif
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-7">
                <div class="small text-uppercase text-muted mb-1">{{ __('bill_to') }}</div>
                @if($customer = $document->customer)
                    <div class="fw-medium">{{ $customer->company ?: $customer->name }}</div>
                    @if($customer->billing_address ?: $customer->address)
                        <div class="small">{!! nl2br(e($customer->billing_address ?: $customer->address)) !!}</div>
                    @endif
                    @if($customer->vat_id)
                        <div class="small">{{ __('vat_id') }}: {{ $customer->vat_id }}</div>
                    @endif
                @endif
            </div>
            <div class="col-5 text-end small">
                @foreach($dates as $label => $date)
                    <div><span class="text-muted">{{ $label }}:</span> {{ $date?->format('M d, Y') ?? '-' }}</div>
                @endforeach
            </div>
        </div>

        @if($document->content)
            <div class="mb-5">{!! nl2br(e($document->content)) !!}</div>
        @endif

        <table class="table align-middle">
            <thead>
            <tr class="small text-uppercase text-muted">
                <th class="fw-normal">{{ __('description') }}</th>
                <th class="fw-normal text-end">{{ __('quantity') }}</th>
                <th class="fw-normal text-end">{{ __('unit_price') }}</th>
                <th class="fw-normal text-end">{{ __('tax') }}</th>
                <th class="fw-normal text-end">{{ __('amount') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($document->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-end text-nowrap">{{ rtrim(rtrim($item->quantity, '0'), '.') }}</td>
                    <td class="text-end text-nowrap">{{ format_currency($item->unit_price, $document->currency) }}</td>
                    <td class="text-end text-nowrap">{{ rtrim(rtrim($item->tax_rate, '0'), '.') }}%</td>
                    <td class="text-end text-nowrap">{{ format_currency($item->subtotal, $document->currency) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @include('shared.document-totals', ['document' => $document, 'class' => 'mb-5', 'payments' => (float) ($document->amount_paid ?? 0) > 0])

        @if($document->notes)
            <div class="small text-uppercase text-muted mb-1">{{ __('notes') }}</div>
            <p class="small">{!! nl2br(e($document->notes)) !!}</p>
        @endif
        @if($document->terms)
            <div class="small text-uppercase text-muted mb-1">{{ __('terms') }}</div>
            <p class="small mb-0">{!! nl2br(e($document->terms)) !!}</p>
        @endif
    </div>
</div>
</body>
</html>
