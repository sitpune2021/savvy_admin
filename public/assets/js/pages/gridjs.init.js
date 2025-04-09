document.getElementById("table-search") && new gridjs.Grid({
    columns: plantColumns,
    pagination: {
        limit: 5
    },
    search: true,
    sort: true,
    data: plantData  // Dynamic data passed from Blade
}).render(document.getElementById("table-search"));
