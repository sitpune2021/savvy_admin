@extends('layouts.app')
@php
    $title = 'Orders - ' . config('app.name');
    $PageTitle = 'Orders List';
@endphp

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="content-page-header">
            <h5>{{ $PageTitle }}</h5>
            <div class="list-btn">
                <ul class="filter-list">
                    {{-- <li>
                        <a class="btn btn-filters w-auto popup-toggle" data-bs-toggle="tooltip"
                            data-bs-placement="bottom" title="Filter"><span class="me-2"><img src="{{ asset('img/icons/filter-icon.svg')}}" alt="filter"></span>Filter </a>
                    </li> --}}
                    {{-- <li>
                        <div class="dropdown dropdown-action" data-bs-toggle="tooltip" data-bs-placement="top" title="Download">
                            <a href="#" class="btn-filters" data-bs-toggle="dropdown" aria-expanded="false"><span><i data-feather="download"></i></span></a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <ul class="d-block">
                                    <li>
                                        <a class="d-flex align-items-center download-item" href="javascript:void(0);" download><i class="far fa-file-pdf me-2"></i>PDF</a>
                                    </li>
                                    <li>
                                        <a class="d-flex align-items-center download-item" href="javascript:void(0);" download><i class="far fa-file-text me-2"></i>CVS</a>
                                    </li>
                                </ul>
                            </div>
                        </div>														
                    </li> --}}
                    {{-- <li>
                        <a class="btn btn-import" href="javascript:void(0);"><span><i data-feather="check-square me-2"></i>Import Customer</span></a>
                    </li> --}}
                    <li>
                        <a class="btn btn-primary" href="{{ route('order.create') }}"><i class="fa fa-plus-circle me-2"
                                aria-hidden="true"></i>Add</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /Page Header -->

    <div class="row">
        <div class="col-sm-12">
            <div class="card-table">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-center table-hover datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Driver</th>
                                    <th>Quantity </th>
                                    <th class="no-sort">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $order->customers->name }}</td>
                                        <td>{{ $order->drivers->name }}</td>
                                        <td>{{ $order->quantity }}</td>
                                        <td class="d-flex align-items-center">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="btn-action-icon" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <ul>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('order.edit', $order->id) }}">
                                                                <i class="far fa-edit me-2"></i>Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="javascript:void(0);"
                                                                data-bs-toggle="modal" data-bs-target="#delete_modal">
                                                                <i class="far fa-trash-alt me-2"></i>Delete
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('order.show', $order->id) }}">
                                                                <i class="far fa-eye me-2"></i>View
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
