    <div class="row">
        <div class="col-sm-12">
            <div class="card-table">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-center table-hover datatable">
                            <thead class="thead-light">
                                <tr class="align-items-center">
                                    <th class="text-center">#</th>
                                    <th>Name</th>
                                    <th>Manager</th>
                                    <th class="no-sort text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($drivers as $driver)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $driver->name }}</td>
                                        <td>{{ $driver->manager }}</td>
                                        <td class="d-flex align-items-center justify-content-center">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="btn-action-icon" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <ul>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('plant.edit', $driver->id) }}">
                                                                <i class="far fa-edit me-2"></i>Edit
                                                            </a>
                                                        </li>
                                                        {{-- <li>
                                                            <a class="dropdown-item" href="javascript:void(0);"
                                                                data-bs-toggle="modal" data-bs-target="#delete_modal">
                                                                <i class="far fa-trash-alt me-2"></i>Delete
                                                            </a>
                                                        </li> --}}
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('plant.show', $driver->id) }}">
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



    {{-- schedule  --}}

    {{-- // Fetch active, accepted additional contracts
    // $contractsAdditional = Contracts::where('status', 'active')
    // ->where('type', 'additional')
    // ->where('accepted_status', 'accepted')
    // ->with('sender.shippingAddress')
    // ->get();

    //             Log::info('Contract Add: ' . $contractsAdditional);


    // foreach ($contractsAdditional as $contractAdditional) {
    //     $endDate = Carbon::parse($contractAdditional->date);
    //     if ($today->greaterThan($endDate)) {
    //         $contractAdditional->status = 'expired';
    //         $contractAdditional->save();
    //         continue;
    //     }
    //     Log::info('Contract ID: ' . $contractAdditional->sender);
    //     $exists = Orders::whereDate('created_at', $today->toDateString())
    //         ->where('contract_id', $contractAdditional->id)
    //         ->where('shipping_id', $shipping->id) // Consider per shipping address
    //         ->exists();

    //     if (!$exists) {
    //         Orders::create([
    //             'customer_id' => $contract->customer_id,
    //             'contract_id' => $contract->id,
    //             'shipping_id' => $shipping->id,
    //             'route_id' => $shipping->route_id,
    //             'driver_id' => $shipping->driver_id,
    //             'status' => 'pending',
    //         ]);
    //     }

    // } --}}