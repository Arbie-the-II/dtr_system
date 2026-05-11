<?php 
// Start session to keep track of user data across pages
session_start();

// Set timezone to Philippines for consistent time tracking
date_default_timezone_set('Asia/Manila');

// Include core system files
include 'connection/conn.php';
include 'timesheet_photos.php';

// Include helper functions organized by category
include 'utilities/schema_utils.php';  // Database structure management
include 'utilities/reset_entries.php';
include 'utilities/session_handler.php';
include 'utilities/time_utils.php';
include 'utilities/overtime_utils.php';
include 'utilities/delete_utils.php';
include 'utilities/export_utils.php';
include 'utilities/pause_utils.php';
include 'utilities/time_entry_utils.php';

// Make sure our database has all the tables and fields it needs
$schema_results = ensureTimesheetSchema($conn);

// Initialize message variable for notifications
$message = ""; // Default to empty (no messages)
$selected_intern_id = isset($_GET['intern_id']) ? $_GET['intern_id'] : (isset($_POST['intern_id']) ? $_POST['intern_id'] : '');

// Check if we have any messages from previous operations
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
    // Once displayed, remove the message from session
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Get all interns for the dropdown menu
$interns_stmt = $conn->prepare("SELECT * FROM interns ORDER BY Intern_Name ASC");
$interns_stmt->execute();

