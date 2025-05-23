@extends('layouts.app')
@php
    $orderIsDown = $orderChange < 0;
    $orderChangeClass = $orderIsDown ? 'text-danger' : 'text-success';
    $orderIcon = $orderIsDown ? 'ri-arrow-right-down-line' : 'ri-arrow-right-up-line';

    $customerIsDown = $customerChange < 0;
    $customerChangeClass = $customerIsDown ? 'text-danger' : 'text-success';
    $customerIcon = $customerIsDown ? 'ri-arrow-right-down-line' : 'ri-arrow-right-up-line';
    $statusClasses = [
        'cancelled' => 'bg-danger-subtle text-danger',
        'pending' => 'bg-warning-subtle text-warning',
        'completed' => 'bg-success-subtle text-success',
        'in_progress' => 'bg-info-subtle text-info',
    ];
@endphp
@push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
       <link href="{{ asset('/assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    
    <script src="{{ asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ asset('/assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/datatables.init.js') }}"></script>
    <script src="{{ asset('/assets/js/app.js') }}"></script>
@endpush
@section('content')
    <div class="row">
        <div class="col">
            <div class="h-100">
                <div class="row mb-3 pb-1">
                    <div class="col-12">
                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-16 mb-1">Good Morning, {{ ucfirst(Auth::user()->name) }}</h4>
                                <p class="text-muted mb-0">
                                    Here's what's happening with your store today.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Orders
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="{{ $orderChangeClass }} fs-14 mb-0">
                                            <i class="{{ $orderIcon }} fs-13 align-middle"></i>
                                            {{ $orderChange }} %
                                        </h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                            <span class="counter-value" data-target="{{ $thisMonthOrders }}">0</span>
                                        </h4>
                                        <a href="{{ url('order') }}" class="text-decoration-underline">View all orders</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle rounded fs-3">
                                            <i class="bx bx-shopping-bag text-info"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Today Orders
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                            <span class="counter-value" data-target="{{ $todayOrders }}">0</span>
                                        </h4>
                                        <a href="{{ url('order') }}" class="d-flex align-items-center gap-2">
                                            <p class="fs-16 mb-0 text-muted"><i
                                                    class="mdi mdi-circle fs-14 align-middle text-success me-1"></i><span
                                                    class="counter-value"
                                                    data-target="{{ $todayCompletedOrders }}">0</span> </p>
                                            <p class="fs-16 mb-0 text-muted"><i
                                                    class="mdi mdi-circle fs-14 align-middle text-danger me-1"></i><span
                                                    class="counter-value" data-target="{{ $todayPendingOrders }}">0</span>
                                            </p>
                                            <p class="fs-16 mb-0 text-muted"><i
                                                    class="mdi mdi-circle fs-14 align-middle text-warning me-1"></i><span
                                                    class="counter-value"
                                                    data-target="{{ $todayInProgressOrders }}">0</span>
                                            </p>
                                        </a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle rounded fs-3">
                                            <i class="bx bx-shopping-bag text-info"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate bg-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-white text-truncate mb-0">
                                            Yesterday Orders
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-white">
                                            <span class="counter-value"
                                                data-target="{{ $yesterdayPendingOrders }}">0</span>
                                        </h4>

                                        <a href="#yesterdayPendingOrders" class="text-decoration-underline text-white">
                                            <span class="counter-value" data-target="{{ count($allPendingOrders) }}">0</span>
                                            view pending orders
                                        </a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title  bg-white bg-opacity-25 rounded fs-3">
                                            <i class="bx bx-shopping-bag text-white"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Customers
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="{{ $customerChangeClass }} fs-14 mb-0">
                                            <i class="{{ $customerIcon }} fs-13 align-middle"></i>
                                            {{ $customerChange }} %
                                        </h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                            <span class="counter-value" data-target="{{ $thisMonthCustomers }}">0</span>
                                        </h4>
                                        <a href="{{ url('customer') }}" class="text-decoration-underline">See details</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                                            <i class="bx bx-user-circle text-warning"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header border-0 align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Revenue</h4>
                                <div>
                                    <button type="button" class="btn btn-soft-secondary material-shadow-none btn-sm">
                                        ALL
                                    </button>
                                    <button type="button" class="btn btn-soft-secondary material-shadow-none btn-sm">
                                        1M
                                    </button>
                                    <button type="button" class="btn btn-soft-secondary material-shadow-none btn-sm">
                                        6M
                                    </button>
                                    <button type="button" class="btn btn-soft-primary material-shadow-none btn-sm">
                                        1Y
                                    </button>
                                </div>
                            </div>
                            <!-- end card header -->

                            <div class="card-header p-0 border-0 bg-light-subtle">
                                <div class="row g-0 text-center">
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0">
                                            <h5 class="mb-1">
                                                <span class="counter-value" data-target="7585">0</span>
                                            </h5>
                                            <p class="text-muted mb-0">Orders</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0">
                                            <h5 class="mb-1">
                                                ₹<span class="counter-value" data-target="22.89">0</span>k
                                            </h5>
                                            <p class="text-muted mb-0">Earnings</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0">
                                            <h5 class="mb-1">
                                                <span class="counter-value" data-target="367">0</span>
                                            </h5>
                                            <p class="text-muted mb-0">Refunds</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0 border-end-0">
                                            <h5 class="mb-1 text-success">
                                                <span class="counter-value" data-target="18.92">0</span>%
                                            </h5>
                                            <p class="text-muted mb-0">
                                                Conversation Ratio
                                            </p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                            </div>
                            <!-- end card header -->

                            <div class="card-body p-0 pb-2">
                                <div class="w-100">
                                    <div id="customer_impression_charts"
                                        data-colors='["--vz-primary", "--vz-success", "--vz-danger"]'
                                        data-colors-minimal='["--vz-light", "--vz-primary", "--vz-info"]'
                                        data-colors-saas='["--vz-success", "--vz-info", "--vz-danger"]'
                                        data-colors-modern='["--vz-warning", "--vz-primary", "--vz-success"]'
                                        data-colors-interactive='["--vz-info", "--vz-primary", "--vz-danger"]'
                                        data-colors-creative='["--vz-warning", "--vz-primary", "--vz-danger"]'
                                        data-colors-corporate='["--vz-light", "--vz-primary", "--vz-secondary"]'
                                        data-colors-galaxy='["--vz-secondary", "--vz-primary", "--vz-primary-rgb, 0.50"]'
                                        data-colors-classic='["--vz-light", "--vz-primary", "--vz-secondary"]'
                                        data-colors-vintage='["--vz-success", "--vz-primary", "--vz-secondary"]'
                                        class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                    <div class="col-xl-4">
                        <div class="card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">
                                    Orders Distribution Across Plants
                                </h4>
                            </div>
                            <!-- end card header -->

                            <div class="card-body">
                                <div id="store-visits-source"
                                    data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
                                    class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                        <!-- .card-->
                    </div>
                    <!-- end col -->
                </div>
                @if (count($allPendingOrders) > 0)
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header align-items-center d-flex">
                                    <h4 class="card-title mb-0 flex-grow-1">
                                        Yesterday Orders
                                    </h4>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive table-card">
                                        <table class="table table-borderless table-centered align-middle table-nowrap mb-0"
                                            id="yesterdayPendingOrders" >
                                            <thead class="text-muted table-light">
                                                <tr>
                                                    <th scope="col">Order ID</th>
                                                    <th scope="col">Customer</th>
                                                    <th scope="col">shipping Address</th>
                                                    <th scope="col">Driver</th>
                                                    <th scope="col">Delivery Quantity</th>
                                                    <th scope="col">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($allPendingOrders as $order)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ url('order/' . $order->id) }}"
                                                                class="fw-medium link-primary">#{{ $order->id }}
                                                                @if( auth()->user()?->vendor?->id === null && $order->drivers?->vendor_id != null)
                                                                    <i class="ri-user-shared-line"></i>
                                                                @endif
                                                                @if( $order->type == 'additional')
                                                                    <i class="ri-shopping-cart-line"></i>
                                                                @endif
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-grow-1">
                                                                    <span style="white-space: pre-wrap;">{{ $order->customers->name }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-grow-1">
                                                                    <span style="white-space: pre-wrap;">{{ $order->shipping->shipping_address }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{{ $order?->drivers?->name }}</td>
                                                        <td>{{ $order->develivered_qty }}</td>
                                                        <td>
                                                            <span
                                                                class="badge {{ $statusClasses[$order->status] ?? 'bg-secondary-subtle text-secondary' }} p-2">
                                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <!-- end tr -->
                                            </tbody>
                                            <!-- end tbody -->
                                        </table>
                                        <!-- end table -->
                                    </div>
                                </div>
                            </div>
                            <!-- .card-->
                        </div>
                        <!-- .col-->
                    </div>
                @endif
                <!-- end row-->
            </div>
            <!-- end .h-100-->
        </div>
        <!-- end col -->
    </div>
    <script>
        window.seriesData = @json($ordersCountByPlant->values()); // Order counts (series data)
        window.labels = @json(collect($ordersCountByPlant->keys())->map(function ($id) use ($plants) {
                    return $plants[$id] ?? 'Unknown';
                })->values());
    </script>
@endsection
