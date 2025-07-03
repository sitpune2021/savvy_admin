@extends('layouts.app')
@php
    $title = 'Request Orders - ' . config('app.name');
    $PageTitle = 'Request Orders List';
    $breadcrumbs = [['name' => 'Home', 'url' => url('/')], ['name' => $PageTitle, 'url' => '']];
    $statusClasses = [
        'expired' => 'bg-danger-subtle text-danger',
        'rejected' => 'bg-danger-subtle text-danger',
        'pending' => 'bg-warning-subtle text-warning',
        'accepted' => 'bg-success-subtle text-success',
        'active' => 'bg-success-subtle text-success',
        'in-progress' => 'bg-info-subtle text-info',
    ];
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
@endpush
@push('scripts')
    <script>
        function toggleEdit(wrapper) {
            const badge = wrapper.querySelector('.status-badge');
            const form = wrapper.querySelector('.status-form');

            badge.classList.add('d-none');
            form.classList.remove('d-none');
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
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

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="buttons-datatables" class="display table table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Sr. No</th>
                                    <th>Customer</th>
                                    <th>shipping Address</th>
                                    <th>Sender</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Assigned Driver</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contracts as $contract)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $contract->customer->name }}</td>
                                        <td>{{ $contract->sender?->shippingAddress?->shipping_address }}</td>
                                        <td>{{ $contract->sender->name }}</td>
                                        <td>{{ $contract->product->name }}</td>
                                        <td>{{ $contract->quantity }}</td>
                                        <td>
                                            <div @if ($contract->status === 'active') ondblclick="toggleEdit(this)" @endif
                                                class="status-wrapper">
                                                <span
                                                    class="badge {{ $statusClasses[$contract->accepted_status] ?? 'bg-secondary-subtle text-secondary' }} p-2 status-badge">
                                                    {{ ucfirst(str_replace('_', ' ', $contract->accepted_status)) }}
                                                </span>

                                                <form action="{{ route('requestOrder.update.status', $contract->id) }}"
                                                    method="POST" class="status-form d-none">
                                                    @csrf
                                                    @method('PUT')
                                                    <select name="accepted_status" class="form-select form-select-sm"
                                                        onchange="this.form.submit()">
                                                        <option value="pending"
                                                            {{ $contract->accepted_status == 'pending' ? 'selected' : '' }}>
                                                            Pending</option>
                                                        <option value="accepted"
                                                            {{ $contract->accepted_status == 'accepted' ? 'selected' : '' }}>
                                                            Accept</option>
                                                        <option value="rejected"
                                                            {{ $contract->accepted_status == 'rejected' ? 'selected' : '' }}>
                                                            Rejected</option>
                                                    </select>
                                                </form>
                                            </div>
                                        </td>
                                        <td>{{ $contract->date }}</td>
                                        <td>{{ $contract->sender?->shippingAddress?->driver->name }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $statusClasses[$contract->status] ?? 'bg-secondary-subtle text-secondary' }} p-2 status-badge">
                                                {{ ucfirst(str_replace('_', ' ', $contract->status)) }}
                                            </span>
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
