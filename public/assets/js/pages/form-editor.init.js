document.querySelectorAll(".snow-editor").forEach(function (editorDiv) {
    var inputId = editorDiv.getAttribute("data-input-id");
    var hiddenInput = document.getElementById(inputId);

    var quill = new Quill(editorDiv, {
        theme: "snow",
        modules: {
            toolbar: [
                [{ font: [] }, { size: [] }],
                ["bold", "italic", "underline", "strike"],
                [{ color: [] }, { background: [] }],
                [{ script: "super" }, { script: "sub" }],
                [{ header: [false, 1, 2, 3, 4, 5, 6] }, "blockquote", "code-block"],
                [{ list: "ordered" }, { list: "bullet" }, { indent: "-1" }, { indent: "+1" }],
                ["direction", { align: [] }],
                ["link", "image", "video"],
                ["clean"]
            ]
        }
    });

    // Optional: initialize with existing content
    if (hiddenInput && hiddenInput.value) {
        quill.root.innerHTML = hiddenInput.value;
    }

    // Update hidden input before form submission
    var form = editorDiv.closest("form");
    if (form && hiddenInput) {
        form.addEventListener("submit", function () {
            hiddenInput.value = quill.root.innerHTML;
        });
    }
});
