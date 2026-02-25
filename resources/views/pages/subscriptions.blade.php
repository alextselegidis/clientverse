@extends('layouts.main-layout')

@section('pageTitle')
    {{__('subscriptions')}}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('settings'), 'url' => route('subscriptions')],
        ['label' => __('subscriptions')]
    ]])
@endsection

@section('navActions')
    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#create-modal">
        <i class="bi bi-plus-square me-2"></i>
        {{__('create')}}
    </a>
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <!-- Sidebar -->
        <div class="flex-shrink-0" style="min-width: 200px;">
            @include('shared.settings-sidebar')
        </div>
        <!-- Main Content -->
        <div class="flex-grow-1">
            <h5 class="fw-bold mb-3">{{ __('subscriptions') }}</h5>
            <div class="card card-table border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <!-- Search -->
                    <div class="table-filters">
                        <form action="{{route('subscriptions')}}" method="GET">
                            <div class="input-group">
                                <span class="input-group-text border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="q" name="q" class="form-control border-start-0"
                                       value="{{$q}}"
                                       placeholder="{{__('search')}}..." style="max-width: 300px;">
                            </div>
                        </form>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive" style="overflow: visible;">
                        <table class="table table-striped table-hover table-light-header align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">
                                        <a href="{{ route('subscriptions', ['sort' => 'id', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none">
                                            {{ __('id') }}
                                            @if(request('sort') === 'id')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('subscriptions', ['sort' => 'plan', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none">
                                            {{ __('plan') }}
                                            @if(request('sort') === 'plan')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('subscriptions', ['sort' => 'customer', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none">
                                            {{ __('customer') }}
                                            @if(request('sort') === 'customer')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('subscriptions', ['sort' => 'start_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none">
                                            {{ __('start_date') }}
                                            @if(request('sort') === 'start_date')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('subscriptions', ['sort' => 'end_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none">
                                            {{ __('end_date') }}
                                            @if(request('sort') === 'end_date')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('subscriptions', ['sort' => 'is_active', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none">
                                            {{ __('active') }}
                                            @if(request('sort') === 'is_active')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="pe-4 text-end" style="width: 100px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($subscriptions as $subscription)
                                <tr onclick="window.location='{{route('subscriptions.show', $subscription->id)}}'" style="cursor: pointer;">
                                    <td class="ps-4">
                                        <span class="fw-medium">{{ $subscription->id }}</span>
                                    </td>
                                    <td>
                                        {{$subscription->plan}}
                                    </td>
                                    <td>
                                        {{$subscription->customer?->display_name ?? '-'}}
                                    </td>
                                    <td>
                                        {{ $subscription->start_date?->format('M d, Y') ?? '-' }}
                                    </td>
                                    <td>
                                        {{ $subscription->end_date?->format('M d, Y') ?? '-' }}
                                    </td>
                                    <td>
                                        @if($subscription->is_active)
                                            <span class="badge bg-success-subtle text-success">{{ __('yes') }}</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">{{ __('no') }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="dropdown" onclick="event.stopPropagation();">
                                            <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                {{__('actions')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a href="{{route('subscriptions.edit', ['subscription' => $subscription->id])}}" class="dropdown-item">
                                                        <i class="bi bi-pencil me-2"></i>{{__('edit')}}
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{route('subscriptions.destroy', $subscription->id)}}"
                                                          method="POST"
                                                          onsubmit="return confirm('{{__('delete_record_prompt')}}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i>{{__('delete')}}
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if($subscriptions->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                        {{ __('no_records_found') }}
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @include('shared.pagination', ['paginator' => $subscriptions])
                </div>
            </div>
        </div>
    </div>

    @include('modals.create-modal', ['route' => route('subscriptions.store'), 'input_name' => 'plan'])
@endsection
