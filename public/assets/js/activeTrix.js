document.addEventListener("DOMContentLoaded", function() {
    // trix on the input with the class
    document.querySelectorAll(".trix-editor").forEach(function(element) {
        element.setAttribute("trix-editor", "true");
    });
    document.addEventListener('trix-change', function(event) {
        const trixEditor = event.target;
        const hiddenInput = document.getElementById('article_description');
        hiddenInput.value = trixEditor.value;
    });
});
