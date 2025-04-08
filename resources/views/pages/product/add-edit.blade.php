@extends('layouts.app')
@php
    $title = 'Products - ' . config('app.name');
    $PageTitle = isset($product) ? ($show ? 'View Product' : 'Edit Product') : 'Create Product';

    // $breadcrumbs = [
    //     ['title' => 'Home', 'url' => url('/')],
    //     ['title' => 'Plants', 'url' => route('skills.index')],
    //     ['title' => $PageTitle, 'url' => null], // Current page without URL
    // ];

@endphp
@section('content')
    <div class="card mb-0">
        <div class="card-body">
            <div class="content-page-header">
                <h5>{{ $PageTitle }}</h5>
            </div>
            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" id="id" name="id" value="{{ $product->id ?? null }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group-item">
                            <h5 class="form-title">Basic Details</h5>
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
                            <h5 class="form-title">Description</h5>
                            <div class="row align-item-center">
                                <div class="col-md-6 col-sm-12">
                                    <div class="input-block mb-3">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" placeholder="Enter description"
                                         rows="3" @if ($show) disabled @endif>{{ old('description', $product->description ?? '') }}</textarea>
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
