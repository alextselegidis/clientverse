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

<div class="bg-primary">
    <div class="container">
        <div class="row">
            <div class="col">
                <nav class="navbar navbar-expand-lg py-2 py-lg-0">
                    <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                        <img src="images/logo-light.svg" alt="Logo" class="me-2" style="height: 32px">
                        <strong class="fs-4 text-white">CLIENTVERSE</strong>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#top-nav"
                            aria-controls="top-nav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse ms-md-4 ms-lg-2 ms-xl-4" id="top-nav">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <!-- Dashboard -->
                            <li class="nav-item">
                                <a class="nav-link nav-menu-item text-white py-lg-4 px-lg-2 px-xl-4 {{ request()->routeIs('dashboard') ? 'fw-bold' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="bi bi-house me-2"></i>
                                    {{ __('dashboard') }}
                                </a>
                            </li>
                            <!-- Customers -->
                            <li class="nav-item">
                                <a class="nav-link nav-menu-item text-white py-lg-4 px-lg-2 px-xl-4 {{ request()->routeIs('customers*') ? 'fw-bold' : '' }}" href="{{ route('customers') }}">
                                    <i class="bi bi-people me-2"></i>
                                    {{ __('customers') }}
                                </a>
                            </li>
                            <!-- Projects -->
                            <li class="nav-item">
                                <a class="nav-link nav-menu-item text-white py-lg-4 px-lg-2 px-xl-4 {{ request()->routeIs('projects*') ? 'fw-bold' : '' }}" href="{{ route('projects') }}">
                                    <i class="bi bi-kanban me-2"></i>
                                    {{ __('projects') }}
                                </a>
                            </li>
                            <!-- Sales -->
                            <li class="nav-item">
                                <a class="nav-link nav-menu-item text-white py-lg-4 px-lg-2 px-xl-4 {{ request()->routeIs('sales*') ? 'fw-bold' : '' }}" href="{{ route('sales') }}">
                                    <i class="bi bi-graph-up me-2"></i>
                                    {{ __('sales') }}
                                </a>
                            </li>
                            <!-- Contracts -->
                            <li class="nav-item">
                                <a class="nav-link nav-menu-item text-white py-lg-4 px-lg-2 px-xl-4 {{ request()->routeIs('contracts*') ? 'fw-bold' : '' }}" href="{{ route('contracts') }}">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    {{ __('contracts') }}
                                </a>
                            </li>
                        </ul>
                        <!-- Global Search -->
                        <ul class="navbar-nav me-lg-2">
                            <li class="nav-item">
                                <button type="button" class="nav-link text-white" data-bs-toggle="modal"
                                        data-bs-target="#search-modal" aria-label="{{ __('search') }}">
                                    <i class="bi bi-search"></i>
                                    <span class="d-lg-none ms-2">{{ __('search') }}</span>
                                </button>
                            </li>
                        </ul>
                        <!-- Account Dropdown -->
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown"
                                   aria-expanded="false">
                                    <i class="bi bi-person me-1"></i>
                                    {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @if(Auth::user()->isAdmin())
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('setup.*') ? 'active' : '' }}" href="{{ route('setup.localization') }}">
                                                <i class="bi bi-gear me-2"></i>{{ __('setup') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                    @endif
                                    <li>
                                        <a class="dropdown-item" href="{{ route('account') }}">
                                            <i class="bi bi-person-circle me-2"></i>{{ __('account') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('about') }}">
                                            <i class="bi bi-info-circle me-2"></i>{{ __('about') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="https://clientverse.org/premium" target="_blank">
                                            <i class="bi bi-star me-2 text-warning"></i>{{ __('premium') }}
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('logout.perform') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-box-arrow-right me-2"></i>{{ __('logout') }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Global Search Modal -->
<div class="modal fade" tabindex="-1" id="search-modal" aria-label="{{ __('search') }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('search') }}" method="GET" class="modal-body">
                <div class="input-group input-group-lg">
                    <span class="input-group-text border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="search" name="q" class="form-control border-start-0"
                           value="{{ request('q') }}"
                           aria-label="{{ __('search') }}"
                           placeholder="{{ __('search') }}...">
                    <button type="submit" class="btn btn-primary">
                        {{ __('search') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
