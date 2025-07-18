@extends('layouts.app')
@php
    $title = $product->rawMaterial->name . ' ' . config('app.name');
    // $PageTitle = isset($Order) ? ($show ? 'View Order' : 'Edit Order') : 'Create Order';
    $PageTitle = isset($product) ? $product->rawMaterial->name . ' - ' . $product->variant_name : '';

    $breadcrumbs = [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => $product->rawMaterial->name . ' - ' . $product->variant_name . 'List', 'url' => url('/order')],
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
    <script src="{{ asset('/assets/js/pages/form-wizard.init.js') }}"></script>
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
        <div class="card-body form-steps">
            @if (isset($show) && $show)
                <!-- Read-Only Alert -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info shadow-sm">
                            <strong>Note:</strong> This is a <strong>read-only</strong> view of the stock purchase. You
                            cannot edit or distribute stock from here.
                        </div>
                    </div>
                </div>

                <!-- Stock Details -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Stock Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Variant:</strong> {{ $product->rawMaterial->name }} - {{ $product->variant_name }}</p>
                        <p><strong>Total Quantity:</strong> {{ $product->total_quantity }}</p>
                        <div class="accordion nesting-accordion custom-accordionwithicon accordion-border-box accordion-fill-secandary"
                            id="accordionnesting">
                            @foreach ($product->transactions as $transaction)
                                @php
                                    $transactionId = 'transaction' . $transaction->id;
                                @endphp
                                <div class="accordion-item material-shadow ">
                                    <h2 class="accordion-header" id="heading{{ $transactionId }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $transactionId }}" aria-expanded="false"
                                            aria-controls="collapse{{ $transactionId }}">
                                            Transaction Details (ID: {{ $transaction->id }})
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $transactionId }}" class="accordion-collapse collapse"
                                        aria-labelledby="heading{{ $transactionId }}" data-bs-parent="#accordionnesting">
                                        <div class="accordion-body">
                                            <p><strong>Type:</strong> {{ $transaction->type }}</p>
                                            <p><strong>Quantity:</strong> {{ $transaction->quantity }}</p>
                                            <p><strong>Date:</strong> {{ $transaction->created_at->format('Y-m-d H:i:s') }}
                                            </p>

                                            @if ($transaction->distributions->isNotEmpty())
                                                <h6 class="mb-3">Distribution Details</h6>
                                                @foreach ($transaction->distributions as $distribution)
                                                    @php
                                                        $distributionId = 'distribution' . $distribution->id;
                                                    @endphp
                                                    <div class="accordion nesting3-accordion custom-accordionwithicon  accordion-border-box mt-3"
                                                        id="accordionnesting3-{{ $distributionId }}">
                                                        <div class="accordion-item mt-2">
                                                            <h2 class="accordion-header" id="heading{{ $distributionId }}">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapse{{ $distributionId }}"
                                                                    aria-expanded="false"
                                                                    aria-controls="collapse{{ $distributionId }}">
                                                                    {{ $distribution->plant->name }}
                                                                </button>
                                                            </h2>

                                                            <div id="collapse{{ $distributionId }}"
                                                                class="accordion-collapse collapse"
                                                                aria-labelledby="heading{{ $distributionId }}"
                                                                data-bs-parent="#accordionnesting3-{{ $distributionId }}">
                                                                <div class="accordion-body">
                                                                    <p><strong>Status:</strong> {{ $distribution->status }}
                                                                    </p>
                                                                    <p><strong>Quantity:</strong>
                                                                        {{ $distribution->quantity }}</p>
                                                                    <p><strong>Date:</strong>
                                                                    {{ $distribution->created_at->format('Y-m-d H:i:s') }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            @else
                <form class="vertical-navs-step" id="stockPurches">
                    <input type="hidden" name="id" id="id" value="{{ $id }}">
                    @if (isset($distribute) && $distribute)
                        <input type="hidden" name="distribute" id="distribute" value="{{ $distribute }}">
                        <input type="hidden" id="total_quantity" name="total_quantity" class="form-control"
                            value="{{ $product->remain_quantity }}" min="1" required>
                    @endif
                    <div class="row gy-5">
                        <!-- Left Nav Steps -->
                        <div class="col-lg-3">
                            <div class="nav flex-column custom-nav nav-pills" role="tablist" aria-orientation="vertical">
                                @if (!isset($distribute))
                                    <button class="nav-link active" id="v-pills-step1-tab" data-bs-toggle="pill"
                                        data-bs-target="#v-pills-step1" type="button" role="tab"
                                        aria-controls="v-pills-step1" aria-selected="true">
                                        <span class="step-title me-2">
                                            <i class="ri-check-line step-icon me-2"></i> Step 1
                                        </span>
                                        Purchase Stock
                                    </button>
                                @endif

                                <button class="nav-link  {{ !isset($distribute) ? '' : 'active' }}" id="v-pills-step2-tab"
                                    data-bs-toggle="pill" data-bs-target="#v-pills-step2" type="button" role="tab"
                                    aria-controls="v-pills-step2" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Step 2
                                    </span>
                                    Select Plants
                                </button>

                                <button class="nav-link" id="v-pills-step3-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-step3" type="button" role="tab"
                                    aria-controls="v-pills-step3" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Step 3
                                    </span>
                                    Distribute Stock
                                </button>

                                <button class="nav-link" id="v-pills-step4-tab" data-bs-toggle="pill"
                                    data-bs-target="#v-pills-step4" type="button" role="tab"
                                    aria-controls="v-pills-step4" aria-selected="false">
                                    <span class="step-title me-2">
                                        <i class="ri-close-circle-fill step-icon me-2"></i> Step 4
                                    </span>
                                    Confirmation
                                </button>

                            </div>
                        </div>


                        <!-- Right Tab Content -->
                        <div class="col-lg-6">
                            <div class="px-lg-4">
                                <!-- Step 1 -->
                                <div class="tab-content">
                                    @if (!isset($distribute))
                                        <div class="tab-pane fade show active" id="v-pills-step1" role="tabpanel"
                                            aria-labelledby="v-pills-step1-tab">
                                            <div>
                                                <h5>Purchase Stock</h5>
                                                <p class="text-muted">
                                                    {{ $product->rawMaterial->name . ' - ' . $product->variant_name }}
                                                </p>
                                            </div>

                                            <div>
                                                <div class="row g-3">
                                                    <div class="col-sm-6">
                                                        <label for="total_quantity" class="form-label">Purchase
                                                            Quantity</label>
                                                        <input type="number" id="total_quantity" name="total_quantity"
                                                            class="form-control" value="{{ old('total_quantity') }}"
                                                            min="1" required>
                                                        <div class="invalid-feedback">Please enter a quantity</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-start gap-3 mt-4">
                                                <button type="button"
                                                    class="btn btn-success btn-label right ms-auto nexttab"
                                                    data-nexttab="v-pills-step2-tab">
                                                    <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                    Go to Select Plant
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                    <!-- Step 2 -->
                                    <div class="tab-pane fade {{ !isset($distribute) ? '' : 'show active' }}"
                                        id="v-pills-step2" role="tabpanel" aria-labelledby="v-pills-step2-tab">
                                        <div>
                                            <h5>Select Plant</h5>
                                            <p class="text-muted">
                                                {{ $product->rawMaterial->name . ' - ' . $product->variant_name }}
                                            <p><strong>Total Stock :</strong> <span class="text-primary">
                                                    @if (isset($distribute) && $distribute)
                                                        {{ $product->total_quantity }}
                                                    @endif
                                                </span></p>
                                            <p><strong>To Distribute Quantity:</strong> <span id="purchaseQtyStep2"
                                                    class="text-primary">
                                                    @if (isset($distribute) && $distribute)
                                                        {{ $product->remain_quantity }}
                                                    @endif
                                                </span></p>
                                            </p>
                                        </div>

                                        <div>
                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    <label for="plants" class="form-label">Select Plants</label>
                                                    <select id="plants" name="plants[]"
                                                        class="select js-example-basic-single" multiple required>
                                                        @foreach ($plant as $value => $name)
                                                            <option value="{{ $value }}"
                                                                data-id="{{ $value }}"
                                                                data-name="{{ $name }}">{{ ucfirst($name) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">Please select at least one plant</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start gap-3 mt-4">
                                            <button type="button" class="btn btn-secondary prevtab"
                                                data-prevtab="v-pills-step1-tab">
                                                <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                Back to Purchase
                                            </button>

                                            <button type="button" class="btn btn-success btn-label right ms-auto nexttab"
                                                data-nexttab="v-pills-step3-tab">
                                                <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                Go to Distribute Stock
                                            </button>
                                        </div>
                                    </div>


                                    <!-- Step 3 -->
                                    <div class="tab-pane fade" id="v-pills-step3" role="tabpanel"
                                        aria-labelledby="v-pills-step3-tab">
                                        <div>
                                            <h5>Distribute Stock Over Plants</h5>
                                            <p class="text-muted">Allocate stock to selected plants</p>
                                            <p><strong>Purchase Quantity:</strong> <span id="purchaseQtyStep3"
                                                    class="text-primary"></span></p>

                                        </div>

                                        <div>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="allocations" class="form-label">Allocation Details</label>
                                                    <div id="allocations"></div>
                                                    <div class="invalid-feedback">Please distribute stock properly</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start gap-3 mt-4">
                                            <button type="button" class="btn btn-secondary prevtab"
                                                data-prevtab="v-pills-step2-tab">
                                                <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                Back to Select Plants
                                            </button>

                                            <button type="button" class="btn btn-success btn-label right ms-auto nexttab"
                                                data-nexttab="v-pills-step4-tab">
                                                <i class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                                                Go to Confirmation
                                            </button>
                                        </div>
                                    </div>


                                    <!-- Step 4 -->
                                    <div class="tab-pane fade" id="v-pills-step4" role="tabpanel"
                                        aria-labelledby="v-pills-step4-tab">
                                        <div>
                                            <h5>Review & Confirm</h5>
                                            <p class="text-muted">Please verify all information before submitting your
                                                order.
                                            </p>
                                        </div>

                                        <div>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Summary</label>
                                                    <div id="summary" class="border p-3 rounded bg-light"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start gap-3 mt-4">
                                            <button type="button" class="btn btn-secondary prevtab"
                                                data-prevtab="v-pills-step3-tab">
                                                <i class="ri-arrow-left-line label-icon align-middle fs-16 me-2"></i>
                                                Back to Distribution
                                            </button>

                                            <button type="submit" class="btn btn-primary ms-auto">
                                                <i class="ri-check-line label-icon align-middle fs-16 me-2"></i>
                                                Submit Order
                                            </button>
                                        </div>
                                    </div>

                                </div> <!-- tab-content -->

                                @if ($errors->any())
                                    <div class="alert alert-danger mt-3">
                                        <ul>
                                            @foreach ($errors->all() as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if (session('success'))
                                    <div class="alert alert-success mt-3">{{ session('success') }}</div>
                                @endif

                            </div>
                        </div>
                    </div>
                    <!-- end row -->
                </form>
            @endif
        </div>
    </div>
    <script>
        window.orderData = {
            shippingId: "{{ $Order->shipping_id ?? '' }}",
            customerId: "{{ $Order->customer_id ?? '' }}",
            develiveredQty: "{{ $Order->develivered_qty ?? 1 }}",
        };
        window.Laravel = {
            routeIndex: "{{ route('raw-materials.index') }}"
        };
    </script>
@endsection
