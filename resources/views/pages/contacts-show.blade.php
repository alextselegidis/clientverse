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
    {{ $contact->name }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('customers'), 'url' => route('customers')],
        ['label' => $customer->name, 'url' => route('customers.show', $customer->id)],
        ['label' => __('contacts'), 'url' => route('customers.contacts', $customer->id)],
        ['label' => $contact->name]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('customers.contacts.edit', [$customer->id, $contact->id]) }}" class="nav-link me-3">
        <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
    </a>
    <form action="{{ route('customers.contacts.destroy', [$customer->id, $contact->id]) }}" method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link text-danger">
            <i class="bi bi-trash me-2"></i>{{ __('delete') }}
        </button>
    </form>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h4 class="mb-1">{{ $contact->name }}</h4>
                            @if($contact->position)
                                <span class="text-muted">{{ $contact->position }}</span>
                            @endif
                        </div>
                        <div>
                            @if($contact->is_primary)
                                <span class="badge bg-success">{{ __('primary') }}</span>
                            @endif
                            @if($contact->has_portal_access)
                                <span class="badge bg-info">{{ __('portal_access') }}</span>
                            @endif
                            @php
                                $roleColors = ['decision_maker' => 'primary', 'finance' => 'success', 'technical' => 'info', 'other' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $roleColors[$contact->role] ?? 'secondary' }}">
                                {{ __($contact->role ?? 'other') }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('email') }}</small>
                            @if($contact->email)
                                <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                            @else
                                <span>-</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('phone') }}</small>
                            @if($contact->phone)
                                <a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>
                            @else
                                <span>-</span>
                            @endif
                        </div>
                    </div>

                    @if($contact->notes)
                        <div>
                            <h6 class="text-muted mb-2">{{ __('notes') }}</h6>
                            <p class="mb-0">{!! nl2br(e($contact->notes)) !!}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Details -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">{{ __('details') }}</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">{{ __('customer') }}</small>
                    <p class="mb-3">
                        <a href="{{ route('customers.show', $customer->id) }}">{{ $customer->name }}</a>
                    </p>

                    <small class="text-muted">{{ __('created') }}</small>
                    <p class="mb-3">{{ $contact->created_at->format('M d, Y H:i') }}</p>

                    <small class="text-muted">{{ __('updated') }}</small>
                    <p class="mb-0">{{ $contact->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
