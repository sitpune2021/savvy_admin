@extends('layouts.app')
@php
    $title = 'Order - ' . config('app.name');
    $PageTitle = isset($assign)
        ? 'Assign Driver'
        : (isset($Order)
            ? ($show
                ? 'View Order'
                : 'Edit Order')
            : 'Create Order');

@endphp
@section('content')
    <div class="card mb-0">
        <div class="card-body">
            <div class="content-page-header">
                <h5>{{ $PageTitle }}</h5>
            </div>
            <form id="orderForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $Order->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item border-0 mb-0">
                            <div class="row align-item-center">
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="customer_id">Select Customer</label>
                                        {{-- <ul class="form-group-plus css-equal-heights">
                                        <li> --}}
                                        @if (isset($assign))
                                            <input type="hidden" name="customer_id"
                                                value="{{ $Order->customer_id ?? null }}">
                                            <input type="hidden" name="develivered_qty"
                                                value="{{ $Order->develivered_qty ?? null }}">
                                            <input type="hidden" name="return_qty"
                                                value="{{ $Order->return_qty ?? null }}">
                                            <input type="hidden" name="status" value="{{ $Order->status ?? null }}">
                                        @endif
                                        <select class="select" name="customer_id"
                                            @if ($show) disabled @endif
                                            @if (isset($assign)) disabled @endif>
                                            <option>Choose Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    @if (isset($Order) && $Order->customer_id == $customer->id) selected @endif>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        {{-- </li>
                                        <li>    
                                            <a class="btn btn-primary form-plus-btn" href="add-customer.html"><i class="fas fa-plus-circle"></i></a>
                                        </li>
                                    </ul>												 --}}
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="driver_id">Select Driver</label>
                                        {{-- <ul class="form-group-plus css-equal-heights">
                                        <li> --}}
                                        <select class="select"
                                            name="driver_id"@if ($show) disabled @endif>
                                            <option>Choose Driver</option>
                                            @foreach ($drivers as $driver)
                                                <option value="{{ $driver->id }}"
                                                    @if (isset($Order) && $Order->driver_id == $driver->id) selected @endif>
                                                    {{ $driver->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        {{-- </li>
                                        <li>    
                                            <a class="btn btn-primary form-plus-btn" href="add-customer.html"><i class="fas fa-plus-circle"></i></a>
                                        </li>
                                    </ul>												 --}}
                                    </div>
                                </div>
                                @if (isset($Order))
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Delivered Quantity</label>
                                        <input name="develivered_qty" type="number" class="form-control"
                                            value="{{ old('develivered_qty', $Order->develivered_qty ?? 1) }}"
                                            @if ($show) disabled @endif required
                                            @if (isset($assign)) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Return Quantity</label>
                                        <input name="return_qty" type="number" class="form-control"
                                            value="{{ old('return_qty', $Order->return_qty ?? 0) }}"
                                            @if ($show) disabled @endif required
                                            @if (isset($assign)) disabled @endif>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @if (!isset($Order))
                        <div class="form-group-item border-0 mb-0">
                            <h5 class="form-title">Contract Details</h5>
                            <div class="row align-item-center">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>product</label>
                                        <select class="select" name="product_id"
                                            @if ($show) disabled @endif>
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ isset($Customer) && $Customer->contracts[0]->product_id == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Quantity</label>
                                        <input name="quantity" type="number" class="form-control"
                                            value="{{ old('quantity', $Customer->contracts[0]->quantity ?? 1) }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Price {{ '/-' }}</label>
                                        <input name="price" type="number" class="form-control"
                                            value="{{ old('price', $Customer->contracts[0]->price ?? 1) }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Duration</label>
                                        <input name="duration" type="number" class="form-control"
                                            value="{{ old('duration', $Customer->contracts[0]->duration ?? 1) }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Duration Type</label>
                                        <select class="select" name="duration_type"
                                            @if ($show) disabled @endif>
                                            <option value="days" @if (isset($Customer) && $Customer->contracts[0]->duration_type == 'days') selected @endif>
                                                Days
                                            </option>
                                            <option value="weeks" @if (isset($Customer) && $Customer->contracts[0]->duration_type == 'weeks') selected @endif>
                                                Weeks
                                            </option>
                                            <option value="months" @if (isset($Customer) && $Customer->contracts[0]->duration_type == 'months') selected @endif>
                                                Months
                                            </option>
                                            <option value="years" @if (isset($Customer) && $Customer->contracts[0]->duration_type == 'years') selected @endif>
                                                Years
                                            </option>
                                            

                                        </select>
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>delivery_time</label>
                                        <input name="delivery_time" type="time" class="form-control"
                                            value="{{ old('delivery_time', !empty($Customer->contracts[0]->delivery_time) ? date('H:i', strtotime($Customer->contracts[0]->delivery_time)) : '08:00') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                            </div>

                        </div>
                        @endif

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
        window.Laravel = {
            routeIndex: "{{ route('order.index') }}"
        };
    </script>

@endsection
