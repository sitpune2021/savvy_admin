@extends('layouts.app')
@php
    $title = 'Drivers - ' . config('app.name');
    $PageTitle = isset($Customer) ? ($show ? 'View Drivers' : 'Edit Drivers') : 'Create Drivers';

    // $breadcrumbs = [
    //     ['title' => 'Home', 'url' => url('/')],
    //     ['title' => 'Drivers', 'url' => route('skills.index')],
    //     ['title' => $PageTitle, 'url' => null],
    // ];

@endphp
@section('content')
    <div class="card mb-0">
        <div class="card-body">
            <div class="content-page-header">
                <h5>{{ $PageTitle }}</h5>
            </div>
            <form   id="customerForm" enctype="multipart/form-data">
            <input type="hidden" id="id" name="id" value="{{ $Customer->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item">
                            <h5 class="form-title">Basic Details</h5>
                            <div class="row align-item-center">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Name</label>
                                        <input name="name" type="text" class="form-control" value="{{ old('name', $Customer->name ?? '') }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>E-mail</label>
                                        <input name="email" type="email" class="form-control" value="{{ old('email', $Customer->email ?? '') }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Phone No</label>
                                        <input name="phone_no" type="number" class="form-control" value="{{ old('phone_no', $Customer->phone_no ?? '' ) }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group-item">
                            <h5 class="form-title">Billing Address</h5>
                            <div class="row align-item-center">
                                <div class="col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Address</label>
                                        <input name="billing_address" type="text" class="form-control" value="{{ old('billing_address', $Customer->billing_address ?? '') }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Country</label>
                                        <input name="billing_country" type="text" class="form-control" value="{{ old('billing_country', $Customer->billing_country ?? '') }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>State</label>
                                        <input name="billing_state" type="text" class="form-control" value="{{ old('billing_state', $Customer->billing_state ?? '' ) }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>City</label>
                                        <input name="billing_city" type="text" class="form-control" value="{{ old('billing_city', $Customer->billing_city ?? '') }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Pin Code</label>
                                        <input name="billing_pincode" type="number" class="form-control" value="{{ old('billing_pincode', $Customer->billing_pincode ?? '' ) }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="form-group-item">
                            <h5 class="form-title">Shipping Address</h5>
                            <div class="row align-item-center">
                                <div class="col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Address</label>
                                        <input name="shipping_address" type="text" class="form-control" value="{{ old('shipping_address', $Customer->shipping_address ?? '') }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Country</label>
                                        <input name="shipping_country" type="text" class="form-control" value="{{ old('shipping_country', $Customer->shipping_country ?? '') }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>State</label>
                                        <input name="shipping_state" type="text" class="form-control" value="{{ old('shipping_state', $Customer->shipping_state ?? '' ) }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>City</label>
                                        <input name="shipping_city" type="text" class="form-control" value="{{ old('shipping_city', $Customer->shipping_city ?? '') }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Pin Code</label>
                                        <input name="shipping_pincode" type="number" class="form-control" value="{{ old('shipping_pincode', $Customer->shipping_pincode ?? '' ) }}" 
                                        @if($show) disabled @endif >
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        <!--<div class="form-group-item">-->
                        <!--    <h5 class="form-title">Basic Details</h5>-->
                        <!--    <div class="row align-item-center">-->
                        <!--        <div class="col-lg-4 col-md-6 col-sm-12">-->
                        <!--            <div class="input-block mb-3">-->
                        <!--                <label>Name</label>-->
                        <!--                <input name="name" type="text" class="form-control" value="{{ old('name', $Customer->name ?? '') }}" -->
                        <!--                @if($show) disabled @endif required>-->
                        <!--            </div>-->
                        <!--        </div>-->
                        <!--        <div class="col-lg-4 col-md-6 col-sm-12">-->
                        <!--            <div class="input-block mb-3">-->
                        <!--                <label>E-mail</label>-->
                        <!--                <input name="email" type="email" class="form-control" value="{{ old('email', $Customer->email ?? '') }}" -->
                        <!--                @if($show) disabled @endif required>-->
                        <!--            </div>-->
                        <!--        </div>-->
                        <!--        <div class="col-lg-4 col-md-6 col-sm-12">-->
                        <!--            <div class="input-block mb-3">-->
                        <!--                <label>Phone No</label>-->
                        <!--                <input name="phone_no" type="number" class="form-control" value="{{ old('phone_no', $Customer->phone_no ?? '' ) }}" -->
                        <!--                @if($show) disabled @endif required>-->
                        <!--            </div>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->

                        <div class="text-end">
                            <button type="button" class="btn btn-primary cancel me-2" onclick="window.location='{{ route('customer.index') }}'">Cancel</button>
                            @if(!$show)
                                <button type="submit" class="btn btn-primary">{{ isset($Customer) ? 'Update' : 'Save' }}</button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <script>
        window.Laravel = {
            routeIndex: "{{ route('customer.index') }}"
        };
    </script>
    
@endsection
