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
{{-- Client portal home: every document the customer may see, linked to its signed public page. --}}
@extends('layouts.portal-layout')

@section('pageTitle')
    {{ __('client_portal') }}
@endsection

@php
    $documents = [
        'invoices' => [$invoices, fn ($invoice) => $invoice->display_status, \App\Models\Invoice::statusColors()],
        'estimates' => [$estimates, fn ($estimate) => $estimate->status, \App\Models\Estimate::statusColors()],
        'proposals' => [$proposals, fn ($proposal) => $proposal->status, \App\Models\Proposal::statusColors()],
    ];
@endphp

@section('content')
    @include('shared.announcements')

    @foreach($documents as $section => [$rows, $statusOf, $colors])
        @continue($rows->isEmpty())
        <div class="card card-table border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent"><h2 class="h6 mb-0">{{ __($section) }}</h2></div>
            <div class="table-responsive">
                <table class="table table-hover table-light-header align-middle mb-0">
                    <tbody>
                    @foreach($rows as $document)
                        @php($status = $statusOf($document))
                        <tr>
                            <td class="ps-3 w-100 w-md-auto">
                                <div class="text-truncate" style="width: 0; min-width: 100%;">
                                    <a href="{{ $document->publicUrl() }}" class="fw-medium">{{ $document->number }}</a>
                                    @if($document->subject)
                                        <span class="text-muted ms-2">{{ $document->subject }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $document->issue_date?->format('M d, Y') }}</td>
                            <td class="d-none d-md-table-cell text-end">{{ format_currency($document->total, $document->currency) }}</td>
                            <td class="pe-3 text-end"><span class="badge bg-{{ $colors[$status] ?? 'secondary' }}">{{ __($status) }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    @if($contracts->isNotEmpty())
        <div class="card card-table border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent"><h2 class="h6 mb-0">{{ __('contracts') }}</h2></div>
            <div class="table-responsive">
                <table class="table table-hover table-light-header align-middle mb-0">
                    <tbody>
                    @foreach($contracts as $contract)
                        <tr>
                            <td class="ps-3 w-100 w-md-auto">
                                <div class="text-truncate" style="width: 0; min-width: 100%;">
                                    <a href="{{ $contract->signingUrl() }}" class="fw-medium">{{ $contract->title }}</a>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $contract->end_date?->format('M d, Y') }}</td>
                            <td class="pe-3 text-end">
                                @if($contract->canBeSigned())
                                    <span class="badge bg-warning">{{ __('awaiting_signature') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __($contract->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($projects->isNotEmpty())
        <div class="card card-table border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent"><h2 class="h6 mb-0">{{ __('projects') }}</h2></div>
            <div class="table-responsive">
                <table class="table table-light-header align-middle mb-0">
                    <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td class="ps-3 w-100 w-md-auto">
                                <div class="text-truncate fw-medium" style="width: 0; min-width: 100%;">{{ $project->name }}</div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $project->due_date?->format('M d, Y') }}</td>
                            <td class="pe-3 text-end"><span class="badge bg-info">{{ \App\Models\Project::statuses()[$project->status] ?? $project->status }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card card-table border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h2 class="h6 mb-0">{{ __('tickets') }}</h2>
            <a href="{{ route('portal.tickets.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>{{ __('new_ticket') }}
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-light-header align-middle mb-0">
                <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td class="ps-3 w-100 w-md-auto">
                            <div class="text-truncate" style="width: 0; min-width: 100%;">
                                <a href="{{ route('portal.tickets.show', $ticket->id) }}" class="fw-medium">{{ $ticket->subject }}</a>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">{{ $ticket->updated_at->format('M d, Y') }}</td>
                        <td class="pe-3 text-end">
                            <span class="badge bg-{{ \App\Models\Ticket::statusColors()[$ticket->status] }}">{{ __($ticket->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td class="text-center text-muted py-4">{{ __('no_records_found') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
