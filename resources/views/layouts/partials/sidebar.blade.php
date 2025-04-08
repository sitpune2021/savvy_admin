<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul class="sidebar-vertical">
                <!-- Main -->
                <li class="menu-title"><span>Main</span></li>
                <li>
                    <a href="{{ url('/') }}" class=" {{ Request::is('/') ? 'active' : '' }}"><i
                            data-feather="home"></i> <span>Dashboard</span></a>
                </li>

                <!-- Customers -->
                <li class="menu-title"><span>Customers</span></li>
                <li>
                    <a href="{{ url('plant') }}" class="{{ Request::is('plant*') ? 'active' : '' }}"><i
                            data-feather="users"></i> <span>Plants</span></a>
                </li>

                <li>
                    <a href="{{ url('product') }}" class="{{ Request::is('product*') ? 'active' : '' }}"><i
                            data-feather="users"></i> <span>Products</span></a>
                </li>



                <li>
                    <a href="{{ url('customer') }}" class="{{ Request::is('customer*') ? 'active' : '' }}"><i
                            data-feather="users"></i> <span>Customers</span></a>
                </li>
                <li>
                    <a href="{{ url('driver') }}" class="{{ Request::is('driver*') ? 'active' : '' }}"><i
                            data-feather="truck"></i> <span>Drivers</span></a>
                </li>
                {{-- <li>
                    <a href="{{ url('customer') }}" class="{{ Request::is('customer*') ? 'active' : '' }}" ><i data-feather="user"></i> <span>Venders</span></a>
                </li> --}}

                <!-- Purchases -->
                <li class="menu-title"><span>Purchases</span></li>
                <li>
                    <a href="{{ url('order') }}" class="{{ Request::is('order*') ? 'active' : '' }}"><i
                            data-feather="shopping-bag"></i> <span>Orders</span></a>
                </li>

                <!-- Settings -->
                <li class="menu-title"><span>Settings</span></li>
                <li>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i data-feather="power"></i> <span>Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
