<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
            <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">
        <li class="dropdown profile-dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                @if (!empty(Auth::guard('admin')->user()->avatar) && Storage::exists(Auth::guard('admin')->user()->avatar))
                    <img src="{{ Storage::url(Auth::guard('admin')->user()->avatar) }}" class="img-fluid rounded-circle mr-1" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}';">
                @else
                    <img avatar="{{Auth::guard('admin')->user()->name}}" class="img-fluid rounded-circle mr-1">
                @endif
                <div class="d-sm-none d-lg-inline-block">{{ __('Hi') }}, {{ Auth::guard('admin')->user()->name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title">{{ __('Logged in') }} <br> {{ Carbon\Carbon::parse(Auth::guard('admin')->user()->last_login_at)->diffForHumans()}} </div>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item has-icon text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}</a>
                    <form method="POST" id="logout-form" action="{{ route('logout') }}" style="display:none;">
                        @csrf
                    </form>
                </a>
            </div>
        </li>
    </ul>
</nav>
