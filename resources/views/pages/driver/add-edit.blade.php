@extends('layouts.app')
@php
    $title = 'Drivers - ' . config('app.name');
    $PageTitle = isset($Driver) ? ($show ? 'View Drivers' : 'Edit Drivers') : 'Create Drivers';
    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Drivers List', 'url' => url('/driver')],
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
            <form id="driverForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $Driver->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item">
                            <div class="row align-item-center">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Name</label>
                                        <input name="name" type="text" class="form-control" placeholder="Enter Name"
                                            value="{{ old('name', $Driver->name ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>E-mail</label>
                                        <input name="email" type="email" class="form-control" placeholder="Enter E-mail"
                                            value="{{ old('email', $Driver->email ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Phone No</label>
                                        <input name="phone_no" type="number" class="form-control" placeholder="Enter  Phone No"
                                            value="{{ old('phone_no', $Driver->phone_no ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group-item">
                            <div class="row align-item-center">
                                <div class="col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Address</label>
                                        <input name="full_address" type="text" class="form-control" placeholder="Enter Address"
                                            value="{{ old('full_address', $Driver->full_address ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Country</label>
                                        <input name="country" type="text" class="form-control" placeholder="Enter Country"
                                            value="{{ old('country', $Driver->country ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>State</label>
                                        <input name="state" type="text" class="form-control" placeholder="Enter State"
                                            value="{{ old('state', $Driver->state ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>City</label>
                                        <input name="city" type="text" class="form-control" placeholder="Enter City"
                                            value="{{ old('city', $Driver->city ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Pin Code</label>
                                        <input name="pincode" type="number" class="form-control" placeholder="Enter Pin Code"
                                            value="{{ old('pincode', $Driver->pincode ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group-item">
                            <div class="row align-item-center">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>License No</label>
                                        <input name="license_no" type="text" class="form-control" placeholder="Enter License No"
                                            value="{{ old('license_no', $Driver->license_no ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Vehicle Name</label>
                                        <input name="vehicle_name" type="text" class="form-control" placeholder="Enter Vehicle Name"
                                            value="{{ old('vehicle_name', $Driver->vehicle_name ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Vehicle No</label>
                                        <input name="vehicle_no" type="text" class="form-control" placeholder="Enter Vehicle No"
                                            value="{{ old('vehicle_no', $Driver->vehicle_no ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Pan Card</label>
                                        <input name="pan_card" type="text" class="form-control" placeholder="Enter Pan Card"
                                            value="{{ old('pan_card', $Driver->pan_card ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Pan Card FILE</label>
                                        @if (!$show)
                                            <input name="pan_card_FILE" type="file" class="form-control"   placeholder="Enter Pan Card"
                                                value="{{ old('pan_card_FILE', $Driver->pan_card_FILE ?? '') }}"
                                                @if ($show) disabled @endif>
                                        @endif
                                        @if (isset($Driver->pan_card_FILE) && $Driver->pan_card_FILE)
                                            <a href="{{ asset('storage/driver/' . $Driver->pan_card_FILE) }}"
                                                data-fancybox="gallery" data-caption="{{ $Driver->pan_card_FILE }}"
                                                target="_blank">
                                                <img src="{{ asset('storage/driver/' . $Driver->pan_card_FILE) }}"
                                                    alt="pan_card_FILE" width="100" height="100"
                                                    style="cursor: pointer;" class="img-thumbnail">
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Aadhar Card</label>
                                        <input name="aadhar_card" type="text" class="form-control" placeholder="Enter Aadhar Card"
                                            value="{{ old('aadhar_card', $Driver->aadhar_card ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Aadhar Card FILE</label>
                                        @if (!$show)
                                            <input name="aadhar_card_FILE" type="file" class="form-control" placeholder="Enter Aadhar Card"
                                                value="{{ old('aadhar_card_FILE', $Driver->aadhar_card_FILE ?? '') }}"
                                                @if ($show) disabled @endif>
                                        @endif
                                        @if ($show)
                                            @if (isset($Driver->aadhar_card_FILE) && $Driver->aadhar_card_FILE)
                                                <a href="{{ asset('storage/driver/' . $Driver->aadhar_card_FILE) }}"
                                                    data-fancybox="gallery"
                                                    data-caption="{{ $Driver->aadhar_card_FILE }}">
                                                    <img src="{{ asset('storage/driver/' . $Driver->aadhar_card_FILE) }}"
                                                        alt="aadhar_card_FILE" width="100" height="100"
                                                        style="cursor: pointer;" class="img-thumbnail">
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-primary cancel me-2"
                                onclick="window.location='{{ route('driver.index') }}'">Cancel</button>
                            @if (!$show)
                                <button type="submit"
                                    class="btn btn-primary">{{ isset($Driver) ? 'Update' : 'Save' }}</button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <script>
        window.Laravel = {
            routeIndex: "{{ route('driver.index') }}"
        };
    </script>
@endsection
