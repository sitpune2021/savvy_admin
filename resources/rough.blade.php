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