// Set up query to fetch timesheet records with school info
$timesheet_stmt = $conn->prepare("SELECT t.*, i.Intern_School as intern_school, i.Required_Hours_Rendered as required_hours, DATE(t.created_at) as render_date 
                                 FROM timesheet t 
                                 JOIN interns i ON t.intern_id = i.Intern_id 
                                 WHERE t.intern_id = :intern_id 
                                 ORDER BY t.created_at DESC");

// If a specific intern is selected, load their information
if (!empty($selected_intern_id)) {
    $timesheet_stmt->bindParam(':intern_id', $selected_intern_id);
    $timesheet_stmt->execute();
    
    // Get personal details of the selected intern
    $intern_details_stmt = $conn->prepare("SELECT * FROM interns WHERE Intern_id = :intern_id");
    $intern_details_stmt->bindParam(':intern_id', $selected_intern_id);
    $intern_details_stmt->execute();
    $intern_details = $intern_details_stmt->fetch(PDO::FETCH_ASSOC);

    // Get today's timesheet entry for the selected intern
    $today_timesheet_stmt = $conn->prepare("SELECT * FROM timesheet WHERE intern_id = :intern_id AND DATE(created_at) = CURRENT_DATE()");
    $today_timesheet_stmt->bindParam(':intern_id', $selected_intern_id);
    $today_timesheet_stmt->execute();
    $current_timesheet = $today_timesheet_stmt->fetch(PDO::FETCH_ASSOC);

/* --- START OF MODIFIED OVERALL TIME CALCULATION --- */
// A simple SUM(), we loop through records to apply historical caps
/* --- OVERALL TOTAL TIME CALCULATION --- */
$total_seconds = 0;

    $calc_query = "SELECT 
                    am_timein, am_timeOut, pm_timein, pm_timeout, created_at, intern_id,
                    (SELECT setting_value FROM settings_history 
                     WHERE setting_key = 'max_daily_hours' 
                     AND effective_date <= DATE(t.created_at) 
                     ORDER BY effective_date DESC LIMIT 1) as daily_limit
                  FROM timesheet t 
                  WHERE t.intern_id = :intern_id";
                  
    $calc_stmt = $conn->prepare($calc_query);
    $calc_stmt->bindParam(':intern_id', $selected_intern_id);
    $calc_stmt->execute();

    while ($row = $calc_stmt->fetch(PDO::FETCH_ASSOC)) {
        $row_date = date('Y-m-d', strtotime($row['created_at']));
        $day_limit = (!empty($row['daily_limit'])) ? floatval($row['daily_limit']) : 8.0;
        $day_seconds = 0;

        // 1. AM Calculation
        if (!empty($row['am_timein']) && $row['am_timein'] !== '00:00:00' && !empty($row['am_timeOut']) && $row['am_timeOut'] !== '00:00:00') {
            $am_in = max(strtotime($row_date . ' ' . $row['am_timein']), strtotime($row_date . ' 07:00:00'));
            $am_out = min(strtotime($row_date . ' ' . $row['am_timeOut']), strtotime($row_date . ' 12:00:00'));
            
            $am_diff = max(0, $am_out - $am_in);
            $am_max_limit = ($day_limit <= 8) ? 4 : 5;
            $day_seconds += min($am_diff, $am_max_limit * 3600);
        }

        // 2. PM Calculation
        if (!empty($row['pm_timein']) && $row['pm_timein'] !== '00:00:00' && !empty($row['pm_timeout']) && $row['pm_timeout'] !== '00:00:00') {
            $custom_stmt = $conn->prepare("SELECT custom_pm_out, is_active FROM intern_custom_limits 
                                          WHERE intern_id = ? AND effective_date <= ? 
                                          ORDER BY effective_date DESC LIMIT 1");
            $custom_stmt->execute([$row['intern_id'], $row_date]);
            $custom_row = $custom_stmt->fetch(PDO::FETCH_ASSOC);

            $off_start = strtotime($row_date . ' 13:00:00');
            $off_end = ($custom_row && $custom_row['is_active'] == 1) 
                ? strtotime($row_date . ' ' . $custom_row['custom_pm_out'])
                : strtotime($row_date . ' ' . (13 + (($day_limit <= 8) ? 5 : 6)) . ':00:00');

            $grace_end = $off_end + (30 * 60);
            $pm_in = max(strtotime($row_date . ' ' . $row['pm_timein']), $off_start);
            $pm_out = min(strtotime($row_date . ' ' . $row['pm_timeout']), $grace_end);
            
            $day_seconds += max(0, $pm_out - $pm_in);
        }

        // 3. THE FIX: STRIP SECONDS FROM THE DAILY TOTAL
        // This rounds the day's work down to the nearest minute, 
        // so leftover seconds don't accumulate into extra minutes.
        $clean_day_seconds = floor(min($day_seconds, $day_limit * 3600) / 60) * 60;
        $total_seconds += $clean_day_seconds;
    }
    
    // Final output formatted as HH:MM:SS
    $total_time_rendered = secondsToTime($total_seconds);
} else {

    // No intern selected, return empty result
    $timesheet_stmt = $conn->prepare("SELECT 1 WHERE 0");
    $timesheet_stmt->execute();
}

// Handle all form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Time In functionality
    if (isset($_POST['time_in']) && !empty($_POST['intern_id'])) {
        $intern_id = $_POST['intern_id'];
        
        // Record the time-in event
        $result = recordTimeIn($conn, $intern_id);
        
        // Save result message for display after redirect
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = $result['message_type'];
        
        // If this is a new record, attach any pending photo
        if ($result['success'] && isset($result['new_record']) && $result['new_record'] && isset($_SESSION['pending_photo'])) {
            // Get the newly created timesheet record ID
            $new_record_stmt = $conn->prepare("SELECT record_id FROM timesheet WHERE intern_id = :intern_id AND DATE(created_at) = CURRENT_DATE()");
            $new_record_stmt->bindParam(':intern_id', $intern_id);
            $new_record_stmt->execute();
            $new_record = $new_record_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($new_record && isset($new_record['record_id'])) {
                // Save the pending photo with the timesheet record
                save_pending_photo($conn, $intern_id, $new_record['record_id']);
            }
        }

        // Redirect to prevent form resubmission on refresh
        header("Location: index.php?intern_id=" . $intern_id);
        exit();
    }

    // Time Out functionality
    if (isset($_POST['time_out']) && !empty($_POST['intern_id'])) {
        $intern_id = $_POST['intern_id'];
        
        // Record the time-out event
        $result = recordTimeOut($conn, $intern_id);
        
        // Save result message for display after redirect
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = $result['message_type'];
        
        // Clean up any session variables that are no longer needed
        if(isset($_SESSION['timein_timestamp'])) unset($_SESSION['timein_timestamp']);
        if(isset($_SESSION['timein_intern_id'])) unset($_SESSION['timein_intern_id']);
        if(isset($_SESSION['overtime_active'])) unset($_SESSION['overtime_active']);
        if(isset($_SESSION['overtime_intern_id'])) unset($_SESSION['overtime_intern_id']);
        if(isset($_SESSION['overtime_start'])) unset($_SESSION['overtime_start']);
        
        // Redirect to prevent form resubmission on refresh
        header("Location: index.php?intern_id=" . $intern_id);
        exit();
    }
    
    // Reset entries functionality (clears timesheet for today)
    if (isset($_POST['reset_entries']) && !empty($_POST['intern_id'])) {
        $intern_id = $_POST['intern_id'];
        
        // Reset today's entries for this intern
        $_SESSION['message'] = resetEntries($conn, $intern_id);
        
        // Clear related session variables
        clearTimesheetSessionVariables();
        
        // Redirect to prevent form resubmission on refresh
        header("Location: index.php?intern_id=" . $intern_id);
        exit();
    }

    // Update Daily Hour Limit with History Tracking
    if (isset($_POST['action']) && $_POST['action'] === 'update_admin_settings') {
        $new_limit = $_POST['new_hour_limit'];
        $eff_date = $_POST['effective_date']; 
        $input_pin = $_POST['admin_pin'];

        // 1. Fetch the stored PIN hash
        $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'admin_pin_hash'");
        $stmt->execute();
        $stored_hash = $stmt->fetchColumn();

        $is_pin_valid = false;
        $is_initial_setup = empty($stored_hash);

        // 2. PIN Validation or Initial Setup
        if ($is_initial_setup) {
            $new_hash = password_hash($input_pin, PASSWORD_DEFAULT);
            $save_pin = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) 
                                       VALUES ('admin_pin_hash', ?) 
                                       ON DUPLICATE KEY UPDATE setting_value = ?");
            $save_pin->execute([$new_hash, $new_hash]);
            $is_pin_valid = true;
        } else {
            if (password_verify($input_pin, $stored_hash)) {
                $is_pin_valid = true;
            }
        }

        // 3. Process the update if PIN is valid
        if ($is_pin_valid) {
            try {
                $conn->beginTransaction();

                $update_sys = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'max_daily_hours'");
                $update_sys->execute([$new_limit]);

                $check_stmt = $conn->prepare("SELECT id FROM settings_history WHERE setting_key = 'max_daily_hours' AND effective_date = ?");
                $check_stmt->execute([$eff_date]);
                $existing_id = $check_stmt->fetchColumn();

                if ($existing_id) {
                    $update_hist = $conn->prepare("UPDATE settings_history SET setting_value = ?, created_at = NOW() WHERE id = ?");
                    $update_hist->execute([$new_limit, $existing_id]);
                } else {
                    $insert_hist = $conn->prepare("INSERT INTO settings_history (setting_key, setting_value, effective_date) VALUES ('max_daily_hours', ?, ?)");
                    $insert_hist->execute([$new_limit, $eff_date]);
                }

                $conn->commit();
                
                $setup_msg = $is_initial_setup ? " Master PIN has been set." : "";
                $_SESSION['message'] = "Limit set to $new_limit hrs starting $eff_date.$setup_msg";
                $_SESSION['message_type'] = "success";

            } catch (Exception $e) {
                $conn->rollBack();
                $_SESSION['message'] = "Database Error: " . $e->getMessage();
                $_SESSION['message_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "Invalid PIN. Changes not saved.";
            $_SESSION['message_type'] = "error";
        }

        header("Location: index.php");
        exit();
    }

    if ($_POST['action'] === 'remove_cast_pm') {
    $intern_csv = $_POST['intern_id_csv'] ?? '';
    $target_date = $_POST['target_date'];
    $input_pin = $_POST['admin_pin'];

    // 1. PIN Check
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'admin_pin_hash'");
    $stmt->execute();
    $stored_hash = $stmt->fetchColumn();

    if ($stored_hash && password_verify($input_pin, $stored_hash)) {
        $intern_ids = !empty($intern_csv) ? explode(',', $intern_csv) : [];

        if (!empty($intern_ids)) {
            try {
                $conn->beginTransaction();
                
                // 2. Prepare DELETE statement
                $del_rule = $conn->prepare("DELETE FROM intern_custom_limits 
                                           WHERE intern_id = ? AND effective_date = ?");
                
                // 3. Prepare Timesheet Reset statement
                $reset_ts = $conn->prepare("UPDATE timesheet SET pm_timeout = '00:00:00' 
                                           WHERE intern_id = ? AND created_at = ?");

                foreach ($intern_ids as $id) {
                    $id = trim($id);
                    if (empty($id)) continue;

                    $del_rule->execute([$id, $target_date]);
                    $reset_ts->execute([$id, $target_date]);
                }

                $conn->commit();
                $_SESSION['message'] = "Records deleted and timesheet reset for $target_date.";
                $_SESSION['message_type'] = "success";
            } catch (Exception $e) {
                $conn->rollBack();
                $_SESSION['message'] = "Error: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['message'] = "Invalid Admin PIN.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: index.php");
    exit();
}
    // Pause time functionality
    if (isset($_POST['pause_time']) && !empty($_POST['intern_id'])) {
        $intern_id = $_POST['intern_id'];
        $result = startPause($conn, $intern_id);
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = $result['message_type'];
        
        if ($result['success'] && isset($result['pause_active']) && $result['pause_active']) {
            $_SESSION['pause_active'] = true;
            $_SESSION['pause_intern_id'] = $result['pause_intern_id'];
            $_SESSION['pause_start'] = $result['pause_start'];
        }
        
        header("Location: index.php?intern_id=" . $intern_id);
        exit();
    }

    // Resume time functionality
    if (isset($_POST['resume_time']) && !empty($_POST['intern_id'])) {
        $intern_id = $_POST['intern_id'];
        $result = resumeWork($conn, $intern_id);
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = $result['message_type'];
        
        if ($result['success']) {
            unset($_SESSION['pause_active']);
            unset($_SESSION['pause_intern_id']);
            unset($_SESSION['pause_start']);
        }
        
        header("Location: index.php?intern_id=" . $intern_id);
        exit();
    }

    // Export all attendance records to CSV
    if (isset($_POST['export_all'])) {
        $date_from = !empty($_POST['date_from']) ? $_POST['date_from'] : null;
        $date_to = !empty($_POST['date_to']) ? $_POST['date_to'] : null;
        $csv_data = exportAttendanceSummaryToCSV($conn, $date_from, $date_to);
        outputCSVForDownload($csv_data['filename'], $csv_data['content']);
    }

    // --- START BULK CAST PM OUT LOGIC (New Continuous Version) ---

if ($_POST['action'] === 'cast_pm_timeout') {
    $intern_csv = $_POST['intern_id_csv'] ?? ''; 
    $target_date = $_POST['target_date'];
    $new_time = $_POST['new_pm_timeout'];
    $input_pin = $_POST['admin_pin'];
    
    // Capture the status (1 = Active, 0 = Disabled)
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

    // 1. PIN Validation
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'admin_pin_hash'");
    $stmt->execute();
    $stored_hash = $stmt->fetchColumn();

    if ($stored_hash && password_verify($input_pin, $stored_hash)) {
        $intern_ids = !empty($intern_csv) ? explode(',', $intern_csv) : [];

        if (!empty($intern_ids)) {
            try {
                $conn->beginTransaction();

                // 2. This query detects the unique (intern_id + effective_date)
                // If it finds a match, it OVERWRITES the time and the active status.
                $upsert_rule = $conn->prepare("INSERT INTO intern_custom_limits 
                    (intern_id, custom_pm_out, effective_date, is_active) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    custom_pm_out = VALUES(custom_pm_out), 
                    is_active = VALUES(is_active)");

                // 3. Update the live timesheet record
                $update_timesheet = $conn->prepare("UPDATE timesheet SET pm_timeout = ? WHERE intern_id = ? AND created_at = ?");

                foreach ($intern_ids as $id) {
                    $id = trim($id);
                    if (empty($id)) continue;

                    // This handles the change from 1 to 0 (or vice versa) for the same date
                    $upsert_rule->execute([$id, $new_time, $target_date, $is_active]);
                    
                    // Only push the time to the timesheet if we are activating
                    if ($is_active == 1) {
                        $update_timesheet->execute([$new_time, $id, $target_date]);
                    }
                }

                $conn->commit();
                
                $action_text = ($is_active == 1) ? "updated" : "disabled";
                $_SESSION['message'] = "Successfully $action_text records for $target_date.";
                $_SESSION['message_type'] = "success";

            } catch (Exception $e) {
                $conn->rollBack();
                $_SESSION['message'] = "Error: " . $e->getMessage();
                $_SESSION['message_type'] = "error";
            }
        }
    } else {
        $_SESSION['message'] = "Invalid Admin PIN.";
        $_SESSION['message_type'] = "error";
    }

    header("Location: index.php");
    exit();
}
    // Redundant Admin update block (kept to maintain your file structure)
    if (isset($_POST['action']) && $_POST['action'] === 'update_admin_settings') {
        $input_pin = $_POST['admin_pin'];
        $new_limit = $_POST['new_hour_limit'];
        $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'admin_pin_hash'");
        $stmt->execute();
        $stored_hash = $stmt->fetchColumn();

        if (empty($stored_hash)) {
            $new_hash = password_hash($input_pin, PASSWORD_DEFAULT);
            $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'admin_pin_hash'")->execute([$new_hash]);
            $stored_hash = $new_hash;
        }

        if (password_verify($input_pin, $stored_hash)) {
            $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'max_daily_hours'");
            $stmt->execute([$new_limit]);
            $_SESSION['message'] = "Daily limit successfully updated to $new_limit hours.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Access Denied: Invalid Security PIN.";
            $_SESSION['message_type'] = "error";
        }
        header("Location: index.php");
        exit();
    }
    
    if (!empty($selected_intern_id)) {
        header("Location: index.php?intern_id=" . $selected_intern_id);
    } else {
        header("Location: index.php");
    }
    exit();
}

// Get selected student name for confirmation dialogs
$selected_student_name = '';
if (!empty($selected_intern_id)) {
    $name_stmt = $conn->prepare("SELECT Intern_Name FROM interns WHERE Intern_id = :intern_id");
    $name_stmt->bindParam(':intern_id', $selected_intern_id);
    $name_stmt->execute();
    $name_data = $name_stmt->fetch(PDO::FETCH_ASSOC);
    $selected_student_name = $name_data ? $name_data['Intern_Name'] : '';
}

// Check if overtime feature should be available for this intern
$overtime_enabled = isOvertimeEligible($conn, $selected_intern_id);
?>