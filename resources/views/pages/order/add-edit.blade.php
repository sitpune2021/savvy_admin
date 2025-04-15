@extends('layouts.app')
@php
    $title = 'Order - ' . config('app.name');
    $PageTitle = isset($Order) ? ($show ? 'View Order' : 'Edit Order') : 'Create Order';

    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Order List', 'url' => url('/order')],
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
            <form id="orderForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $Order->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item border-0 mb-0">
                            <div class="row align-item-center">
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="customer_id">Select Customer</label>
                                        <select id="customer-select" class="select js-example-basic-single"
                                            name="customer_id" @if ($show) disabled @endif>
                                            <option value="">Choose Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    data-contracts='{{$customer->contracts}}'
                                                    data-shippings='{{$customer->shippingAddresses}}'
                                                    @if (isset($Order) && $Order->customer_id == $customer->id) selected @endif>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        </ul>
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="contract_id">Select Contract</label>
                                        <select id="contract-select" class="select js-example-basic-single"
                                            name="contract_id" @if ($show) disabled @endif>
                                            <option value="">Choose Contract</option>
                                        </select>
                                        </ul>
                                    </div>
                                </div>


                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="shipping_id">Select shipping</label>
                                        <select id="shipping-select" class="select js-example-basic-single"
                                            name="shipping_id[]" @if ($show) disabled @endif multiple>
                                            <option value="">Choose Shipping</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="route_id">Select Route</label>
                                        <select class="select js-example-basic-single"
                                            name="route_id"@if ($show) disabled @endif>
                                            <option>Choose Route</option>
                                            @foreach ($routes as $route)
                                                <option value="{{ $route->id }}"
                                                    @if (isset($Order) && $Order->route_id == $route->id) selected @endif>
                                                    {{ $route->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Delivered Quantity</label>
                                        <input id="delivered-qty" name="develivered_qty" type="number" class="form-control"
                                            value="{{ old('develivered_qty', $Order->develivered_qty ?? 1) }}"
                                            @if ($show) disabled @endif required>
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
            contractId: "{{ $Order->contract_id ?? '' }}",
            shippingId: "{{ $Order->shipping_id ?? '' }}",
            customerId: "{{ $Order->customer_id ?? '' }}"
        };
        window.Laravel = {
            routeIndex: "{{ route('order.index') }}"
        };
    </script>
@endsection
