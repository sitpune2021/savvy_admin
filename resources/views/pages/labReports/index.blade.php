@extends('layouts.app')
@php
    $title = 'Lab Reports - ' . config('app.name');
    $PageTitle = 'Lab Reports List';
    $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => $PageTitle, 'url' => '']];
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
@endpush
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="{{ asset('/assets/js/pages/datatables.init.js') }}"></script>
    <script src="{{ asset('/assets/js/app.js') }}"></script>
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
        <div class="card-body">
            <div class="row g-2">
                <div class="col-sm-auto ms-auto">
                    <div class="list-grid-nav hstack gap-1">
                        <a class="btn btn-success" href="{{ route('lab-reports.create') }}">
                            <i class="ri-add-fill me-1 align-bottom"></i> Add
                            Lab Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="buttons-datatables" class="display table table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr. No</th>
                                    <th>Report Name</th>
                                    <th>Version</th>
                                    <th>Report Date</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($labReports as $labReport)
                                    @php
                                        $latest = $labReport->latestVersion();
                                    @endphp

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <!-- Name -->
                                        <td>{{ $labReport->report_name }}</td>

                                        <!-- Latest Version -->
                                        <td>v{{ $latest->version_no ?? 1 }}</td>

                                        <!-- Dates -->
                                        <td>{{ optional($latest->report_date)->format('d-m-Y') }}</td>
                                        <td>{{ optional($latest->expiry_date)->format('d-m-Y') }}</td>

                                        <!-- Status -->
                                        <td>
                                            @if ($latest && $latest->isExpired())
                                                <span class="badge bg-danger">Expired</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>

                                        <!-- Actions -->
                                        <td>
                                            <div class="hstack gap-3 flex-wrap">

                                                <!-- View Versions -->
                                                <a href="{{ route('lab-reports.show', $labReport->id) }}"
                                                    class="link-primary fs-15">
                                                    <i class="ri-eye-line"></i>
                                                </a>

                                                <!-- Upload New Version -->
                                                <a href="{{ route('lab-reports.create', ['parent_id' => $labReport->id]) }}"
                                                    class="link-success fs-15">
                                                    <i class="ri-upload-2-line"></i>
                                                </a>

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
