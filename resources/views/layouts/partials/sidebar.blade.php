<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ url('/') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-1.png') }}" alt="" height="22" />

            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo-1.png') }}" alt="" height="45" />
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ url('/') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-1.png') }}" alt="" height="22" />
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo-1.png') }}" alt="" height="45" />

            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                @foreach (config('menu') as $item)
                    @if (auth()->check() && auth()->user()->hasAnyRole($item['roles']))
                        @if (isset($item['type']) && $item['type'] === 'section')
                            <li class="menu-title">
                                <i class="ri-more-fill"></i>
                                <span data-key="t-pages">{{ $item['label'] }}</span>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ Request::is($item['url'] . '*') ? 'active' : '' }}"
                                    href="{{ url($item['url']) }}">
                                    <i class="{{ $item['icon'] }}"></i>
                                    <span data-key="t-dashboards">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>

        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
