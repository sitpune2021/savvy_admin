<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="light"  data-sidebar-size="lg" data-sidebar-image="none">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials.seo', [
        'title' => $title ?? null,
        'description' => $description ?? null,
        'keywords' => $keywords ?? null,
        'image' => $image ?? null,
    ]);
    @include('layouts.partials.head')
    @stack('styles')
</head>
<body>
    @guest
    @yield('content')
    @else
    <div class="main-wrapper">
        @include('layouts.partials.menu') <!-- Sidebar menu -->
        @include('layouts.partials.sidebar') <!-- Sidebar menu -->
        <div class="page-wrapper">
            <div class="content container-fluid pb-0">
            @yield('content')
            </div>
        </div>
    </div>
    {{-- @include('layouts.partials.themeSetting') <!-- Sidebar menu --> --}}

    @endguest
</body>
</html>
