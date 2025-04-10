@extends('layouts.app')
@php
    $title = 'Customers - ' . config('app.name');
    $PageTitle = isset($Customer) ? ($show ? 'View Customers' : 'Edit Customers') : 'Create Customers';
    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Customers List', 'url' => url('/customer')],
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
    {{-- <div class="card mb-0">
        <div class="card-body"> --}}
    <form id="customerForm" enctype="multipart/form-data">
        <input type="hidden" id="id" name="id" value="{{ $Customer->id ?? null }}">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group-item card">
                    <div class="card-header">
                        <h5 class="form-title">Basic Details</h5>
                    </div>
                    <div class="row align-item-center card-body">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Customer Zoho Id</label>
                                <input name="customer_zohi_id" type="text" class="form-control"
                                    placeholder="Enter Customer Zoho Id"
                                    value="{{ old('customer_zohi_id', $Customer->customer_zohi_id ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Plant</label>
                                <select class="select js-example-basic-single" name="plant_id" id="plant_id"
                                    @if ($show) disabled @endif>
                                    <option value="">Select Plant</option>
                                    @foreach ($plants as $plant)
                                        <option value="{{ $plant->id }}"
                                            {{ isset($Customer) && $Customer->plant_id == $plant->id ? 'selected' : '' }}>
                                            {{ $plant->name }}</option>
                                    @endforeach
                                </select>
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


                <div class="form-group-item card">
                    <div class="card-header">
                        <h5 class="form-title">Shipping Address</h5>
                    </div>
                    <div class="row align-item-center  card-body">
                        <div class="col-sm-12">
                            <div class="input-block mb-3">
                                <label>Address</label>
                                <input name="shipping_address" type="text" class="form-control"
                                    placeholder="Enter Shipping Address"
                                    value="{{ old('shipping_address', $Customer->shipping_address ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Country</label>
                                <input name="shipping_country" type="text" class="form-control"
                                    placeholder="Enter Shipping Country"
                                    value="{{ old('shipping_country', $Customer->shipping_country ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>State</label>
                                <input name="shipping_state" type="text" class="form-control"
                                    placeholder="Enter Shipping State"
                                    value="{{ old('shipping_state', $Customer->shipping_state ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>City</label>
                                <input name="shipping_city" type="text" class="form-control"
                                    placeholder="Enter Shipping City"
                                    value="{{ old('shipping_city', $Customer->shipping_city ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Pin Code</label>
                                <input name="shipping_pincode" type="number" class="form-control"
                                    placeholder="Enter Shipping Pin Code"
                                    value="{{ old('shipping_pincode', $Customer->shipping_pincode ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group-item card">
                    <div class="card-header">
                        <h5 class="form-title">Contact Person</h5>
                    </div>
                    <div class="row align-item-center  card-body">
                        <div class=" col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Name</label>
                                <input name="contact_person" type="text" class="form-control"
                                    placeholder="Enter Name"
                                    value="{{ old('contact_person', $Customer->contact_person ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class=" col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Mobile No</label>
                                <input name="contact_person_phone" type="text" class="form-control"
                                    placeholder="Enter Mobile No"
                                    value="{{ old('contact_person_phone', $Customer->contact_person_phone ?? '') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="form-group-item card">
                    <div class="card-header">
                        <h5 class="form-title">Machine Details</h5>
                    </div>
                    <div class="row align-item-center  card-body">
                        <div class=" col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Deployed</label>
                                <select class="select js-example-basic-single" name="machine_deployed"
                                    @if ($show) disabled @endif>
                                    <option value="Yes" @if (isset($Customer) && $Customer->machine_deployed == 'Yes') selected @endif>
                                        Yes
                                    </option>
                                    <option value="No" @if (isset($Customer) && $Customer->machine_deployed == 'No') selected @endif>
                                        No
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="form-group-item card mb-2">
                    <div class="card-header">
                        <h5 class="form-title">Contract Details</h5>
                    </div>
                    <div class="row align-item-center  card-body">
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>product</label>
                                <select class="select js-example-basic-single" name="product_id"
                                    @if ($show) disabled @endif>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}"
                                            {{ isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Quantity</label>
                                <input name="quantity" type="number" class="form-control" placeholder="Enter Quantity"
                                    value="{{ old('quantity', $Customer->contracts[0]->quantity ?? 1) }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Price {{ '/-' }}</label>
                                <input name="price" type="number" class="form-control" placeholder="Enter Price"
                                    value="{{ old('price', $Customer->contracts[0]->price ?? 1) }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Duration</label>
                                <input name="duration" type="number" class="form-control" placeholder="Enter Duration"
                                    value="{{ old('duration', $Customer->contracts[0]->duration ?? 1) }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Duration Type</label>
                                <select class="select js-example-basic-single" name="duration_type"
                                    @if ($show) disabled @endif>
                                    <option value="years" @if (isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->duration_type == 'years') selected @endif>
                                        Years
                                    </option>
                                    <option value="months" @if (isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->duration_type == 'months') selected @endif>
                                        Months
                                    </option>
                                    <option value="weeks" @if (isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->duration_type == 'weeks') selected @endif>
                                        Weeks
                                    </option>
                                    <option value="days" @if (isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->duration_type == 'days') selected @endif>
                                        Days
                                    </option>

                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>Delivery Frequency</label>
                                <select class="select js-example-basic-single" name="delivery_frequency">
                                    <option value="daily" @if (isset($Customer) && count($Customer->contracts) > 0 && $Customer->contracts[0]->delivery_frequency == 'daily') selected @endif>
                                        Daily
                                    </option>
                                    <option value="alternate_day" @if (isset($Customer) &&
                                            count($Customer->contracts) > 0 &&
                                            $Customer->contracts[0]->delivery_frequency == 'alternate_day') selected @endif>
                                        Alternate Day
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="input-block mb-3">
                                <label>delivery_time</label>
                                <input name="delivery_time" type="time" class="form-control"
                                    placeholder="Enter Delivery Time"
                                    value="{{ old('delivery_time', !empty($Customer->contracts[0]->delivery_time) ? date('H:i', strtotime($Customer->contracts[0]->delivery_time)) : '08:00') }}"
                                    @if ($show) disabled @endif>
                            </div>
                        </div>
                    </div>

                    <div class="text-end m-3 mt-0">
                        <button type="button" class="btn btn-primary cancel me-2"
                            onclick="window.location='{{ route('customer.index') }}'">Cancel</button>
                        @if (!$show)
                            <button type="submit"
                                class="btn btn-primary">{{ isset($Customer) ? 'Update' : 'Save' }}</button>
                        @endif
                    </div>
                </div>
            </div>
    </form>
    {{-- </div>
    </div> --}}
    <script>
        window.Laravel = {
            routeIndex: "{{ route('customer.index') }}"
        };
    </script>
@endsection
