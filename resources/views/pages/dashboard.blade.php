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
    {{ __('dashboard') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('dashboard')]
    ]])
@endsection

@section('content')
    <!-- Overview Widgets -->
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-people text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $totalCustomers }}</h3>
                            <small class="text-muted">{{ __('total_customers') }}</small>
                        </div>
                    </div>
                </div>
                <a href="{{ route('customers') }}" class="card-footer bg-transparent border-0 text-decoration-none">
                    <small>{{ __('view') }} <i class="bi bi-arrow-right"></i></small>
                </a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-kanban text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $activeProjects }}</h3>
                            <small class="text-muted">{{ __('active_projects') }}</small>
                        </div>
                    </div>
                </div>
                <a href="{{ route('projects', ['status' => 'active']) }}" class="card-footer bg-transparent border-0 text-decoration-none">
                    <small>{{ __('view') }} <i class="bi bi-arrow-right"></i></small>
                </a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-graph-up text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $openDeals }}</h3>
                            <small class="text-muted">{{ __('open_deals') }}</small>
                            @if($openDealsValue > 0)
                                <div><small class="text-success">{{ format_currency($openDealsValue) }}</small></div>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('sales') }}" class="card-footer bg-transparent border-0 text-decoration-none">
                    <small>{{ __('view') }} <i class="bi bi-arrow-right"></i></small>
                </a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-file-earmark-text text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ $activeContracts }}</h3>
                            <small class="text-muted">{{ __('active_contracts') }}</small>
                        </div>
                    </div>
                </div>
                <a href="{{ route('contracts', ['status' => 'active']) }}" class="card-footer bg-transparent border-0 text-decoration-none">
                    <small>{{ __('view') }} <i class="bi bi-arrow-right"></i></small>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>{{ __('quick_actions') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row row-cols-2 row-cols-sm-auto g-2">
                        <div class="col d-grid">
                            <a href="{{ route('customers.create') }}" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-1"></i> {{ __('add') }} {{ __('customer') }}
                            </a>
                        </div>
                        <div class="col d-grid">
                            <a href="{{ route('projects.create') }}" class="btn btn-outline-success">
                                <i class="bi bi-folder-plus me-1"></i> {{ __('create') }} {{ __('project') }}
                            </a>
                        </div>
                        <div class="col d-grid">
                            <a href="{{ route('sales.create') }}" class="btn btn-outline-warning">
                                <i class="bi bi-plus-circle me-1"></i> {{ __('new') }} {{ __('sale') }}
                            </a>
                        </div>
                        <div class="col d-grid">
                            <a href="{{ route('contracts.create') }}" class="btn btn-outline-info">
                                <i class="bi bi-file-plus me-1"></i> {{ __('new') }} {{ __('contract') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>{{ __('recent_activity') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($recentCustomers as $customer)
                            <a href="{{ route('customers.show', $customer->id) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person-plus text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium text-truncate">{{ $customer->name }}</div>
                                    <small class="text-muted">{{ __('new') }} {{ __('customer') }}</small>
                                </div>
                                <small class="text-muted text-nowrap flex-shrink-0 ms-3">{{ $customer->created_at->diffForHumans() }}</small>
                            </a>
                        @endforeach
                        @foreach($recentProjects as $project)
                            <a href="{{ route('projects.show', $project->id) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-kanban text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium text-truncate">{{ $project->name }}</div>
                                    <small class="text-muted">{{ __('project') }} {{ __('updated') }}</small>
                                </div>
                                <small class="text-muted text-nowrap flex-shrink-0 ms-3">{{ $project->updated_at->diffForHumans() }}</small>
                            </a>
                        @endforeach
                        @if($recentCustomers->isEmpty() && $recentProjects->isEmpty())
                            <div class="list-group-item text-center text-muted py-4">
                                {{ __('no_records_found') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>{{ __('monthly_sales') }}</h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-primary mb-0">{{ format_currency($monthlySales) }}</h2>
                    <small class="text-muted">{{ now()->format('F Y') }}</small>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-check2-circle me-2"></i>{{ __('project_completion_rate') }}</h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-primary mb-0">{{ $projectCompletionRate }}%</h2>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $projectCompletionRate }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
