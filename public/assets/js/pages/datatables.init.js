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
