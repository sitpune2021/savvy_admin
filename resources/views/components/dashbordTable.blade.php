<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">
                    Yesterday Orders
                </h4>
            </div>

            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-borderless table-centered align-middle table-nowrap mb-0"
                        id="yesterdayPendingOrders">
                        <thead class="text-muted table-light">
                            <tr>
                                <th scope="col">Order ID</th>
                                <th scope="col">Customer</th>
                                <th scope="col">shipping Address</th>
                                <th scope="col">Driver</th>
                                <th scope="col">Delivery Quantity</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody id="pending-orders-table-body">
                            {{-- <div id="loader">Loading...</div> --}}
                            @include('components.dashbordTableBody', [
                                'allPendingOrders' => $allPendingOrders,
                            ])
                        </tbody>
                    </table>
                    <!-- end table -->
                </div>
            </div>
        </div>
        <!-- .card-->
    </div>
    <!-- .col-->
</div>
