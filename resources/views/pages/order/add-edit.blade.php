@extends('layouts.app')
@php
    $title = 'Order - ' . config('app.name');
    $PageTitle = isset($Order) ? ($show ? 'View Order' : 'Edit Order') : 'Create Order';

    // $breadcrumbs = [
    //     ['title' => 'Home', 'url' => url('/')],
    //     ['title' => 'Skill', 'url' => route('skills.index')], // Changed "user" to "skill"
    //     ['title' => $PageTitle, 'url' => null], // Current page without URL
    // ];

@endphp
@section('content')
    <div class="card mb-0">
        <div class="card-body">
            <div class="content-page-header">
                <h5>{{ $PageTitle }}</h5>
            </div>
            <form   id="orderForm" enctype="multipart/form-data">
            <input type="hidden" id="id" name="id" value="{{ $Order->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item border-0 mb-0">
                            <div class="row align-item-center">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="customer_id">Select Customer</label>
                                        {{-- <ul class="form-group-plus css-equal-heights">
                                        <li> --}}
                                            <select class="select" name="customer_id" @if($show) disabled @endif>
                                                <option>Choose Customer</option>
                                                @foreach ($customers as $customer)
                                                    <option value="{{ $customer->id }}" 
                                                        @if(isset($Order) && $Order->customer_id == $customer->id) selected @endif>
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

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label for="driver_id" >Select Driver</label>
                                        {{-- <ul class="form-group-plus css-equal-heights">
                                        <li> --}}
                                        <select class="select" name="driver_id"@if($show) disabled @endif>
                                            <option>Choose Driver</option>
                                            @foreach ($drivers as $driver)
                                                <option value="{{ $driver->id }}" 
                                                    @if(isset($Order) && $Order->driver_id == $driver->id) selected @endif>
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
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Quantity</label>
                                        <input name="quantity" type="number" class="form-control" value="{{ old('quantity', $Order->quantity ?? 1) }}" 
                                        @if($show) disabled @endif required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="order_details" class="form-control" value="order_details">

                        <div class="text-end">
                            <button type="button" class="btn btn-primary cancel me-2" onclick="window.location='{{ route('order.index') }}'">Cancel</button>
                            @if(!$show)
                                <button type="submit" class="btn btn-primary">{{ isset($Order) ? 'Update' : 'Save' }}</button>
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
