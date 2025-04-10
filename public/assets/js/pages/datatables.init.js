function initializeTables() {
    new DataTable("#buttons-datatables", {
        dom: "Bfrtip",
        fixedHeader: !0,
        pagingType: "full_numbers",
        pageLength: 25,
        buttons: ["copy", "csv", "excel", "print", "pdf"]
    });
}
document.addEventListener("DOMContentLoaded", function () {
    initializeTables()
});