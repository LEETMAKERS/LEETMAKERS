document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".edit-icon").forEach(icon => {
        icon.addEventListener("click", function () {
            let inputId = this.getAttribute("data-target");
            let inputField = document.getElementById(inputId);

            if (inputField.hasAttribute("readonly")) {
                inputField.removeAttribute("readonly");
                inputField.classList.remove("disabled-input");
                this.classList.remove("fa-pen");
                this.classList.add("fa-times"); // Change to cancel icon
                inputField.dataset.originalValue = inputField.value; // Store original value
                inputField.focus();
            } else {
                inputField.setAttribute("readonly", true);
                inputField.classList.add("disabled-input");
                this.classList.remove("fa-times");
                this.classList.add("fa-pen"); // Change back to edit icon
                inputField.value = inputField.dataset.originalValue; // Restore original value
            }
        });
    });
});
