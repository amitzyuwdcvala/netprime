@extends('layouts.auth')

@section('content')
<div class="card card-primary">
    <div class="card-header"><h4>{{ __('Login') }}</h4></div>

    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <p class="mb-0">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="form-group">
                <label for="email">{{ __('Email') }}</label>
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" tabindex="1" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="control-label">{{ __('Password') }}</label>
                <input id="password" type="password" class="form-control" name="password" tabindex="2" required>
            </div>

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="remember" class="custom-control-input" tabindex="3" id="remember">
                    <label class="custom-control-label" for="remember">{{ __('Remember Me') }}</label>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                    {{ __('Login') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
