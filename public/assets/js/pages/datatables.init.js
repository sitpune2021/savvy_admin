function initializeTables() {
    const exportOptions = {
        columns: ':not(:last-child)' // exclude the last column (typically "Action")
    };
    new DataTable("#buttons-datatables", {
        dom: "Bfrtip",
        fixedHeader: true,
        order: [],
        pagingType: "full_numbers",
        pageLength: 25,
        buttons: [
            {
                extend: 'copy',
                exportOptions: exportOptions
            },
            {
                extend: 'csv',
                exportOptions: exportOptions
            },
            {
                extend: 'excel',
                exportOptions: exportOptions
            },
            {
                extend: 'print',
                exportOptions: exportOptions
            }
        ]
    });

    new DataTable("#buttons-datatables-cust", {
        dom: "Bfrtip",
        fixedHeader: true,
        paging: false,  // Disable pagination
        buttons: [
            {
                extend: 'copy',
                exportOptions: exportOptions
            },
            {
                extend: 'csv',
                exportOptions: exportOptions
            },
            {
                extend: 'excel',
                exportOptions: exportOptions
            },
            {
                extend: 'print',
                exportOptions: exportOptions
            }
        ]
    });

    function initDataTable(selector, ajaxUrl, columns, exportOptions = null, options = {}) {
        return new DataTable(selector, {
            dom: exportOptions ? 'Bfrtip' : 'frtip',
            fixedHeader: true,
            order: [],
            pagingType: 'full_numbers',
            pageLength: 25,
            processing: true,
            serverSide: true,
            ajax: ajaxUrl,
            columns: columns,
            buttons: exportOptions ? [
                {
                    extend: 'copy',
                    exportOptions: exportOptions
                },
                {
                    extend: 'csv',
                    exportOptions: exportOptions
                },
                {
                    extend: 'excel',
                    exportOptions: exportOptions
                },
                {
                    extend: 'print',
                    exportOptions: exportOptions
                }
            ] : [],
            preDrawCallback: function (settings) {
                $('#data-table-loader').fadeIn(200);
                return true; // continue drawing
            },
            drawCallback: function (settings) {
                $('#data-table-loader').fadeOut(200);
            },
            language: { processing: '' },
            ...options // override or extend other options
        });
    }

    initDataTable('#orders-table', '/order', [
        { data: 'order_id', name: 'order_id', orderable: false },
        { data: 'plant', name: 'shipping.plant.name', orderable: false },
        { data: 'customer', name: 'customers.name', orderable: false },
        { data: 'shipping_address', name: 'shipping.shipping_address', orderable: false },
        { data: 'driver', name: 'drivers.name', orderable: false },
        { data: 'develivered_qty', name: 'develivered_qty', orderable: false },
        { data: 'status_label', name: 'status', orderable: false },
        { data: 'date', name: 'created_at', orderable: false },
        { data: 'date_complete', name: 'date_complete', orderable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ], exportOptions);

    initDataTable('#orders-request-table', '/request-order', [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
        { data: 'customer_name', name: 'customer.name', orderable: false },
        { data: 'shipping_address', name: 'shippingAddress.shipping_address', orderable: false },
        { data: 'sender_name', name: 'sender.name', orderable: false },
        { data: 'product_name', name: 'product.name', orderable: false },
        { data: 'quantity', name: 'quantity', orderable: false },
        { data: 'accepted_status', name: 'accepted_status', orderable: false, searchable: false },
        { data: 'date', name: 'date', orderable: false },
        { data: 'driver_name', name: 'shippingAddress.driver.name', orderable: false },
        { data: 'status', name: 'status', orderable: false, searchable: false }
    ], exportOptions);

    initDataTable('#yesterdayPendingOrders', '/yesterday-pending-orders-data', [
        { data: 'order_id', name: 'order_id', orderable: false },
        { data: 'customer', name: 'customer', orderable: false },
        { data: 'shipping_address', name: 'shipping_address', orderable: false },
        { data: 'driver', name: 'driver', orderable: false },
        { data: 'delivery_quantity', name: 'delivery_quantity', orderable: false },
        { data: 'status', name: 'status', orderable: false },
        { data: 'date', name: 'date', orderable: false }
    ]);

    new DataTable("#buttons-datatables-2", {
        dom: "Bfrtip",
        fixedHeader: true,
        order: [],
        pagingType: "full_numbers",
        pageLength: 25,
        buttons: [
            {
                extend: 'copy',
                exportOptions: exportOptions
            },
            {
                extend: 'csv',
                exportOptions: exportOptions
            },
            {
                extend: 'excel',
                exportOptions: exportOptions
            },
            {
                extend: 'print',
                exportOptions: exportOptions
            }
        ]
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initializeTables();
});
