@extends('layouts.app')
@php
    $title = 'Plants - ' . config('app.name');
    $PageTitle = isset($Plant) ? ($show ? 'View Plants' : 'Edit Plants') : 'Create Plants';
    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Plant List', 'url' => url('/plant')],
        ['name' => $PageTitle, 'url' => ''],
    ];
@endphp
@push('styles')
    <link href="{{ asset('/assets/libs/quill/quill.core.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/libs/quill/quill.bubble.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/libs/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />
    <link href="../../../../cdn.jsdelivr.net/npm/select2%404.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
            <form id="plantForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $Plant->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item">
                            <div class="row align-item-center">
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Name</label>
                                        <input name="name" type="text" class="form-control"
                                            placeholder="Enter the plant name"
                                            value="{{ old('name', $Plant->name ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Manager</label>
                                        <input name="manager" type="text" class="form-control"
                                            placeholder="Enter the manager's name"
                                            value="{{ old('manager', $Plant->manager ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group-item">
                            <div class="row align-item-center">
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Address</label>
                                        <input name="address" type="text" class="form-control"
                                            placeholder="Enter the address"
                                            value="{{ old('address', $Plant->address ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Location</label>
                                        <input name="location" type="text" class="form-control"
                                            placeholder="Enter the location"
                                            value="{{ old('location', $Plant->location ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Pin Code</label>
                                        <input name="pincode" type="number" class="form-control"
                                            placeholder="Enter the pin code"
                                            value="{{ old('pincode', $Plant->pincode ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group-item border-0 mb-0">
                            <div class="row align-item-center">
                                <div class="col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Details</label>
                                        <textarea name="details" class="form-control snow-editor" rows="10"
                                            placeholder="Enter additional details about the plant" @if ($show) disabled @endif>{{ old('details', $Plant->details ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-primary cancel me-2"
                            onclick="window.location='{{ route('plant.index') }}'">Cancel</button>
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
            routeIndex: "{{ route('plant.index') }}"
        };
    </script>
@endsection
