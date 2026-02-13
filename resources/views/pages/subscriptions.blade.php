@extends('layouts.main-layout')

@section('pageTitle')
    {{__('Subscriptions')}}
@endsection

@section('navActions')
    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#create-modal">
        <i class="bi bi-plus-square me-2"></i>
        {{__('create')}}
    </a>
@endsection

@section('content')
    <div class="d-flex flex-column-reverse flex-lg-row">
        <div class="flex-grow-0 sidebar-width">
            @include('shared.settings-sidebar')
        </div>

        <div class="flex-grow-1 mb-5 mb-lg-0">
            <form action="{{route('subscriptions')}}" method="GET" class="mb-3">
                @csrf
                @method('GET')
                <div class="input-group mb-3">
                    <span class="bg-body-tertiary input-group-text px-3">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="q" name="q" class="form-control bg-body-tertiary border-start-0"
                           value="{{$q}}"
                           placeholder="{{__('search')}}" autofocus tabindex="-1" style="max-width: 300px;">
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th style="width: 10%">
                            <a href="{{ route('subscriptions', ['sort' => 'id', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('id', __('id')) !!}
                            </a>
                        </th>
                        <th style="width: 50%">
                            <a href="{{ route('subscriptions', ['sort' => 'plan', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('plan', __('plan')) !!}
                            </a>
                        </th>
                        <th style="width: 50%">
                            <a href="{{ route('subscriptions', ['sort' => 'customer', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('customer', __('customer')) !!}
                            </a>
                        </th>
                        <th style="width: 25%">
                            <a href="{{ route('subscriptions', ['sort' => 'start_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('start_date', __('start_date')) !!}
                            </a>
                        </th>
                        <th style="width: 25%"
                            <a href="{{ route('subscriptions', ['sort' => 'end_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('end_date', __('end_date')) !!}
                            </a>
                        </th>
                        <th style="width: 25%">
                            <a href="{{ route('subscriptions', ['sort' => 'is_active', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('is_active', __('active')) !!}
                            </a>
                        </th>
                        <th style="width: 15%">
                            <!-- Actions -->
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($subscriptions as $subscription)
                        <tr onclick="window.location='{{route('subscriptions.show', $subscription->id)}}'" style="cursor: pointer;">
                            <td>
                                @include('shared.id-value', ['value' => $subscription->id])
                            </td>
                            <td>
                                {{$subscription->plan}}
                            </td>
                            <td>
                                {{$subscription->customer?->display_name ?? '-'}}
                            </td>
                            <td>
                                @include('shared.date-value', ['value' => $subscription->start_date])
                            </td>
                            <td>
                                @include('shared.date-value', ['value' => $subscription->end_date])
                            </td>
                            <td>
                                @include('shared.bool-value', ['value' => $subscription->is_active])
                            </td>
                            <td class="text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-link text-decoration-none dropdown-toggle py-0" type="button"
                                            data-bs-toggle="dropdown">
                                        {{__('actions')}}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{route('subscriptions.edit', ['subscription' => $subscription->id])}}"
                                               class="dropdown-item">
                                                {{__('edit')}}
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{route('subscriptions.destroy', $subscription->id)}}"
                                                  method="POST"
                                                  onsubmit="return confirm('{{__('delete_record_prompt')}}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item">
                                                    {{__('delete')}}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('modals.create-modal', ['route' => route('subscriptions.store'), 'input_name' => 'plan'])
@endsection
