<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}">NetPrime</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('admin.dashboard') }}">NP</a>
        </div>
        <ul class="sidebar-menu">

            {{-- Dashboard --}}
            <li class="{{ is_active_route(null, 2) && request()->segment(1) === 'admin' && !request()->segment(2) ? 'active' : is_active_route('dashboard', 1) }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home"></i> <span>{{ __('Dashboard') }}</span>
                </a>
            </li>

            {{-- Settings --}}
            <li class="{{ is_active_route('settings', 2) }}">
                <a class="nav-link" href="{{ route('admin.settings.index') }}">
                    <i class="fas fa-cog"></i> <span>{{ __('Settings') }}</span>
                </a>
            </li>

            {{-- Add more menu items below as needed --}}
            {{--
            <li class="menu-header">Management</li>

            <li class="{{ is_active_route('users', 2) }}">
                <a class="nav-link" href="#">
                    <i class="fas fa-users"></i> <span>{{ __('Users') }}</span>
                </a>
            </li>

            <li class="{{ is_active_route('subscriptions', 2) }}">
                <a class="nav-link" href="#">
                    <i class="fas fa-crown"></i> <span>{{ __('Subscriptions') }}</span>
                </a>
            </li>

            <li class="{{ is_active_route('payments', 2) }}">
                <a class="nav-link" href="#">
                    <i class="fas fa-money-bill-wave"></i> <span>{{ __('Payments') }}</span>
                </a>
            </li>
            --}}

        </ul>
    </aside>
</div>
