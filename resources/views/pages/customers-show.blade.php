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
    {{ $customer->name }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('customers'), 'url' => route('customers')],
        ['label' => $customer->name]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('customers.create') }}" class="nav-link me-lg-3">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('add') }}
    </a>
    <a href="{{ route('customers.edit', $customer->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{ __('edit') }}
    </a>
    <form action="{{ route('customers.destroy', $customer->id) }}"
          method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')"
          class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>
            {{ __('delete') }}
        </button>
    </form>
@endsection

@section('content')
    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex align-items-center">
                    <h5 class="mb-0">{{ $customer->name }}</h5>
                    @php
                        $statusColors = ['lead' => 'warning', 'active' => 'success', 'inactive' => 'secondary'];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$customer->status] ?? 'secondary' }} ms-2">
                        {{ __($customer->status) }}
                    </span>
                    <span class="badge bg-light text-dark ms-2">{{ __($customer->type) }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if($customer->company)
                                <p class="mb-2"><i class="bi bi-building me-2 text-muted"></i>{{ $customer->company }}</p>
                            @endif
                            @if($customer->email)
                                <p class="mb-2"><i class="bi bi-envelope me-2 text-muted"></i><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></p>
                            @endif
                            @if($customer->phone)
                                <p class="mb-2"><i class="bi bi-telephone me-2 text-muted"></i><a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a></p>
                            @endif
                            @if($customer->website)
                                <p class="mb-2"><i class="bi bi-globe me-2 text-muted"></i><a href="{{ $customer->website }}" target="_blank">{{ $customer->website }}</a></p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($customer->address)
                                <p class="mb-2"><i class="bi bi-geo-alt me-2 text-muted"></i>{{ $customer->address }}</p>
                            @endif
                            @if($customer->vat_id)
                                <p class="mb-2"><i class="bi bi-credit-card me-2 text-muted"></i>{{ __('vat_id') }}: {{ $customer->vat_id }}</p>
                            @endif
                            @if($customer->currency)
                                <p class="mb-2"><i class="bi bi-currency-exchange me-2 text-muted"></i>{{ __('currency') }}: {{ $customer->currency }}</p>
                            @endif
                        @if($customer->billing_address)
                                <p class="mb-2"><i class="bi bi-receipt me-2 text-muted"></i>{{ __('billing_address') }}: {{ $customer->billing_address }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-muted small">
                    {{ __('created') }}: {{ $customer->created_at->format('M d, Y') }} |
                    {{ __('updated') }}: {{ $customer->updated_at->format('M d, Y') }}
                </div>
            </div>

            <!-- Contacts -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-people me-2"></i>{{ __('contacts') }}</h6>
                    <a href="{{ route('customers.contacts.create', $customer->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus"></i> {{ __('add') }}
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($customer->contacts->count())
                        <div class="list-group list-group-flush">
                            @foreach($customer->contacts as $contact)
                                <a href="{{ route('customers.contacts.show', [$customer->id, $contact->id]) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $contact->full_name }}</strong>
                                            @if($contact->is_primary)
                                                <span class="badge bg-primary ms-2">{{ __('is_primary') }}</span>
                                            @endif
                                            @if($contact->position)
                                                <small class="text-muted d-block">{{ $contact->position }}</small>
                                            @endif
                                        </div>
                                        <div class="text-muted small">
                                            {{ $contact->email }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">{{ __('no_records_found') }}</div>
                    @endif
                </div>
            </div>

            <!-- Notes Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>{{ __('notes') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#add-note-form">
                        <i class="bi bi-plus"></i> {{ __('add') }}
                    </button>
                </div>
                <div class="card-body">
                    <!-- Add Note Form -->
                    <div class="collapse mb-3" id="add-note-form">
                        <form action="{{ route('customers.notes.store', $customer->id) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <textarea name="content" class="form-control" rows="3" placeholder="{{ __('enter_note') }}..." required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="collapse" data-bs-target="#add-note-form">{{ __('cancel') }}</button>
                                <button type="submit" class="btn btn-sm btn-primary">{{ __('save') }}</button>
                            </div>
                        </form>
                    </div>

                    @if($customer->customerNotes->count())
                        @foreach($customer->customerNotes as $note)
                            <div class="border-bottom pb-3 mb-3" id="note-{{ $note->id }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">
                                        {{ $note->user?->name ?? __('unknown') }} &middot; {{ $note->created_at->format('M d, Y H:i') }}
                                    </small>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('edit-note-{{ $note->id }}').classList.toggle('d-none'); document.getElementById('view-note-{{ $note->id }}').classList.toggle('d-none');">
                                                    <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('customers.notes.destroy', [$customer->id, $note->id]) }}" method="POST" onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>{{ __('delete') }}
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div id="view-note-{{ $note->id }}">
                                    <p class="mb-0">{!! nl2br(e($note->content)) !!}</p>
                                </div>
                                <div id="edit-note-{{ $note->id }}" class="d-none">
                                    <form action="{{ route('customers.notes.update', [$customer->id, $note->id]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-2">
                                            <textarea name="content" class="form-control" rows="3" required>{{ $note->content }}</textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('edit-note-{{ $note->id }}').classList.add('d-none'); document.getElementById('view-note-{{ $note->id }}').classList.remove('d-none');">{{ __('cancel') }}</button>
                                            <button type="submit" class="btn btn-sm btn-primary">{{ __('save') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-3">{{ __('no_notes_yet') }}</div>
                    @endif
                </div>
            </div>

            @include('shared.files-section', [
                'model' => $customer,
                'uploadRoute' => route('customers.files.upload', $customer->id),
                'downloadRoute' => 'customers.files.download',
                'deleteRoute' => 'customers.files.delete',
                'routeParams' => ['customer' => $customer->id],
                'uploadLimits' => $uploadLimits,
            ])
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Projects -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-kanban me-2"></i>{{ __('projects') }}</h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">{{ $customer->projects->count() }}</span>
                        <a href="{{ route('projects.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus"></i> {{ __('add') }}
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($customer->projects->count())
                        <div class="list-group list-group-flush">
                            @foreach($customer->projects->take(5) as $project)
                                <a href="{{ route('projects.show', $project->id) }}" class="list-group-item list-group-item-action">
                                    {{ $project->name }}
                                    <span class="badge bg-{{ $project->status == 'completed' ? 'success' : ($project->status == 'active' ? 'primary' : 'secondary') }} float-end">
                                        {{ __($project->status) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">{{ __('no_records_found') }}</div>
                    @endif
                </div>
            </div>

            <!-- Sales -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>{{ __('sales') }}</h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning">{{ $customer->sales->count() }}</span>
                        <a href="{{ route('sales.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus"></i> {{ __('add') }}
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($customer->sales->count())
                        <div class="list-group list-group-flush">
                            @foreach($customer->sales->take(5) as $sale)
                                <a href="{{ route('sales.show', $sale->id) }}" class="list-group-item list-group-item-action">
                                    {{ $sale->name }}
                                    <span class="badge bg-secondary float-end">{{ __($sale->stage) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">{{ __('no_records_found') }}</div>
                    @endif
                </div>
            </div>

            <!-- Contracts -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>{{ __('contracts') }}</h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-info">{{ $customer->contracts->count() }}</span>
                        <a href="{{ route('contracts.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus"></i> {{ __('add') }}
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($customer->contracts->count())
                        <div class="list-group list-group-flush">
                            @foreach($customer->contracts->take(5) as $contract)
                                <a href="{{ route('contracts.show', $contract->id) }}" class="list-group-item list-group-item-action">
                                    {{ $contract->title }}
                                    <span class="badge bg-{{ $contract->status == 'active' ? 'success' : 'secondary' }} float-end">
                                        {{ __($contract->status) }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">{{ __('no_records_found') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
