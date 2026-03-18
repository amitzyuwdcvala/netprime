<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard') }}">FlixyGO</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('admin.dashboard') }}">FG</a>
        </div>
        <ul class="sidebar-menu">
            @foreach (config('navigation.sidebar', []) as $attribute)
                @if (!empty($attribute['is_visible']))
                    <li class="{{ request()->routeIs($attribute['link']) ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route($attribute['link']) }}">
                            <i class="fas {{ $attribute['icon'] ?? 'fa-circle' }}"></i>
                            <span>{{ __($attribute['label']) }}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </aside>
</div>