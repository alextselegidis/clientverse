{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Powerful CRM System
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
    {{$user->name}}
@endsection

@section('navTitle')
    {{__('view_user')}}
@endsection

@section('navActions')
    <a href="#" class="nav-link me-lg-3" data-bs-toggle="modal" data-bs-target="#create-modal">
        <i class="bi bi-plus-square me-2"></i>
        {{__('add')}}
    </a>
    <a href="{{ route('setup.users.edit', ['user' => $user->id]) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{__('edit')}}
    </a>

    <form action="{{route('setup.users.destroy', $user->id)}}"
          method="POST"
          onsubmit="return confirm('{{__('delete_record_prompt')}}')">
        @csrf
        @method('DELETE')

        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>
            {{__('delete')}}
        </button>
    </form>
@endsection

@section('content')

    <div class="d-flex">

        <div class="flex-grow-0 sidebar-width">
            @include('shared.settings-sidebar')
        </div>

        <div class="flex-grow-1">

            @include('shared.show-title', ['title' => $user->name])

            <div class="d-lg-flex">
                <div class="w-100">
                    @include('shared.show-id', ['label' => __('id'), 'value' => $user->id])
                    @include('shared.show-link', ['label' => __('email'), 'href' => 'mailto:' . $user->email, 'value' => $user->email])
                </div>
                <div class="w-100">
                    @include('shared.show-date', ['label' => __('created'), 'value' => $user->created_at])
                    @include('shared.show-bool', ['label' => __('active'), 'value' => $user->is_active])
                </div>
            </div>

        </div>
    </div>

    @include('modals.create-modal', ['route' => route('users.store')])

@endsection

