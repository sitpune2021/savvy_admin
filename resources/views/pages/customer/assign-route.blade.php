@extends('layouts.app')
@php
    $title = 'Assign Routes - ' . config('app.name');
    $PageTitle = 'Assign Routes';

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
            <form id="assignRoutesForm" enctype="multipart/form-data">

                <div class="row">
                    <div class="col-md-12">
                        @foreach ($shippingAddresses as $key => $shippingAddresse)
                            @php
                                // Try to find the matching order where shipping_id matches current shipping address
                                $matchedOrder = collect($orders)->firstWhere('shipping_id', $shippingAddresse->id);
                            @endphp

                            @if ($matchedOrder)
                                {{-- Hidden field with the order ID --}}
                                <input type="hidden" name="order[{{ $key }}][id]"
                                    value="{{ $matchedOrder['id'] }}">
                                <input type="hidden" name="order[{{ $key }}][shipping_id]"
                                    value="{{ $shippingAddresse->id }}">
                            @endif
                            <div class="form-group-item border-0 mb-3">
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
                                    <h5 class="mb-2">Shipping Address {{ $key + 1 }} :
                                        {{ $shippingAddresse->shipping_address }}</h5>
                                </div>
                                <div class="row align-item-center ">
                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                        <div class="input-block mb-3">
                                            <label for="order[{{ $key }}][route_id]">Select Routes</label>
                                            <select class="select js-example-basic-single"
                                                name="order[{{ $key }}][route_id]"@if ($show) disabled @endif>
                                                <option>Choose routes</option>
                                                @foreach ($routes as $route)
                                                    <option value="{{ $route->id }}"
                                                        @if (isset($shippingAddresse) && $shippingAddresse->route_id == $route->id) selected @endif>
                                                        {{ $route->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary"> Save</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <script>
        window.Laravel = {
            routeIndex: "{{ route('order.index') }}"
        };
    </script>
@endsection
