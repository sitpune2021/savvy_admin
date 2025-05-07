@extends('layouts.app')
@php
    $title = 'Dispensary - ' . config('app.name');
    $PageTitle = isset($Dispensary) ? ($show ? 'View Dispensary' : 'Edit Dispensary') : 'Create Dispensary';

    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Dispensary List', 'url' => url('/dispensary')],
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
            <form id="dispensaryForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $Dispensary->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item border-0 mb-0">
                            <div class="row align-item-center">

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Model Name</label>
                                        <input id="model_name" name="model_name" class="form-control"
                                            value="{{ old('model_name', $Dispensary->model_name ?? '') }}"
                                            @if ($show) disabled @endif >
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Serial Number</label>
                                        <input id="serial_number" name="serial_number" type="number" class="form-control"
                                            value="{{ old('serial_number', $Dispensary->serial_number ?? "") }}"
                                            @if ($show) disabled @endif >
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="machine_type">Type</label>
                                        <select id="machine_type" class="select js-example-basic-single" name="machine_type"
                                            @if ($show) disabled @endif>
                                            @foreach (['2_tab', '3_tab'] as $type)
                                                <option value="{{ $type }}"
                                                    {{ isset($Dispensary) && $Dispensary->type == $type ? 'selected' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                                </option>
                                            @endforeach

                                        </select>
                                        </ul>
                                    </div>
                                </div>

                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="customer_id">Select Customer</label>
                                        <select id="customer-select" class="select js-example-basic-single"
                                            name="customer_id" @if ($show) disabled @endif>
                                            <option value="">Choose Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    data-shippings='{{ $customer->shippingAddresses }}'
                                                    @if (isset($Dispensary) && $Dispensary->customer_id == $customer->id) selected @endif>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="shipping_id">Select shipping</label>
                                        <select id="shipping-select" class="select js-example-basic-single"
                                            name="shipping_id" @if ($show) disabled @endif>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Documents</label>
                                        @if (!$show)
                                            <input name="documents" type="file" class="form-control"
                                                placeholder="Enter Documents"
                                                value="{{ old('documents', $Dispensary->documents ?? '') }}"
                                                @if ($show) disabled @endif>
                                        @endif
                                            @if (isset($Dispensary->documents) && $Dispensary->documents)
                                                <a href="{{ asset('storage/dispensary/' . $Dispensary->documents) }}"
                                                    data-fancybox="gallery"
                                                    data-caption="{{ $Dispensary->documents }}">
                                                    <img src="{{ asset('storage/dispensary/' . $Dispensary->documents) }}"
                                                        alt="documents" width="100" height="100"
                                                        style="cursor: pointer;" class="img-thumbnail">
                                                </a>
                                            @endif
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Warranty</label>
                                        <input id="warranty" name="warranty" type="number" class="form-control"
                                            value="{{ old('warranty', $Dispensary->warranty ?? 1) }}"
                                            @if ($show) disabled @endif >
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Garanty</label>
                                        <input id="garanty" name="garanty" type="number" class="form-control"
                                            value="{{ old('garanty', $Dispensary->garanty ?? 1) }}"
                                            @if ($show) disabled @endif >
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-primary cancel me-2"
                                onclick="window.location='{{ route('order.index') }}'">Cancel</button>
                            @if (!$show)
                                <button type="submit"
                                    class="btn btn-primary">{{ isset($Order) ? 'Update' : 'Save' }}</button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <script>
        window.orderData = {
            shippingId: "{{ $Dispensary->shipping_id ?? '' }}",
            customerId: "{{ $Dispensary->customer_id ?? '' }}",

        };
        window.Laravel = {
            routeIndex: "{{ route('dispensary.index') }}"
        };
    </script>
@endsection
