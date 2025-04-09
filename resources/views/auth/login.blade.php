@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('/assets/libs/particles.js/particles.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/particles.app.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/passowrd-create.init.js') }}"></script>
@endpush
@section('content')
    {{-- <div class="container">

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
    </div> --}}

    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <a class="d-inline-block auth-logo">
                                    <img src="{{ asset('/assets/images/logo-1.png') }}" alt="" height="30">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4 card-bg-fill">

                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Welcome Back !</h5>
                                    <p class="text-muted">Sign in to continue to savvy.</p>
                                </div>
                                <div class="p-2 mt-4">
                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="email" class="form-label">E-mail</label>
                                            <input id="email" type="email"
                                                class="form-control  @error('email') is-invalid @enderror" name="email"
                                                value="{{ old('email') }}" required autocomplete="email" autofocus
                                                placeholder="Enter username">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <div class="float-end">
                                                @if (Route::has('password.request'))
                                                    <a class="text-muted" href="{{ route('password.request') }}">Forgot
                                                        Password ?</a>
                                                @endif
                                            </div>
                                            <label class="form-label" for="password-input">Password</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password"
                                                    class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                                                    placeholder="Enter password" id="password-input" name="password"
                                                    required autocomplete="current-password">
                                                <button
                                                    class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                                                    type="button" id="password-addon"><i
                                                        class="ri-eye-fill align-middle"></i></button>
                                                @error('password')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="auth-remember-check"
                                                name="remember"{{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="auth-remember-check">Remember me</label>
                                        </div>



                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Sign In</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy;
                                <script>
                                    document.write(new Date().getFullYear())
                                </script> Velzon. Crafted with <i class="mdi mdi-heart text-danger"></i> by
                                Themesbrand
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection
