@extends('layouts.main-layout')

@section('pageTitle')
    {{__('releases')}}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('settings'), 'url' => route('releases')],
        ['label' => __('releases')]
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
            <h5 class="fw-bold mb-3">{{ __('releases') }}</h5>
            <!-- Search -->
            <form action="{{route('releases')}}" method="GET" class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="q" name="q" class="form-control bg-light border-start-0"
                           value="{{$q}}"
                           placeholder="{{__('search')}}..." style="max-width: 300px;">
                </div>
            </form>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <!-- Table -->
                    <div class="table-responsive" style="overflow: visible;">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="border-0 ps-4">
                                        <a href="{{ route('releases', ['sort' => 'id', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none text-white">
                                            {{ __('id') }}
                                            @if(request('sort') === 'id')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="border-0">
                                        <a href="{{ route('releases', ['sort' => 'project', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none text-white">
                                            {{ __('project') }}
                                            @if(request('sort') === 'project')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="border-0">
                                        <a href="{{ route('releases', ['sort' => 'version', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none text-white">
                                            {{ __('version') }}
                                            @if(request('sort') === 'version')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="border-0">
                                        <a href="{{ route('releases', ['sort' => 'environment', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none text-white">
                                            {{ __('environment') }}
                                            @if(request('sort') === 'environment')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="border-0">
                                        <a href="{{ route('releases', ['sort' => 'released_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none text-white">
                                            {{ __('released_at') }}
                                            @if(request('sort') === 'released_at')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="border-0 pe-4 text-end" style="width: 100px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($releases as $release)
                                <tr onclick="window.location='{{route('releases.show', $release->id)}}'" style="cursor: pointer;">
                                    <td class="border-0 ps-4">
                                        <span class="fw-medium">{{ $release->id }}</span>
                                    </td>
                                    <td class="border-0">
                                        {{$release->project?->name ?? '-'}}
                                    </td>
                                    <td class="border-0">
                                        {{$release->version}}
                                    </td>
                                    <td class="border-0">
                                        {{$release->environment}}
                                    </td>
                                    <td class="border-0">
                                        {{ $release->released_at?->format('M d, Y H:i') ?? '-' }}
                                    </td>
                                    <td class="border-0 pe-4 text-end">
                                        <div class="dropdown" onclick="event.stopPropagation();">
                                            <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                {{__('actions')}}
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a href="{{route('releases.edit', ['release' => $release->id])}}" class="dropdown-item">
                                                        <i class="bi bi-pencil me-2"></i>{{__('edit')}}
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{route('releases.destroy', $release->id)}}"
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
                            @if($releases->isEmpty())
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
        </div>
    </div>

    @include('modals.create-modal', ['route' => route('releases.store'), 'input_name' => 'version'])
@endsection
