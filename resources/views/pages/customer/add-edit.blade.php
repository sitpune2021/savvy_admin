@extends('layouts.app')
@php
    $title = 'Customers - ' . config('app.name');
    $PageTitle = isset($Customer) ? ($show ? 'View Customers' : 'Edit Customers') : 'Create Customers';
    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Customers List', 'url' => url('/customer')],
        ['name' => $PageTitle, 'url' => ''],
    ];
    $selectedDays = [];
    if (isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->days) {
        $selectedDays = explode('|', $Customer->contracts[0]->days);
    }
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

    <form id="customerForm" enctype="multipart/form-data">
        <input type="hidden" id="id" name="id" value="{{ $Customer->id ?? null }}">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group-item card">
                    <div class="card-header">
                        <h5 class="form-title">Basic Details</h5>
                    </div>
                    <div class="row align-item-center card-body">
                        <div class=" col-sm-12">
                            <div class="input-block mb-3">
                                <label>Customer Zoho Id</label>
                                <input name="customer_zohi_id" type="text" class="form-control"
                                    placeholder="Enter Customer Zoho Id"
                                    value="{{ old('customer_zohi_id', $Customer->customer_zohi_id ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Name</label>
                                <input name="name" type="text" class="form-control" placeholder="Enter Customer Name"
                                    value="{{ old('name', $Customer->name ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>E-mail</label>
                                <input name="email" type="email" class="form-control"
                                    placeholder="Enter Customer E-mail" value="{{ old('email', $Customer->email ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Phone No</label>
                                <input name="phone_no" type="number" class="form-control"
                                    placeholder="Enter Customer Phone No"
                                    value="{{ old('phone_no', $Customer->phone_no ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group-item card">
                    <div class="card-header">
                        <h5 class="form-title">Billing Address</h5>
                    </div>
                    <div class="row align-item-center  card-body">
                        <div class="col-sm-12">
                            <div class="input-block mb-3">
                                <label>Address</label>
                                <input name="billing_address" type="text" class="form-control"
                                    placeholder="Enter Billing Address"
                                    value="{{ old('billing_address', $Customer->billing_address ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Country</label>
                                <input name="billing_country" type="text" class="form-control"
                                    placeholder="Enter Billing Country"
                                    value="{{ old('billing_country', $Customer->billing_country ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>State</label>
                                <input name="billing_state" type="text" class="form-control"
                                    placeholder="Enter Billing State"
                                    value="{{ old('billing_state', $Customer->billing_state ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>City</label>
                                <input name="billing_city" type="text" class="form-control"
                                    placeholder="Enter Billing City"
                                    value="{{ old('billing_city', $Customer->billing_city ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Pin Code</label>
                                <input name="billing_pincode" type="number" class="form-control"
                                    placeholder="Enter Billing Pin Code"
                                    value="{{ old('billing_pincode', $Customer->billing_pincode ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                    </div>
                </div>
                @if (!isset($Customer))
                    <div id="shipping_address_div">
                        <div class="form-group-item card address-block">
                            <div class="card-header d-flex justify-content-between align-items-center add-remove">
                                <h5 class="form-title">Shipping Address</h5>
                                <button type="button" class="btn btn-sm btn-success" id="add-address">
                                    + Add Location
                                </button>
                            </div>
                            <div class="row align-item-center card-body">
                                <div class="col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Address</label>
                                        <input name="shipping[0][shipping_address]" type="text" class="form-control"
                                            placeholder="Enter Shipping Address">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Country</label>
                                        <input name="shipping[0][shipping_country]" type="text" class="form-control"
                                            placeholder="Enter Shipping Country">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>State</label>
                                        <input name="shipping[0][shipping_state]" type="text" class="form-control"
                                            placeholder="Enter Shipping State">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>City</label>
                                        <input name="shipping[0][shipping_city]" type="text" class="form-control"
                                            placeholder="Enter Shipping City">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Pin Code</label>
                                        <input name="shipping[0][shipping_pincode]" type="number" class="form-control"
                                            placeholder="Enter Shipping Pin Code">
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Plant</label>
                                        <select class="select js-example-basic-single" name="shipping[0][plant_id]"
                                            id="plant_id" @if ($show) disabled @endif>
                                            <option value="">Select Plant</option>
                                            @foreach ($plants as $plant)
                                                <option value="{{ $plant->id }}"
                                                    {{ isset($Customer) && $Customer->plant_id == $plant->id ? 'selected' : '' }}>
                                                    {{ $plant->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Routes</label>
                                        <select name="shipping[0][route_id]" class="select js-example-basic-single"
                                            id="route_id" @if ($show) disabled @endif>
                                            <option value="">Select Route</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Drivers</label>
                                        <select name="shipping[0][driver_id]" class="select js-example-basic-single"
                                            id="driver_id" @if ($show) disabled @endif>
                                            <option value="">Select Driver</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Name</label>
                                        <input name="shipping[0][contact_person]" type="text" class="form-control"
                                            placeholder="Enter Name">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Mobile No</label>
                                        <input name="shipping[0][contact_person_phone]" type="text"
                                            class="form-control" placeholder="Enter Mobile No">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Deployed</label>
                                        <select class="select js-example-basic-single"
                                            name="shipping[0][machine_deployed]">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Product</label>
                                        <select class="select js-example-basic-single" name="contract[0][product_id]"
                                            @if ($show) disabled @endif>
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->product_id == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
        
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Quantity</label>
                                        <input name="contract[0][quantity]" type="number" class="form-control" placeholder="Enter Quantity"
                                            value="{{ old('quantity', $Customer->contracts[0]->quantity ?? 1) }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
        
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Price {{ '/-' }}</label>
                                        <input name="contract[0][price]" type="number" class="form-control" placeholder="Enter Price"
                                            value="{{ old('price', $Customer->contracts[0]->price ?? 1) }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
        
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Delivery Frequency</label>
                                        <select class="select js-example-basic-single" name="contract[0][frequency]" id="frequency">
                                            @foreach (['daily', 'alternate_day', 'weekly', 'twice_per_week', 'random'] as $freq)
                                                <option value="{{ $freq }}"
                                                    {{ isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->frequency == $freq ? 'selected' : '' }}>
                                                    {{ ucwords(str_replace('_', ' ', $freq)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
        
                                <div class="col-lg-4 col-md-6 col-sm-12" id="frequency_count">
                                    <div class="input-block mb-3">
                                        <label>Frequency Count</label>
                                        <input name="contract[0][frequency_count]" type="number" class="form-control"
                                            value="1">
                                    </div>
                                </div>
        
                                <div class="col-lg-4 col-md-6 col-sm-12" id="days_select">
                                    <div class="input-block mb-3">
                                        <label>Delivery Day</label>
                                        <select class="select js-example-basic-single" name="contract[0][days][]"
                                                @if ($show) disabled @endif multiple>
                                            @foreach (['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                                                <option value="{{ $day }}" {{ in_array($day, $selectedDays) ? 'selected' : '' }}>
                                                    {{ ucfirst($day) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
        
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Duration</label>
                                        <input name="contract[0][duration]" type="number" class="form-control" placeholder="Enter Duration"
                                            value="{{ old('duration', $Customer->contracts[0]->duration ?? 1) }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
        
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Duration Type</label>
                                        <select class="select js-example-basic-single" name="contract[0][duration_type]"
                                            @if ($show) disabled @endif>
                                            @foreach (['years', 'months', 'weeks', 'days'] as $type)
                                                <option value="{{ $type }}"
                                                    {{ isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->duration_type == $type ? 'selected' : '' }}>
                                                    {{ ucfirst($type) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="form-group-item card mb-2">
                    <div class="text-end m-3">
                        <button type="button" class="btn btn-primary cancel me-2"
                            onclick="window.location='{{ route('customer.index') }}'">Cancel</button>
                        @if (!$show)
                            <button type="submit" class="btn btn-primary">
                                {{ isset($Customer) ? 'Update' : 'Save' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
    @if (isset($Customer))
        <div class="row">
            <div class="col-md-12">
                <div class="form-group-item card">
                    <div class="card-header d-flex justify-content-between align-items-center add-remove">
                        <h5 class="form-title">Shipping Address</h5>
                        @if (!$show)
                            <button type="button" class="btn btn-sm btn-success" id="add-address-edit">
                                + Add Location
                            </button>
                        @endif
                    </div>
                    <div class="row align-item-center card-body">
                        <div class="table-responsive mt-4 mt-xl-0">
                            <table class="table table-striped table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Address</th>
                                        <th>Person</th>
                                        <th>No.</th>
                                        @if (!$show)
                                            <th>Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($Customer->shippingAddresses as $index => $shippingAddress)
                                        <tr data-index="{{ $index }}">
                                            <td class="fw-medium">{{ $index + 1 }}</td>
                                            <td>{{ $shippingAddress->shipping_address }}</td>
                                            <td>{{ $shippingAddress->contact_person }}</td>
                                            <td>{{ $shippingAddress->contact_person_phone }}</td>
                                            @if (!$show)
                                                <td>
                                                    <div class="hstack gap-3 flex-wrap">
                                                        <a href="javascript:void(0);"
                                                            class="link-success fs-15 edit-address"
                                                            data-address='@json($shippingAddress)'
                                                            data-contract='@json($shippingAddress->Contract)'
                                                            >
                                                            <i class="ri-edit-2-line"></i>
                                                        </a>
                                                        <a href="javascript:void(0);"
                                                            class="link-danger fs-15 remove-address">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (!$show)
            <form id="shippingForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12">
                        <div id="address-container">
                            {{-- Dynamic address blocks injected here --}}
                        </div>
                    </div>
                </div>
            </form>
        @endif
    @endif



    <script>
        window.show = @json($show);
        window.plants = @json($plants);
        window.products = @json($products);
        window.routeData = @json($routes);
        window.driverData = @json($drivers);
        window.locationData = false;

        window.Laravel = {
            routeIndex: "{{ route('customer.index') }}"
        };
    </script>
@endsection
