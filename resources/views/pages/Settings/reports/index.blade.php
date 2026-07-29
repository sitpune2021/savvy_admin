@extends('layouts.app')
@php
    $title = 'Reports - ' . config('app.name');
    $PageTitle = 'Reports List';
    $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => $PageTitle, 'url' => '']];
@endphp

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="{{ asset('/assets/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {

            const $reportType = $('#report_type');
            const $fuelMonth = $('#fuelMonth');
            const $startDate = $('#startDate');
            const $endDate = $('#endDate');

            function formatDateLocal(date) {
                const y = date.getFullYear();
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const d = String(date.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }


            function enableFuelMode() {
                $('#fuel-month-wrapper').removeClass('d-none');
                $('.date-range-wrapper').addClass('d-none');

                $startDate.prop('readonly', true);
                $endDate.prop('readonly', true);
            }

            function enableMisMode() {
                $('#fuel-month-wrapper').addClass('d-none');
                $('.date-range-wrapper').removeClass('d-none');

                $startDate.prop('readonly', false);
                $endDate.prop('readonly', false);
            }

            function applyFuelMonth(monthValue) {
                if (!monthValue) return;

                const [year, month] = monthValue.split('-').map(Number);
                const monthIndex = month - 1;

                const start = new Date(year, monthIndex, 1);

                let end;
                if (monthIndex === 11) {
                    end = new Date(year, monthIndex, 25); // December rule
                } else {
                    end = new Date(year, monthIndex + 1, 0); // last day
                }

                $startDate.val(formatDateLocal(start));
                $endDate.val(formatDateLocal(end));
            }

            // Report type change
            $reportType.on('change', function() {
                if ($(this).val() === 'fuel') {
                    enableFuelMode();
                } else {
                    enableMisMode();
                }
            });

            // -------- INIT ON PAGE LOAD --------
            if ($reportType.val() === 'fuel') {
                enableFuelMode();

                const now = new Date();
                const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;

                $fuelMonth.val(currentMonth);
                applyFuelMonth(currentMonth);
            }

            // Month selection for fuel
            $fuelMonth.on('change', function() {
                applyFuelMonth(this.value);
            });

        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">{{ $PageTitle }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        @foreach ($breadcrumbs as $breadcrumb)
                            <li class="breadcrumb-item">
                                @if (isset($breadcrumb['url']))
                                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                @else
                                    {{ $breadcrumb['name'] }}
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Filters</h5>
            <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse"
                aria-expanded="true" aria-controls="filtersCollapse">
                <i class="ri-arrow-down-s-line"></i> Toggle Filters
            </button>
        </div>
        <div id="filtersCollapse" class="collapse show">
            <div class="card-body">
                <form id="fuel-report-form" action="{{ route('reports.export') }}" method="POST">
                    @csrf
                    @method('post')

                    <div class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <select id="report_type" name="report_type" class="form-select" required>
                                <option value="fuel">Fuel</option>
                                <option value="mis">Delivery MIS</option>
                                <option value="driver_wise_summery">Driver Wise Summery</option>
                                <option value="date_wise_summery">Date Wise Summery</option>
                                <option value="plant_wise_summery">Plant Wise Summery</option>
                                <option value="customers_wise_summery">Customers Wise Summery</option>
                                <option value="failed_orders">Failed Order Generation</option>

                            </select>
                        </div>

                        <div class="col-md-3 d-none" id="fuel-month-wrapper">
                            <input type="month" id="fuelMonth" class="form-control">
                        </div>

                        <div class="col-md-3 date-range-wrapper">
                            <input type="date" id="startDate" name="start_date" class="form-control">
                        </div>

                        <div class="col-md-3 date-range-wrapper">
                            <input type="date" id="endDate" name="end_date" class="form-control">
                        </div>


                        <div class="col-md-3 text-end">
                            <button type="submit" id="export-btn" class="btn btn-primary">
                                <i class="ri-download-line me-1"></i> Export Report
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
