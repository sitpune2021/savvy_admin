@extends('layouts.app')
@php
    $title = 'Products - ' . config('app.name');
    $PageTitle = isset($product) ? ($show ? 'View Product' : 'Edit Product') : 'Create Product';

    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Products List', 'url' => url('/product')],
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
            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $product->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item">
                            <div class="row align-item-center">
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Name</label>
                                        <input name="name" type="text" class="form-control" placeholder="Enter name"
                                            value="{{ old('name', $product->name ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Code</label>
                                        <input name="code" type="text" class="form-control" placeholder="Enter code"
                                            value="{{ old('code', $product->code ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Price</label>
                                        <input name="price" type="number" class="form-control" placeholder="Enter price"
                                            value="{{ old('price', $product->price ?? '') }}"
                                            @if ($show) disabled @endif>
                                    </div>
                                </div>

                                <div class=" col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Image</label>
                                        @if (!$show)
                                            <input name="image" type="file" class="form-control" placeholder="Enter image"
                                                value="{{ old('image', $product->image ?? '') }}"
                                                @if ($show) disabled @endif>
                                        @endif
                                        @if ($show)
                                            @if (isset($product->image) && $product->image)
                                                <a href="{{ asset('storage/product/' . $product->image) }}"
                                                    data-fancybox="gallery"
                                                    data-caption="{{ $product->image }}">
                                                    <img src="{{ asset('storage/product/' . $product->image) }}"
                                                        alt="image" width="100" height="100"
                                                        style="cursor: pointer;" class="img-thumbnail">
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group-item">
                            <div class="row align-item-center">
                                <div class="col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control snow-editor" rows="10" placeholder="Enter description"
                                          @if ($show) disabled @endif>{{ old('description', $product->description ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-primary cancel me-2"
                            onclick="window.location='{{ route('product.index') }}'">Cancel</button>
                        @if (!$show)
                            <button type="submit" class="btn btn-primary">{{ isset($product) ? 'Update' : 'Save' }}</button>
                        @endif
                    </div>
                </div>
        </div>
        </form>

    </div>
    </div>
    <script>
        window.Laravel = {
            routeIndex: "{{ route('product.index') }}"
        };
    </script>
@endsection
