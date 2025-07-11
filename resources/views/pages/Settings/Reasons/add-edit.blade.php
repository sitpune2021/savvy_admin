@extends('layouts.app')
@php
    $title = 'Reasons - ' . config('app.name');
    $PageTitle = isset($Reason) ? ($show ? 'View Reasons' : 'Edit Reasons') : 'Create Reasons';
    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Reasons List', 'url' => url('/reasons')],
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
            <form id="reasonForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $Reason->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item">
                            <div class="row align-item-center">
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>For</label>
                                        <select class="select js-example-basic-single" name="for" id="for"
                                            @if ($show) disabled @endif>
                                            <option value="">Select Role</option>
                                            @foreach (['client', 'driver', 'vendor','plant-manager'] as $role)
                                                <option value="{{ $role }}"
                                                    {{ isset($Reason) && $Reason->for == $role ? 'selected' : '' }}>
                                                    {{ $role }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>reasons</label>
                                        @if ($show)
                                            <div name="reasons" class="form-control ql-editor" rows="10"
                                                style=" background-color: var(--vz-tertiary-bg); opacit: 1;">
                                                {!! $Reason->reasons !!}</div>
                                        @else
                                            <div name="reasons" class="form-control snow-editor" rows="10"
                                                data-input-id="reasons"
                                                placeholder="Enter additional details about the plant">
                                                {{ old('reasons', $Reason->reasons ?? '') }}</div>
                                            <input type="hidden" name="reasons" id="reasons"
                                                value="{{ old('reasons', $Reason->reasons ?? '') }}">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-primary cancel me-2"
                            onclick="window.location='{{ route('reasons.index') }}'">Cancel</button>
                        @if (!$show)
                            <button type="submit" class="btn btn-primary">{{ isset($Plant) ? 'Update' : 'Save' }}</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        window.Laravel = {
            routeIndex: "{{ route('reasons.index') }}"
        };
    </script>
@endsection
