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
@extends('layouts.main-layout')

@section('pageTitle')
    #{{ $ticket->id }} {{ $ticket->subject }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('tickets'), 'url' => route('tickets')],
        ['label' => '#' . $ticket->id]
    ]])
@endsection

@section('navActions')
    <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>
            {{ __('delete') }}
        </button>
    </form>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex align-items-center gap-2">
                    <h5 class="mb-0 text-truncate">{{ __('replies') }}</h5>
                    <span class="badge bg-{{ \App\Models\Ticket::statusColors()[$ticket->status] }}">{{ __($ticket->status) }}</span>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($ticket->replies as $reply)
                        <li class="list-group-item py-3">
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span class="fw-medium text-body">{{ $reply->user?->name ?? $reply->contact?->full_name ?? $ticket->customer?->name }}</span>
                                <span>{{ $reply->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <div>{!! nl2br(e($reply->body)) !!}</div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('tickets.replies.store', $ticket->id) }}" method="POST" id="reply-form">
                        @csrf
                        <label for="body" class="form-label">{{ __('reply') }}</label>
                        <textarea id="body" name="body" class="form-control" rows="5" required>{{ old('body') }}</textarea>
                        @error('body')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                            <div class="form-check">
                                <input type="hidden" name="notify" value="0">
                                <input type="checkbox" id="notify" name="notify" value="1" class="form-check-input"
                                       @checked(old('notify', true)) @disabled(!$ticket->recipient_email)>
                                <label for="notify" class="form-check-label">
                                    {{ __('email_the_customer') }}
                                    @if($ticket->recipient_email)
                                        <span class="text-muted">({{ $ticket->recipient_email }})</span>
                                    @else
                                        <span class="text-muted">({{ __('no_email_on_file') }})</span>
                                    @endif
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('reply') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="mb-3">
                        <strong>{{ __('customer') }}:</strong>
                        @if($ticket->customer)
                            <a href="{{ route('customers.show', $ticket->customer_id) }}">{{ $ticket->customer->name }}</a>
                        @else
                            -
                        @endif
                    </p>
                    <form action="{{ route('tickets.update', $ticket->id) }}" method="POST" id="ticket-form">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="status" class="form-label">{{ __('status') }}</label>
                            <select id="status" name="status" class="form-select">
                                @foreach(\App\Models\Ticket::statuses() as $key => $label)
                                    <option value="{{ $key }}" @selected($ticket->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="priority" class="form-label">{{ __('priority') }}</label>
                            <select id="priority" name="priority" class="form-select">
                                @foreach(\App\Models\Task::priorities() as $key => $label)
                                    <option value="{{ $key }}" @selected($ticket->priority === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @include('shared.department-select', ['selected' => $ticket->department_id])
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">{{ __('assigned_to') }}</label>
                            <select id="assigned_to" name="assigned_to" class="form-select">
                                <option value="">-</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected($ticket->assigned_to == $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-light border w-100">{{ __('save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
