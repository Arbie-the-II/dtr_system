<?php 
include './main.php';

// Set up our initial variables - we'll use these later
$has_active_pause = false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DICT Internship Timesheet</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="./assets/images/Dict.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="./assets/css/index.css">

  <?php
  $has_active_timein = false;
  $active_timein_session = false;

  if (!empty($selected_intern_id) && isset($current_timesheet) && is_array($current_timesheet)) {
      if (!isTimeEmpty($current_timesheet['am_timein']) && isTimeEmpty($current_timesheet['am_timeOut'])) {
          $has_active_timein = true;
      } else if (!isTimeEmpty($current_timesheet['pm_timein']) && isTimeEmpty($current_timesheet['pm_timeout'])) {
          $has_active_timein = true;
      }

      if (isset($_SESSION['timein_timestamp']) && $has_active_timein) {
          $active_timein_session = true;
      }

      if (!isTimeEmpty($current_timesheet['pause_start']) && isTimeEmpty($current_timesheet['pause_end'])) {
          $has_active_pause = true;
      }
  }

  $system_settings = [];
  try {
      $settings_stmt = $conn->query("SELECT * FROM system_settings");
      while ($row = $settings_stmt->fetch(PDO::FETCH_ASSOC)) {
          $system_settings[$row['setting_key']] = $row['setting_value'];
      }
  } catch (Exception $e) {}

  $company_name = $system_settings['company_name'] ?? 'DICT Internship Timesheet';
  $company_header = $system_settings['company_header'] ?? 'Department of Information and Communication Technology';
  $logo_path = $system_settings['logo_path'] ?? './assets/images/Dict.png';
  ?>

  <?php if($active_timein_session): ?>
    <input type="hidden" id="php-timein-timestamp" value="<?php echo $_SESSION['timein_timestamp']; ?>">
  <?php endif; ?>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const hasActiveTimin = <?php echo $has_active_timein ? 'true' : 'false'; ?>;
      const selectedInternId = "<?php echo $selected_intern_id ?? ''; ?>";
      if (hasActiveTimin && selectedInternId) {
        sessionStorage.setItem('timein_intern_id', selectedInternId);
      }
    });
  </script>
</head>
<body class="bg-gray-50 min-h-screen">
  <div class="container mx-auto px-4 py-6">
    <?php include './components/header.php'; ?>
    <?php include './components/alert-message.php'; ?>
    <?php include './components/pause-status.php'; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Sidebar -->
      <div class="lg:col-span-3">
        <?php include './components/sidebar-actions.php'; ?>

        <!-- ✅ Export Timesheet Button -->
        <!--<div class="mt-4">
          <button onclick="openDownloadModal()"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow text-sm font-medium transition">
            <i class="fas fa-file-csv"></i>
            Export Timesheet CSV
          </button>
        </div>-->
      </div>

      <!-- Main -->
      <div class="lg:col-span-9">
        <?php include './components/time-management.php'; ?>
        <?php include './components/timesheet-status.php'; ?>
      </div>
    </div>

    <?php include './components/timesheet-records.php'; ?>
  </div>

  <!-- Modals -->
  <?php 
    include './components/modals/delete-modal.php';
    include './components/modals/reset-modal.php';
    include './components/modals/delete-all-modal.php';
    include './components/modals/export-modal.php';
    include './components/modals/overtime-modal.php';
    include './components/modals/pause-modal.php';
    include './components/modals/settings-modal.php'; 
    include './components/modals/note-modal.php';
    include './components/modals/time-adjustment-modal.php'; 
    include './components/modals/about-us-modal.php';
    include './components/modals/face-scanner-modal.php'; 
    include './components/modals/camera-capture-modal.php';
    include './components/modals/photo-gallery-modal.php';
    include './components/modals/admin-limit-modal.php';
    include './components/modals/cast-pm-out-modal.php';
    include './components/modals/exportInternDTR-modal.php';
    
  ?>

  <!-- Scripts -->
  <link rel="stylesheet" href="./assets/css/face-scanner.css">
  <script src="./assets/js/utils.js"></script>
  <script src="./assets/js/core.js"></script>
  <script src="./assets/js/live-counters.js"></script>
  <script src="./assets/js/delete-student.js"></script>
  <script src="./assets/js/reset-entries.js"></script>
<script src="./assets/js/export.js?v=<?php echo filemtime('./assets/js/export.js'); ?>"></script>
  <script src="./assets/js/overtime.js"></script>
  <script src="./assets/js/pause.js"></script>
  <script src="./assets/js/settings.js"></script>
  <script src="./assets/js/notes.js"></script>
  <script src="./assets/js/time-adjustments.js"></script>
  <script src="./assets/js/button-controls.js"></script>
  <script src="./assets/js/overtime-counter.js"></script>
  <script src="./assets/js/about-us.js"></script>
  <script src="./assets/js/index.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
  <script src="./assets/js/face-scanner.js"></script>
  <script src="./assets/js/camera-capture.js"></script>
  <script src="./assets/js/photo-gallery.js"></script>
  <script src="./assets/js/exportForm6.js"></script>

  <!-- JS for Export Modal -->
  <script>
    function openDownloadModal() {
      document.getElementById('downloadModal').classList.remove('hidden');
    }

    function closeDownloadModal() {
      document.getElementById('downloadModal').classList.add('hidden');
    }

    function openAdminModal() {
    const modal = document.getElementById('adminLimitModal');
    const overlay = document.getElementById('adminLimitOverlay');
    const content = document.getElementById('adminLimitContent');
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        overlay.classList.add('opacity-100');
        content.classList.remove('scale-90');
        content.classList.add('scale-100');
    }, 10);
}

function closeAdminModal() {
    const overlay = document.getElementById('adminLimitOverlay');
    const content = document.getElementById('adminLimitContent');
    
    overlay.classList.remove('opacity-100');
    content.classList.add('scale-90');
    
    setTimeout(() => {
        document.getElementById('adminLimitModal').classList.add('hidden');
    }, 300);
}

    function sendToGoogleSheet() {
      fetch('export_to_sheets.php')
        .then(res => res.text())
        .then(msg => {
          alert(msg);
          closeDownloadModal();
        })
        .catch(err => {
          alert("Failed to send to Google Sheet.");
          console.error(err);
        });
    }
function openCastPmModal() {
    const modal = document.getElementById('castPmModal');
    const overlay = document.getElementById('castPmOverlay');
    const content = document.getElementById('castPmContent');
    
    // 1. Reveal the modal container
    modal.classList.remove('hidden');
    
    // 2. Trigger the transition after a tiny delay
    setTimeout(() => {
        overlay.classList.add('opacity-100');
        content.classList.remove('scale-90');
        content.classList.add('scale-100');
    }, 10);
}

function closeCastPmModal() {
    const overlay = document.getElementById('castPmOverlay');
    const content = document.getElementById('castPmContent');
    
    // 1. Reverse the animations
    overlay.classList.remove('opacity-100');
    content.classList.add('scale-90');
    content.classList.remove('scale-100');
    
    // 2. Wait for animation to finish then hide
    setTimeout(() => {
        document.getElementById('castPmModal').classList.add('hidden');
    }, 300);
}


  </script>
</body>
</html>
