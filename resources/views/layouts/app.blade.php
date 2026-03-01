<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #343a40; color: white; }
        .sidebar a { color: rgba(255,255,255,.8); text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,.1); color: white; }
        .nav-header { padding: 10px 15px; font-size: 0.85em; text-transform: uppercase; color: #6c757d; }
    </style>
</head>
<body>
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="p-3 text-center border-bottom border-secondary">
                <h4>{{ config('app.name') }}</h4>
            </div>
            <nav class="mt-3">
                <a href="{{ route('admin.settings.index') }}">Dashboard</a>
                <div class="nav-header">Manage</div>
                <a href="{{ route('admin.settings.index') }}" class="active">Settings</a>
                
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                     @csrf
                </form>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="mt-5 text-danger">Logout</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10">
            <nav class="navbar navbar-light bg-white shadow-sm mb-4">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">Admin Panel</span>
                    <div class="d-flex">
                        <span class="me-3">{{ Auth::user()->name ?? 'Guest' }}</span>
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
