@extends('layouts.app')
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
                    @php
                        $isGrouped =
                            isset($record) &&
                            is_array($record) &&
                            collect($record)->every(
                                fn($val) => is_array($val) || $val instanceof \Illuminate\Support\Collection,
                            );
                    @endphp

                    @if ($isGrouped)
                        @foreach ($record as $key => $regionData)
                            @include('components.dashbordCard', ['region' => $key, 'data' => $regionData])
                        @endforeach
                    @else
                        @include('components.dashbordCards')
                    @endif
                </div>
                @php
                    $userRole = auth()->user()->role;
                    $isAdmin = $userRole === 'admin';
                @endphp

                <div class="row">
                    @if ($isAdmin)
                        @foreach ($plantWiseStats as $plantName => $stats)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-sm border-0 rounded-3">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary fw-bold">{{ ucfirst($plantName) }}
                                            Plant</h5>
                                        <h3 class="fw-bold">{{ $stats['thisMonthOrders'] }} <small
                                                class="text-muted">orders</small></h3>

                                        {{-- Percentage Growth --}}
                                        <p class="mb-3 {{ $stats['orderChange'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $stats['orderChange'] >= 0 ? '▲' : '▼' }}
                                            {{ $stats['orderChange'] }}% vs last month
                                        </p>

                                        {{-- Status-wise counts --}}
                                        <div class="d-flex justify-content-between text-center mb-3">
                                            <div class="bg-warning bg-opacity-25 p-2 rounded w-100 me-1">
                                                <strong
                                                    class="text-warning">{{ $stats['thisMonthPendingOrders'] }}</strong>
                                                <div class="small text-muted">Pending</div>
                                            </div>
                                            <div class="bg-success bg-opacity-25 p-2 rounded w-100 mx-1">
                                                <strong class="text-success">{{ $stats['thisMonthCompletedOrders'] }}</strong>
                                                <div class="small text-muted">Completed</div>
                                            </div>
                                            <div class="bg-info bg-opacity-25 p-2 rounded w-100 ms-1">
                                                <strong class="text-info">{{ $stats['thisMonthInProgressOrders'] }}</strong>
                                                <div class="small text-muted">In Progress</div>
                                            </div>
                                        </div>

                                        <h3 class="fw-bold">{{ $stats['todayOrders'] }} <small
                                                class="text-muted">Todays orders</small></h3>

                                        <div class="d-flex justify-content-between text-center mb-3">
                                            <div class="bg-warning bg-opacity-25 p-2 rounded w-100 me-1">
                                                <strong
                                                    class="text-warning">{{ $stats['todayPendingOrders'] }}</strong>
                                                <div class="small text-muted">Pending</div>
                                            </div>
                                            <div class="bg-success bg-opacity-25 p-2 rounded w-100 mx-1">
                                                <strong class="text-success">{{ $stats['todayCompletedOrders'] }}</strong>
                                                <div class="small text-muted">Completed</div>
                                            </div>
                                            <div class="bg-info bg-opacity-25 p-2 rounded w-100 ms-1">
                                                <strong class="text-info">{{ $stats['todayInProgressOrders'] }}</strong>
                                                <div class="small text-muted">In Progress</div>
                                            </div>
                                        </div>

                                        <h3 class="fw-bold">{{ $stats['yesterdayPendingOrders'] }} <small
                                                class="text-muted">Yesterday orders</small></h3>

                                        <div class="d-flex justify-content-between text-center mb-3">
                                            <div class="bg-warning bg-opacity-25 p-2 rounded w-100 me-1">
                                                <strong
                                                    class="text-warning">{{ $stats['allPendingOrdersCount'] }}</strong>
                                                <div class="small text-muted">All Pending</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
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
                    @endif


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

                @if ($isAdmin || auth()->user()->role === 'plant-manager')
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header border-0 align-items-center d-flex flex-wrap gap-3">
                            <div class="flex-grow-1">
                                <h4 class="card-title mb-1">Raw Material Stock</h4>
                                <p class="text-muted mb-0">
                                    Label-wise stock and jar availability for the selected plant
                                </p>
                            </div>

                            <form method="GET" action="{{ route('home') }}" class="d-flex align-items-end gap-2 flex-wrap">
                                <div>
                                    <label for="plant-search" class="form-label fw-semibold mb-1">Search Plant</label>
                                    <input id="plant-search" type="search" class="form-control"
                                        placeholder="Type plant name" autocomplete="off">
                                </div>
                                <div>
                                    <label for="raw-material-plant" class="form-label fw-semibold mb-1">Plant</label>
                                    <select id="raw-material-plant" name="plant_id" class="form-select"
                                        onchange="this.form.submit()">
                                        @foreach ($dashboardPlants as $plant)
                                            <option value="{{ $plant->id }}" @selected($selectedPlant?->id === $plant->id)>
                                                {{ ucfirst($plant->name) }} Plant
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if (request()->filled('value'))
                                    <input type="hidden" name="value" value="{{ request('value') }}">
                                @endif
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="row g-4">
                                @foreach ([
                                    ['key' => 'labels', 'title' => 'Labels', 'color' => 'primary'],
                                    ['key' => 'caps', 'title' => 'Caps', 'color' => 'success'],
                                    ['key' => 'jars', 'title' => 'Jars', 'color' => 'warning'],
                                    ['key' => 'maintenance', 'title' => 'Maintenance', 'color' => 'danger'],
                                ] as $materialGroup)
                                    <div class="col-md-6 col-xl-3">
                                        <div class="border rounded h-100 overflow-hidden"
                                            data-material-group="{{ $materialGroup['key'] }}">
                                            <div class="bg-{{ $materialGroup['color'] }}-subtle p-3">
                                                <h5 class="text-{{ $materialGroup['color'] }} mb-0">
                                                    {{ $materialGroup['title'] }}
                                                </h5>
                                                @if (in_array($materialGroup['key'], ['labels', 'jars']))
                                                    <input type="search"
                                                        class="form-control form-control-sm mt-2 material-stock-search"
                                                        data-search-group="{{ $materialGroup['key'] }}"
                                                        placeholder="Search {{ strtolower($materialGroup['title']) }}..."
                                                        autocomplete="off">
                                                @endif
                                            </div>
                                            <div class="table-responsive"
                                                @if ($rawMaterialStock[$materialGroup['key']]->count() > 10)
                                                    style="max-height: 455px; overflow-y: auto;"
                                                @endif>
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Type</th>
                                                            <th class="text-end">Quantity</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($rawMaterialStock[$materialGroup['key']] as $stock)
                                                            <tr class="stock-row"
                                                                data-stock-name="{{ strtolower($stock['name']) }}">
                                                                <td>{{ $stock['name'] }}</td>
                                                                <td class="text-end fw-semibold">
                                                                    {{ number_format($stock['quantity']) }}
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2" class="text-center text-muted py-4">
                                                                    No stock available
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if ($allPendingOrdersCount > 0)
                    @include('components.dashbordTable')
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

        document.addEventListener('DOMContentLoaded', function () {
            const plantSearch = document.getElementById('plant-search');
            const plantSelect = document.getElementById('raw-material-plant');

            if (plantSearch && plantSelect) {
                const plantOptions = Array.from(plantSelect.options);

                plantSearch.addEventListener('input', function () {
                    const searchTerm = this.value.trim().toLowerCase();

                    plantOptions.forEach(function (option) {
                        option.hidden = searchTerm !== '' &&
                            !option.text.toLowerCase().includes(searchTerm);
                    });

                    const firstMatch = plantOptions.find(option => !option.hidden);
                    if (firstMatch && searchTerm !== '') {
                        plantSelect.value = firstMatch.value;
                    }
                });

                plantSearch.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        const matchingOption = plantOptions.find(option => !option.hidden);
                        if (matchingOption) {
                            plantSelect.value = matchingOption.value;
                            plantSelect.form.submit();
                        }
                    }
                });
            }

            document.querySelectorAll('.material-stock-search').forEach(function (searchInput) {
                searchInput.addEventListener('input', function () {
                    const searchTerm = this.value.trim().toLowerCase();
                    const group = document.querySelector(
                        '[data-material-group="' + this.dataset.searchGroup + '"]'
                    );

                    group?.querySelectorAll('.stock-row').forEach(function (row) {
                        row.classList.toggle(
                            'd-none',
                            !row.dataset.stockName.includes(searchTerm)
                        );
                    });
                });
            });
        });
    </script>
@endsection
