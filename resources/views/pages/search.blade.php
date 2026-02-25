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
    {{ __('search') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('search')]
    ]])
@endsection

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('search') }}" method="GET" class="mb-4">
                <div class="input-group input-group-lg">
                    <span class="input-group-text border-end-0 bg-white">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0"
                           value="{{ $q }}"
                           placeholder="{{ __('search') }}..."
                           autofocus>
                    <button type="submit" class="btn btn-primary">{{ __('search') }}</button>
                </div>
            </form>

            @if($q && strlen($q) >= 2)
                <div class="text-muted mb-4">
                    {{ __('found') }} {{ $totalResults }} {{ __('results_for') }} "{{ $q }}"
                </div>

                @if($totalResults === 0)
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-search display-4 d-block mb-3"></i>
                        {{ __('no_records_found') }}
                    </div>
                @else
                    {{-- Customers --}}
                    @if(!empty($results['customers']) && $results['customers']->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="bi bi-people me-2 text-primary"></i>
                                {{ __('customers') }}
                                <span class="badge bg-secondary ms-2">{{ $results['customers']->count() }}</span>
                            </h5>
                            <div class="list-group">
                                @foreach($results['customers'] as $customer)
                                    <a href="{{ route('customers.show', $customer->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $customer->name }}</strong>
                                            @if($customer->company)
                                                <span class="text-muted">@ {{ $customer->company }}</span>
                                            @endif
                                            @if($customer->email)
                                                <br><small class="text-muted">{{ $customer->email }}</small>
                                            @endif
                                        </div>
                                        <span class="badge bg-{{ $customer->status === 'active' ? 'success' : ($customer->status === 'lead' ? 'warning' : 'secondary') }}">
                                            {{ __($customer->status) }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Projects --}}
                    @if(!empty($results['projects']) && $results['projects']->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="bi bi-kanban me-2 text-primary"></i>
                                {{ __('projects') }}
                                <span class="badge bg-secondary ms-2">{{ $results['projects']->count() }}</span>
                            </h5>
                            <div class="list-group">
                                @foreach($results['projects'] as $project)
                                    <a href="{{ route('projects.show', $project->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $project->name }}</strong>
                                            @if($project->description)
                                                <br><small class="text-muted">{{ Str::limit($project->description, 100) }}</small>
                                            @endif
                                        </div>
                                        @php
                                            $statusColors = ['planned' => 'secondary', 'active' => 'primary', 'on_hold' => 'warning', 'completed' => 'success'];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">
                                            {{ __($project->status) }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Sales --}}
                    @if(!empty($results['sales']) && $results['sales']->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="bi bi-graph-up me-2 text-primary"></i>
                                {{ __('sales') }}
                                <span class="badge bg-secondary ms-2">{{ $results['sales']->count() }}</span>
                            </h5>
                            <div class="list-group">
                                @foreach($results['sales'] as $sale)
                                    <a href="{{ route('sales.show', $sale->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $sale->name }}</strong>
                                            @if($sale->value)
                                                <span class="text-muted ms-2">{{ format_currency($sale->value, $sale->currency) }}</span>
                                            @endif
                                        </div>
                                        @php
                                            $stageColors = ['lead' => 'secondary', 'qualified' => 'info', 'proposal_sent' => 'warning', 'won' => 'success', 'lost' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $stageColors[$sale->stage] ?? 'secondary' }}">
                                            {{ __($sale->stage) }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Contracts --}}
                    @if(!empty($results['contracts']) && $results['contracts']->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>
                                {{ __('contracts') }}
                                <span class="badge bg-secondary ms-2">{{ $results['contracts']->count() }}</span>
                            </h5>
                            <div class="list-group">
                                @foreach($results['contracts'] as $contract)
                                    <a href="{{ route('contracts.show', $contract->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $contract->title }}</strong>
                                            @if($contract->value)
                                                <span class="text-muted ms-2">{{ format_currency($contract->value, $contract->currency) }}</span>
                                            @endif
                                        </div>
                                        @php
                                            $statusColors = ['draft' => 'secondary', 'active' => 'success', 'expired' => 'warning', 'cancelled' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$contract->status] ?? 'secondary' }}">
                                            {{ __($contract->status) }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Users (Admin only) --}}
                    @if(!empty($results['users']) && $results['users']->count() > 0)
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="bi bi-person-gear me-2 text-primary"></i>
                                {{ __('users') }}
                                <span class="badge bg-secondary ms-2">{{ $results['users']->count() }}</span>
                            </h5>
                            <div class="list-group">
                                @foreach($results['users'] as $user)
                                    <a href="{{ route('setup.users.edit', $user->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            <br><small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark me-2">{{ __($user->role) }}</span>
                                            @if($user->is_active)
                                                <span class="badge bg-success-subtle text-success">{{ __('active') }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">{{ __('inactive') }}</span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @elseif($q && strlen($q) < 2)
                <div class="text-center text-muted py-5">
                    <i class="bi bi-info-circle display-4 d-block mb-3"></i>
                    {{ __('enter_at_least_2_characters') }}
                </div>
            @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-search display-4 d-block mb-3"></i>
                    {{ __('enter_search_term') }}
                </div>
            @endif
        </div>
    </div>
@endsection
