/**
 * Form 6 View Functionality
 * Redirects to view_form6.php with the selected intern's ID
 */
document.addEventListener("DOMContentLoaded", function () {
    const viewForm6Btn = document.getElementById("view-form6-btn");
    const internSelect = document.getElementById("intern-select");
    const exportModal = document.getElementById("export-modal");

    if (viewForm6Btn) {
        viewForm6Btn.addEventListener("click", function () {
            const internId = internSelect.value;


            // Redirect to the preview page in a new tab
            const url = `view_form6.php?id=${internId}`;
            window.open(url, '_blank');

            // Close the modal after opening the tab
            if (exportModal) {
                exportModal.classList.add("hidden");
                document.body.classList.remove("overflow-hidden");
            }
        });
    }
});