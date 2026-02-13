@extends('layouts.main-layout')

@section('pageTitle')
    {{__('releases')}}
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
            <form action="{{route('releases')}}" method="GET" class="mb-3">
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
                            <a href="{{ route('releases', ['sort' => 'id', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('id', __('id')) !!}
                            </a>
                        </th>
                        <th style="width: 30%">
                            <a href="{{ route('releases', ['sort' => 'project', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('project', __('project')) !!}
                            </a>
                        </th>
                        <th style="width: 30%">
                            <a href="{{ route('releases', ['sort' => 'version', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('version', __('version')) !!}
                            </a>
                        </th>
                        <th style="width: 30%">
                            <a href="{{ route('releases', ['sort' => 'environment', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('environment', __('environment')) !!}
                            </a>
                        </th>
                        <th style="width: 40%">
                            <a href="{{ route('releases', ['sort' => 'released_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}">
                                {!! sort_link('released_at', __('released_at')) !!}
                            </a>
                        </th>
                        <th style="width: 20%">
                            <!-- Actions -->
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($releases as $release)
                        <tr onclick="window.location='{{route('releases.show', $release->id)}}'" style="cursor: pointer;">
                            <td>
                                @include('shared.id-value', ['value' => $release->id])
                            </td>
                            <td>
                                {{$release->project?->name ?? '-'}}
                            </td>
                            <td>
                                {{$release->version}}
                            </td>
                            <td>
                                {{$release->environment}}
                            </td>
                            <td>
                                @include('shared.date-time-value', ['value' => $release->released_at])
                            </td>
                            <td class="text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-link text-decoration-none dropdown-toggle py-0" type="button"
                                            data-bs-toggle="dropdown">
                                        {{__('actions')}}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{route('releases.edit', ['release' => $release->id])}}"
                                               class="dropdown-item">
                                                {{__('edit')}}
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{route('releases.destroy', $release->id)}}"
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

    @include('modals.create-modal', ['route' => route('releases.store'), 'input_name' => 'version'])
@endsection
