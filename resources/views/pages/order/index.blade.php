@extends('layouts.app')
@php
    $title = 'Orders - ' . config('app.name');
    $PageTitle = 'Orders List';
    $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => $PageTitle, 'url' => '']];
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
@endpush
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="{{ asset('/assets/js/pages/datatables.init.js') }}"></script>
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
    @if (auth()->user()?->vendor?->id === null && auth()->user()?->plantManager?->id == null)
        <div class="card">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-sm-auto ms-auto">
                        <div class="list-grid-nav hstack gap-1">
                            <a class="btn btn-success" href="{{ route('order.create') }}">
                                <i class="ri-add-fill me-1 align-bottom"></i> Add
                                Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="orders-table" class="display table table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr. No</th>
                                    <th>Plant</th>
                                    <th>Customer</th>
                                    <th>shipping Address</th>
                                    <th>Driver</th>
                                    <th>Delivery Quantity</th>
                                    <th>Status</th>
                                    <th>Order Create Date</th>
                                    <th>Order Completed Date </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @foreach ($orders as $order)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                            @if( auth()->user()?->vendor?->id === null && $order->drivers?->vendor_id != null && auth()->user()?->plantManager?->id == null)
                                                <i class="ri-user-shared-line"></i>
                                            @endif
                                            @if( $order->type == 'additional')
                                                <i class="ri-shopping-cart-line"></i>
                                            @endif
                                        </td>
                                        <td>{{ $order->customers->name }}</td>
                                        <td>{{ $order->shipping->shipping_address }}</td>
                                        <td>{{ $order?->drivers?->name }}</td>
                                        <td>{{ $order->develivered_qty }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $statusClasses[$order->status] ?? 'bg-secondary-subtle text-secondary' }} p-2">
                                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('d-m-Y') }}</td>
                                        <td>
                                            <div class="hstack gap-3 flex-wrap">
                                                <a href="{{ route('order.show', $order->id) }}"
                                                    class="link-primary fs-15"><i class="ri-eye-line"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
