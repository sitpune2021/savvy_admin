@extends('layouts.app')
@push('styles')
    <link href="{{ asset('/assets/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
    <script src="{{ asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('/assets/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ asset('/assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>
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
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Total Earnings
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="text-success fs-14 mb-0">
                                            <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                                            +16.24 %
                                        </h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                            ₹<span class="counter-value" data-target="559.25">0</span>k
                                        </h4>
                                        <a href="#" class="text-decoration-underline">View net earnings</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle rounded fs-3">
                                            <i class="bx bx-dollar-circle text-success"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Orders
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="text-danger fs-14 mb-0">
                                            <i class="ri-arrow-right-down-line fs-13 align-middle"></i>
                                            -3.57 %
                                        </h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                            <span class="counter-value" data-target="36894">0</span>
                                        </h4>
                                        <a href="#" class="text-decoration-underline">View all orders</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle rounded fs-3">
                                            <i class="bx bx-shopping-bag text-info"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            Customers
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="text-success fs-14 mb-0">
                                            <i class="ri-arrow-right-up-line fs-13 align-middle"></i>
                                            +29.08 %
                                        </h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                            <span class="counter-value" data-target="183.35">0</span>M
                                        </h4>
                                        <a href="#" class="text-decoration-underline">See details</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                                            <i class="bx bx-user-circle text-warning"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                            My Balance
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="text-muted fs-14 mb-0">+0.00 %</h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                            ₹<span class="counter-value" data-target="165.89">0</span>k
                                        </h4>
                                        <a href="#" class="text-decoration-underline">Withdraw money</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                                            <i class="bx bx-wallet text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row-->

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
                                    Store Visits by Source
                                </h4>
                                <div class="flex-shrink-0">
                                    <div class="dropdown card-header-dropdown">
                                        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <span class="text-muted">Report<i
                                                    class="mdi mdi-chevron-down ms-1"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Download Report</a>
                                            <a class="dropdown-item" href="#">Export</a>
                                            <a class="dropdown-item" href="#">Import</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end card header -->

                            <div class="card-body">
                                <div id="store-visits-source"
                                    data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
                                    data-colors-minimal='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]'
                                    data-colors-interactive='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]'
                                    data-colors-galaxy='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]'
                                    class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                        <!-- .card-->
                    </div>
                    <!-- end col -->
                </div>


                <!-- end row-->

                <div class="row">

                    <!-- .col-->

                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">
                                    Recent Orders
                                </h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-info btn-sm material-shadow-none">
                                        <i class="ri-file-list-3-line align-middle"></i>
                                        Generate Report
                                    </button>
                                </div>
                            </div>
                            <!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                        <thead class="text-muted table-light">
                                            <tr>
                                                <th scope="col">Order ID</th>
                                                <th scope="col">Customer</th>
                                                <th scope="col">Product</th>
                                                <th scope="col">Amount</th>
                                                <th scope="col">Vendor</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Rating</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <a href="apps-ecommerce-order-details.html"
                                                        class="fw-medium link-primary">#VZ2112</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="assets/images/users/avatar-1.jpg" alt=""
                                                                class="avatar-xs rounded-circle material-shadow" />
                                                        </div>
                                                        <div class="flex-grow-1">Alex Smith</div>
                                                    </div>
                                                </td>
                                                <td>Clothes</td>
                                                <td>
                                                    <span class="text-success">₹109.00</span>
                                                </td>
                                                <td>Zoetic Fashion</td>
                                                <td>
                                                    <span class="badge bg-success-subtle text-success">Paid</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 fw-medium mb-0">
                                                        5.0<span class="text-muted fs-11 ms-1">(61 votes)</span>
                                                    </h5>
                                                </td>
                                            </tr>
                                            <!-- end tr -->
                                            <tr>
                                                <td>
                                                    <a href="apps-ecommerce-order-details.html"
                                                        class="fw-medium link-primary">#VZ2111</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="assets/images/users/avatar-2.jpg" alt=""
                                                                class="avatar-xs rounded-circle material-shadow" />
                                                        </div>
                                                        <div class="flex-grow-1">Jansh Brown</div>
                                                    </div>
                                                </td>
                                                <td>Kitchen Storage</td>
                                                <td>
                                                    <span class="text-success">₹149.00</span>
                                                </td>
                                                <td>Micro Design</td>
                                                <td>
                                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 fw-medium mb-0">
                                                        4.5<span class="text-muted fs-11 ms-1">(61 votes)</span>
                                                    </h5>
                                                </td>
                                            </tr>
                                            <!-- end tr -->
                                            <tr>
                                                <td>
                                                    <a href="apps-ecommerce-order-details.html"
                                                        class="fw-medium link-primary">#VZ2109</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="assets/images/users/avatar-3.jpg" alt=""
                                                                class="avatar-xs rounded-circle material-shadow" />
                                                        </div>
                                                        <div class="flex-grow-1">Ayaan Bowen</div>
                                                    </div>
                                                </td>
                                                <td>Bike Accessories</td>
                                                <td>
                                                    <span class="text-success">₹215.00</span>
                                                </td>
                                                <td>Nesta Technologies</td>
                                                <td>
                                                    <span class="badge bg-success-subtle text-success">Paid</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 fw-medium mb-0">
                                                        4.9<span class="text-muted fs-11 ms-1">(89 votes)</span>
                                                    </h5>
                                                </td>
                                            </tr>
                                            <!-- end tr -->
                                            <tr>
                                                <td>
                                                    <a href="apps-ecommerce-order-details.html"
                                                        class="fw-medium link-primary">#VZ2108</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="assets/images/users/avatar-4.jpg" alt=""
                                                                class="avatar-xs rounded-circle material-shadow" />
                                                        </div>
                                                        <div class="flex-grow-1">Prezy Mark</div>
                                                    </div>
                                                </td>
                                                <td>Furniture</td>
                                                <td>
                                                    <span class="text-success">₹199.00</span>
                                                </td>
                                                <td>Syntyce Solutions</td>
                                                <td>
                                                    <span class="badge bg-danger-subtle text-danger">Unpaid</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 fw-medium mb-0">
                                                        4.3<span class="text-muted fs-11 ms-1">(47 votes)</span>
                                                    </h5>
                                                </td>
                                            </tr>
                                            <!-- end tr -->
                                            <tr>
                                                <td>
                                                    <a href="apps-ecommerce-order-details.html"
                                                        class="fw-medium link-primary">#VZ2107</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="assets/images/users/avatar-6.jpg" alt=""
                                                                class="avatar-xs rounded-circle material-shadow" />
                                                        </div>
                                                        <div class="flex-grow-1">Vihan Hudda</div>
                                                    </div>
                                                </td>
                                                <td>Bags and Wallets</td>
                                                <td>
                                                    <span class="text-success">₹330.00</span>
                                                </td>
                                                <td>iTest Factory</td>
                                                <td>
                                                    <span class="badge bg-success-subtle text-success">Paid</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 fw-medium mb-0">
                                                        4.7<span class="text-muted fs-11 ms-1">(161 votes)</span>
                                                    </h5>
                                                </td>
                                            </tr>
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
                <!-- end row-->
            </div>
            <!-- end .h-100-->
        </div>
        <!-- end col -->
    </div>
@endsection
