@extends('layouts.app')

@section('content')
    <div class="container">

        <img class="img-fluid logo-dark mb-2 logo-color" src="{{ asset('/img/logo2.png') }}" alt="Logo">
        <img class="img-fluid logo-light mb-2" src="{{ asset('/img/logo2-white.png') }}" alt="Logo">
        <div class="loginbox">
            <div class="login-right">
                <div class="login-right-wrap">
                    <h1>Login</h1>
                    <p class="account-subtitle">Access to our dashboard</p>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="input-block mb-3">
                            <label class="form-control-label">Email Address</label>
                            <input id="email" type="email" class="form-control  @error('email') is-invalid @enderror"
                                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="input-block mb-3">
                            <label class="form-control-label">Password</label>
                            <div class="pass-group">
                                <input id="password" type="password"
                                    class="form-control  pass-input @error('password') is-invalid @enderror" name="password"
                                    required autocomplete="current-password">
                                <span class="fas fa-eye toggle-password"></span>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="input-block mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check custom-checkbox">
                                        <input type="checkbox" class="form-check-input" name="remember" id="remember"
                                            {{ old('remember') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="remember">Remember me</label>
                                    </div>
                                </div>
                                <div class="col-6 text-end">
                                    @if (Route::has('password.request'))
                                        <a class="forgot-link" href="{{ route('password.request') }}">Forgot Password ?</a>
                                    @endif

                                </div>
                            </div>
                        </div>
                        <button class="btn btn-lg  btn-primary w-100" type="submit">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
