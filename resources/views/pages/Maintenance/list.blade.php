@extends('layouts.app')
@php
    $title = 'Maintenance - ' . config('app.name');
    $PageTitle = 'Maintenance List';
    $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => $PageTitle, 'url' => '']];
    $statusClasses = [
        'cancelled' => 'bg-danger-subtle text-danger',
        'pending' => 'bg-warning-subtle text-warning',
        'completed' => 'bg-success-subtle text-success',
        'in_progress' => 'bg-info-subtle text-info',
    ];
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

    {{-- <div class="card">
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
    </div> --}}

    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills animation-nav gap-2 mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#fuel1" role="tab">
                                Fuel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#other1" role="tab">
                                Maintenance
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content text-muted">
                        <div class="tab-pane active" id="fuel1" role="tabpanel">
                            <div class="table-responsive">
                                <table id="buttons-datatables" class="display table table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Driver</th>
                                            <th>Total Fuel</th>
                                            <th>Amount</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($maintenances->where('type', 'fuel') as $maintenance)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $maintenance?->driver?->name }}</td>
                                                <td>{{ $maintenance->description }} ltr</td>
                                                <td>{{ $maintenance->amount }}</td>
                                                <td>
                                                    @if (is_array($maintenance->image) && count($maintenance->image))
                                                        @if (!empty($maintenance->image['metercopy']))
                                                            <a href="{{ asset('storage/fuel/' . $maintenance->image['metercopy']) }}"
                                                                target="_blank">
                                                                <img src="{{ asset('storage/fuel/' . $maintenance->image['metercopy']) }}"
                                                                    alt="Meter Copy" width="50">
                                                            </a>
                                                        @endif
                                                        @if (!empty($maintenance->image['recipt']))
                                                            <a href="{{ asset('storage/fuel/' . $maintenance->image['recipt']) }}"
                                                                target="_blank">
                                                                <img src="{{ asset('storage/fuel/' . $maintenance->image['recipt']) }}"
                                                                    alt="Receipt" width="50">
                                                            </a>
                                                        @endif
                                                    @else
                                                        <span>No image</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <form action="{{ route('maintenance.update.status', $maintenance->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                    
                                                        <select name="status" class="form-select" onchange="this.form.submit()">
                                                            <option value="pending" {{ $maintenance->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="approved" {{ $maintenance->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                                            <option value="rejected" {{ $maintenance->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td>{{ $maintenance->date }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="other1" role="tabpanel">
                            <div class="table-responsive">
                                <table id="buttons-datatables-2" class="display table table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Driver</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Image</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($maintenances->where('type', 'other') as $maintenance)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $maintenance?->driver?->name }}</td>
                                                <td>{{ $maintenance->description }}</td>
                                                <td>{{ $maintenance->amount }}</td>
                                                <td>
                                                    @if (is_array($maintenance->image) && count($maintenance->image))
                                                        @if (!empty($maintenance->image['bill']))
                                                            <a href="{{ asset('storage/other/' . $maintenance->image['bill']) }}"
                                                                target="_blank">
                                                                <img src="{{ asset('storage/other/' . $maintenance->image['bill']) }}"
                                                                    alt="Bill" width="50">
                                                            </a>
                                                        @endif
                                                    @else
                                                        <span>No image</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge {{ $statusClasses[$maintenance->status] ?? 'bg-secondary-subtle text-secondary' }} p-2">
                                                        {{ ucfirst(str_replace('_', ' ', $maintenance->status)) }}
                                                    </span>
                                                </td>
                                                <td>{{ $maintenance->date }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!-- end card-body -->
            </div>
        </div>
    </div>
@endsection
