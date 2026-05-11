/**
 * Export Functionality
 * Handles exporting individual and master timesheet data
 */

document.addEventListener("DOMContentLoaded", function () {
  setupExportModal();
});

function setupExportModal() {
  // Get Modal Elements
  const exportModal = document.getElementById("export-modal");
  const exportMasterModal = document.getElementById("export-master-modal");
  
  // Get Button Elements
  const exportButton = document.querySelector('button[name="export_csv"]');
  const confirmExport = document.getElementById("confirm-export");
  const confirmMasterExport = document.getElementById("confirm-master-export");
  
  // Selection Elements
  const internSelect = document.getElementById("intern-select");
  const exportStudentNameSpan = document.getElementById("export-student-name");
  const exportFilenameElement = document.getElementById("export-filename");

  // Logic to show the correct modal
  if (exportButton) {
    exportButton.addEventListener("click", function (e) {
      e.preventDefault();

      // DIRECT TO MASTER MODAL (No alert message here)
      if (internSelect.value === "") {
        if (exportModal) exportModal.classList.add("hidden");
        if (exportMasterModal) {
          exportMasterModal.classList.remove("hidden");
          document.body.classList.add("overflow-hidden");
        }
        return;
      }

      // IF STUDENT IS SELECTED: Direct to Individual Modal
      const selectedOption = internSelect.options[internSelect.selectedIndex];
      if (exportStudentNameSpan) exportStudentNameSpan.textContent = selectedOption.text;

      if (exportFilenameElement) {
        const studentName = selectedOption.text.replace(/ /g, "_");
        const currentDate = new Date().toISOString().split("T")[0];
        exportFilenameElement.textContent = `${studentName}_timesheet_${currentDate}.csv`;
      }

      if (exportModal) {
        exportModal.classList.remove("hidden");
        document.body.classList.add("overflow-hidden");
      }
    });
  }

  // Handle Master CSV Submission
  if (confirmMasterExport) {
    confirmMasterExport.addEventListener("click", function () {
      const form = document.createElement("form");
      form.method = "post";
      form.action = "export_handler.php"; 

      const exportInput = document.createElement("input");
      exportInput.type = "hidden";
      exportInput.name = "export_master_report";
      exportInput.value = "1";
      form.appendChild(exportInput);

      exportMasterModal.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
      
      // Removed: showCustomAlert logic to keep it silent

      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);
    });
  }

  // Handle Individual CSV Submission
  if (confirmExport) {
    confirmExport.addEventListener("click", function () {
      const form = document.createElement("form");
      form.method = "post";
      form.action = "index.php";

      const internIdInput = document.createElement("input");
      internIdInput.type = "hidden";
      internIdInput.name = "intern_id";
      internIdInput.value = internSelect.value;
      form.appendChild(internIdInput);

      const exportInput = document.createElement("input");
      exportInput.type = "hidden";
      exportInput.name = "export_csv";
      exportInput.value = "1";
      form.appendChild(exportInput);

      exportModal.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");

      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);
    });
  }

  // Close Logic
  const closeTriggers = [
    "close-export-modal", "cancel-export", 
    "close-master-modal", "cancel-master-export"
  ];

  closeTriggers.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener("click", () => {
        if (exportModal) exportModal.classList.add("hidden");
        if (exportMasterModal) exportMasterModal.classList.add("hidden");
        document.body.classList.remove("overflow-hidden");
      });
    }
  });

  // Close when clicking outside
  window.addEventListener("click", function (e) {
    if (e.target.classList.contains("modal-overlay")) {
      if (exportModal) exportModal.classList.add("hidden");
      if (exportMasterModal) exportMasterModal.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
    }
  });

  // Close with Escape key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      if (exportModal) exportModal.classList.add("hidden");
      if (exportMasterModal) exportMasterModal.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
    }
  });
}