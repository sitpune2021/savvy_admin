@extends('layouts.app')
@php
    $title = 'Lab Reports - ' . config('app.name');
    $PageTitle = isset($labReport) ? ($show ? 'View Lab Report' : 'Edit Lab Report') : 'Create Lab Report';

    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Lab Reports List', 'url' => url('/lab-reports')],
        ['name' => $PageTitle, 'url' => ''],
    ];
@endphp
@push('styles')
    <link href="{{ asset('/assets/libs/quill/quill.core.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/libs/quill/quill.bubble.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/libs/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@push('scripts')
    <script src="{{ asset('/assets/libs/quill/quill.min.js') }}"></script>
    <script src="{{ asset('/assets/js/pages/form-editor.init.js') }}"></script>

    <!--jquery cdn-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!--select2 cdn-->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('/assets/js/pages/select2.init.js') }}"></script>

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
    <div class="card mb-0">
        <div class="card-body">
            <form id="labReportForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $labReport->id ?? '' }}">
                <input type="hidden" name="parent_id" value="{{ $parentId ?? ($labReport->parent_id ?? null) }}">
                <div class="row">

                    <!-- Report Name -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Report Name</label>
                            <input name="report_name" type="text" class="form-control"
                                value="{{ old('report_name', $labReport->report_name ?? '') }}"
                                {{ $show ? 'disabled' : '' }} required>
                        </div>
                    </div>

                    <!-- Version -->
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label>Version</label>
                            <input type="text" class="form-control" value="v{{ $labReport->version_no ?? 1 }}" disabled>
                        </div>
                    </div>

                    <!-- Report Date -->
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label>Report Date</label>
                            <input name="report_date" type="date" class="form-control"
                                value="{{ old('report_date', isset($labReport->report_date) ? $labReport->report_date->format('Y-m-d') : '') }}"
                                {{ $show ? 'disabled' : '' }}>
                        </div>
                    </div>

                    <!-- Expiry Date -->
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label>Expiry Date</label>
                            <input name="expiry_date" type="date" class="form-control"
                                value="{{ old('expiry_date', isset($labReport->expiry_date) ? $labReport->expiry_date->format('Y-m-d') : '') }}"
                                {{ $show ? 'disabled' : '' }}>
                        </div>
                    </div>

                    <!-- File -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Upload File</label>

                            @if (!$show)
                                <input name="file" type="file" class="form-control"
                                    {{ !isset($labReport) ? 'required' : '' }}>
                            @endif

                            @if (isset($labReport) && $labReport->file_path)
                                <a href="{{ asset('storage/' . $labReport->file_path) }}" target="_blank">
                                    📄 View File
                                </a>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Buttons -->
                <div class="text-end">
                    <button type="button" class="btn btn-secondary"
                        onclick="window.location='{{ route('lab-reports.index') }}'">
                        Cancel
                    </button>

                    @if (!$show)
                        <button type="submit" class="btn btn-primary">
                            {{ isset($labReport) ? 'Update' : 'Save' }}
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
    <script>
        window.Laravel = {
            routeIndex: "{{ route('lab-reports.index') }}"
        };
    </script>
@endsection
