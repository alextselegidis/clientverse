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
{{-- A portal ticket: the conversation and a reply box, or ($ticket = null) the form opening a new one. --}}
@extends('layouts.portal-layout')

@section('pageTitle')
    {{ $ticket?->subject ?? __('new_ticket') }}
@endsection

@section('content')
    <a href="{{ route('portal') }}" class="d-inline-block mb-3"><i class="bi bi-arrow-left me-1"></i>{{ __('back') }}</a>

    @if($ticket)
        <div class="d-flex align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0 text-truncate">{{ $ticket->subject }}</h1>
            <span class="badge bg-{{ \App\Models\Ticket::statusColors()[$ticket->status] }}">{{ __($ticket->status) }}</span>
        </div>

        @foreach($ticket->replies as $reply)
            <div class="card border-0 shadow-sm mb-3 {{ $reply->user_id ? '' : 'border-start border-primary border-3' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span class="fw-medium text-body">{{ $reply->user?->name ?? $reply->contact?->full_name ?? '-' }}</span>
                        <span>{{ $reply->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div>{!! nl2br(e($reply->body)) !!}</div>
                </div>
            </div>
        @endforeach
    @else
        <h1 class="h4 mb-3">{{ __('new_ticket') }}</h1>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ $ticket ? route('portal.tickets.replies.store', $ticket->id) : route('portal.tickets.store') }}" method="POST">
                @csrf
                @unless($ticket)
                    <div class="mb-3">
                        <label for="subject" class="form-label">{{ __('subject') }} <span class="text-danger">*</span></label>
                        <input type="text" id="subject" name="subject" class="form-control" maxlength="255" required value="{{ old('subject') }}">
                    </div>
                    @include('shared.department-select')
                @endunless
                <div class="mb-3">
                    <label for="body" class="form-label">{{ $ticket ? __('reply') : __('message') }} <span class="text-danger">*</span></label>
                    <textarea id="body" name="body" class="form-control" rows="5" required>{{ old('body') }}</textarea>
                </div>
                @include('shared.errors', ['class' => 'mb-3'])
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">{{ $ticket ? __('reply') : __('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
