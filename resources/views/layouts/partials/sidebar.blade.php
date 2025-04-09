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
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                        <i class="ri-dashboard-2-line"></i>
                        <span data-key="t-dashboards">Dashboards</span>
                    </a>
                </li>
                <!-- end Dashboard Menu -->

                <li class="menu-title">
                    <i class="ri-more-fill"></i>
                    <span data-key="t-pages">Customers</span>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('plant*') ? 'active' : '' }}"
                        href="{{ url('plant') }}">
                        <i class="ri-building-2-line"></i>
                        <span data-key="t-dashboards">Plants</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('product*') ? 'active' : '' }}"
                        href="{{ url('product') }}">
                        <i class="ri-dashboard-2-line"></i>
                        <span data-key="t-dashboards">Products</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('driver*') ? 'active' : '' }}"
                        href="{{ url('driver') }}">
                        <i class="ri-dashboard-2-line"></i>
                        <span data-key="t-dashboards">Drivers</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('customer*') ? 'active' : '' }}"
                        href="{{ url('customer') }}">
                        <i class="ri-dashboard-2-line"></i>
                        <span data-key="t-dashboards">Customers</span>
                    </a>
                </li>

                <li class="menu-title">
                    <i class="ri-more-fill"></i>
                    <span data-key="t-pages">Purchases</span>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('order*') ? 'active' : '' }}"
                        href="{{ url('order') }}">
                        <i class="ri-dashboard-2-line"></i>
                        <span data-key="t-dashboards">Orders</span>
                    </a>
                </li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
