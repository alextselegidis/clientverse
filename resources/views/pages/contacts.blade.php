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
    {{ __('contacts') }} - {{ $customer->name }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('customers'), 'url' => route('customers')],
        ['label' => $customer->name, 'url' => route('customers.show', $customer->id)],
        ['label' => __('contacts')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('customers.contacts.create', $customer->id) }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Search -->
            <form action="{{ route('customers.contacts', $customer->id) }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="q" name="q" class="form-control bg-light border-start-0"
                               value="{{ $q }}"
                               placeholder="{{ __('search') }}...">
                    </div>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                    <tr>
                        <th class="border-0 ps-3">{{ __('name') }}</th>
                        <th class="border-0">{{ __('email') }}</th>
                        <th class="border-0">{{ __('phone') }}</th>
                        <th class="border-0">{{ __('role') }}</th>
                        <th class="border-0">{{ __('primary') }}</th>
                        <th class="border-0 pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($contacts as $contact)
                        <tr onclick="window.location='{{ route('customers.contacts.show', [$customer->id, $contact->id]) }}'" style="cursor: pointer;">
                            <td class="ps-3">
                                <span class="fw-medium">{{ $contact->name }}</span>
                                @if($contact->position)
                                    <small class="text-muted d-block">{{ $contact->position }}</small>
                                @endif
                            </td>
                            <td>
                                @if($contact->email)
                                    <a href="mailto:{{ $contact->email }}" onclick="event.stopPropagation()">{{ $contact->email }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $contact->phone ?? '-' }}</td>
                            <td>
                                @php
                                    $roleColors = ['decision_maker' => 'primary', 'finance' => 'success', 'technical' => 'info', 'other' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $roleColors[$contact->role] ?? 'secondary' }}">
                                    {{ __($contact->role ?? 'other') }}
                                </span>
                            </td>
                            <td>
                                @if($contact->is_primary)
                                    <span class="badge bg-success">{{ __('yes') }}</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        {{ __('actions') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('customers.contacts.edit', [$customer->id, $contact->id]) }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('customers.contacts.destroy', [$customer->id, $contact->id]) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>{{ __('delete') }}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($contacts->isEmpty())
                        <tr>
                            <td colspan="6" class="border-0 text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                {{ __('no_records_found') }}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
