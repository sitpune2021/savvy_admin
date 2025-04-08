@extends('layouts.app')
@php
    $title = 'Plants - ' . config('app.name');
    $PageTitle = isset($Plant) ? ($show ? 'View Plants' : 'Edit Plants') : 'Create Plants'
@endphp
@section('content')
    <div class="card mb-0">
        <div class="card-body">
            <div class="content-page-header">
                <h5>{{ $PageTitle }}</h5>
            </div>
            <form id="plantForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $Plant->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item">
                            <h5 class="form-title">Basic Details</h5>
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
                            <h5 class="form-title">Address</h5>
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
                            <h5 class="form-title">Details</h5>
                            <div class="row align-item-center">
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Details</label>
                                        <textarea name="details" class="form-control" rows="3" 
                                            placeholder="Enter additional details about the plant"
                                            @if ($show) disabled @endif>{{ old('details', $Plant->details ?? '') }}</textarea>
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
