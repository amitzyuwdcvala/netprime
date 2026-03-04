<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="admin-toggle-active-url" content="{{ route('admin.toggle.active') }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}" />

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

    <link rel="stylesheet" href="{{ asset('css/toastr.min.css') }}">

    <link rel="stylesheet" href="{{ asset('css/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">

    @stack('styles')

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">

    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

</head>

<body>

    <div id="app">
        <div class="main-wrapper">

            @include('layouts.header')

            @include('layouts.sidebar')

            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    @yield('content')
                </section>
            </div>

            @include('layouts.footer')

        </div>
    </div>

    <div class="spinner">
        <div class="bounce1"></div>
        <div class="bounce2"></div>
        <div class="bounce3"></div>
    </div>

    <div id="commonModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title mt-2"></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script type="text/javascript" src="{{ asset('js/jquery-3.5.1.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/popper.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.nicescroll.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/letter.avatar.js') }}"></script>

    <script type="text/javascript" src="{{ asset('js/jquery.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

    <script type="text/javascript" src="{{ asset('js/toastr.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('js/stisla.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dropzone.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/select2.full.min.js') }}"></script>

    <!-- jQuery Validate -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Template JS File -->
    <script type="text/javascript" src="{{ asset('js/scripts.js') }}"></script>

    <!-- GLOBAL JS MESSAGE VARIABLES -->
    <script type="text/javascript">
        var globalAjaxSuccessLabel = '{{ __("Success") }}';
        var globalAjaxErrorLabel = '{{ __("Error") }}';
        var globalAjaxErrorMessage = '{{ __("Some Thing Is Wrong") }}';
    </script>

    <script type="text/javascript" src="{{ asset('js/custom.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/admin-common.js') }}"></script>

    @stack('scripts')

    @if(Session::has('success'))
        <script> toastrr(globalAjaxSuccessLabel, "{!! session('success') !!}", 'success'); </script>
    @endif
    @if(Session::has('error'))
        <script> toastrr(globalAjaxErrorLabel, "{!! session('error') !!}", 'error'); </script>
    @endif
</body>

</html>