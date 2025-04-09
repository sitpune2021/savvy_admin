<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="dark"
    data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default"
    data-theme-colors="default">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.seo', [
        'title' => $title ?? null,
        'description' => $description ?? null,
        'keywords' => $keywords ?? null,
        'image' => $image ?? null,
    ])
    @stack('styles')
    @include('layouts.partials.head')
</head>

<body>
    @guest
        @yield('content')
    @else
        <div id="layout-wrapper">
            @include('layouts.partials.menu') <!-- Sidebar menu -->
            @include('layouts.partials.sidebar') <!-- Sidebar menu -->
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </div>
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>
                                © Velzon.
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-end d-none d-sm-block">
                                    Design & Develop by Themesbrand
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!--start back-to-top-->
        <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
            <i class="ri-arrow-up-line"></i>
        </button>
        <!--end back-to-top-->

        <!--preloader-->
        <div id="preloader">
            <div id="status">
                <div class="spinner-border text-primary avatar-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
        {{-- @include('layouts.partials.themeSetting') <!-- Sidebar menu --> --}}

    @endguest

    <!-- JAVASCRIPT -->
    <script src="{{ asset('/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('/assets/js/plugins.js') }}"></script>

    @stack('scripts')



</body>

</html>
