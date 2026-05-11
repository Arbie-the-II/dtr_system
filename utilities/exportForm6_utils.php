<?php
/**
 * Form 6 (DTR) Export Utility
 * Handles A4 layout, 12-hour formatting, and precise decimal hour totals.
 */

function generateForm6Preview($conn, $intern_id) {
    // 1. Fetch Intern Details (Updated to use 'supervisor' column)
    $stmt = $conn->prepare("SELECT Intern_Name, project_assigned, supervisor FROM interns WHERE Intern_id = ?");
    $stmt->execute([$intern_id]);
    $intern = $stmt->fetch();

    if (!$intern) { 
        echo "<div class='alert alert-danger'>Intern data not found.</div>"; 
        return; 
    }

    // 2. Fetch Timesheet Data
    $stmt = $conn->prepare("SELECT * FROM timesheet WHERE intern_id = ? ORDER BY created_at ASC");
    $stmt->execute([$intern_id]);
    $entries = $stmt->fetchAll();

    /**
     * Helper: Format to 12-hour (e.g., 7:00)
     */
    function formatTo12hr($timeString) {
        if (!$timeString || $timeString == '00:00:00' || $timeString == '-' || $timeString == '00:00:00.000000') return '&nbsp;';
        return date("g:i", strtotime($timeString));
    }

    // Setup Supervisor name display logic
    $supervisor_display = !empty($intern['supervisor']) ? htmlspecialchars($intern['supervisor']) : '&nbsp;';

    // ── Header Section ──
    echo '<h3 class="text-center mb-5" style="font-weight: bold; font-family: Arial; font-size: 20px; text-transform: uppercase;">Internship Time Sheet</h3>';

    echo '<div class="row mb-4" style="font-family: Arial; font-size: 13px;">';
    echo '  <div class="col-7 text-start">';
    echo '    <div class="mb-2">Name: <span class="line">' . htmlspecialchars($intern['Intern_Name']) . '</span></div>';
    echo '    <div class="mb-2">Company: <span class="line">DICT IX & BASULTA</span></div>';
    echo '    <div class="mb-2">Department: <span class="line">' . htmlspecialchars($intern['project_assigned'] ?? 'N/A') . '</span></div>';
    echo '  </div>';
    echo '  <div class="col-5 text-start">';
    echo '    <div class="mb-2">Supervisor: <span class="line">' . $supervisor_display . '</span></div>';
    echo '  </div>';
    echo '</div>';

    // ── Table Section ──
    echo '<table class="table table-f6" style="width:100%; border-collapse: collapse; font-family: Arial; font-size: 12px;">';
    echo '  <thead>';
    echo '    <tr>';
    echo '      <th rowspan="2" style="border: 1px solid black; width: 14%;">Date</th>';
    echo '      <th colspan="2" style="border: 1px solid black; width: 20%;">AM</th>';
    echo '      <th colspan="2" style="border: 1px solid black; width: 20%;">PM</th>';
    echo '      <th rowspan="2" style="border: 1px solid black; width: 14%;">No. of Hours</th>';
    echo '      <th rowspan="2" style="border: 1px solid black; width: 16%;">Running Total</th>';
    echo '      <th rowspan="2" style="border: 1px solid black; width: 16%;">Signature</th>';
    echo '    </tr>';
    echo '    <tr>';
    echo '      <th style="border: 1px solid black;">In</th><th style="border: 1px solid black;">Out</th>';
    echo '      <th style="border: 1px solid black;">In</th><th style="border: 1px solid black;">Out</th>';
    echo '    </tr>';
    echo '  </thead>';
    echo '  <tbody>';

    $running_total_seconds = 0;
    if (count($entries) > 0) {
        foreach ($entries as $row) {
            $row_date = date('Y-m-d', strtotime($row['created_at']));
            $day_seconds = 0;

            // 1. Fetch Dynamic Rule (8 or 10) for this date
            $hist = $conn->prepare("SELECT setting_value FROM settings_history 
                                    WHERE effective_date <= ? AND setting_key = 'max_daily_hours'
                                    ORDER BY effective_date DESC LIMIT 1");
            $hist->execute([$row_date]);
            $db_rule = $hist->fetchColumn();
            $day_limit = ($db_rule !== false) ? (float)$db_rule : 8.0;

            // 2. AM Logic: Strict 12:00 PM Cutoff
            if (!empty($row['am_timein']) && $row['am_timein'] !== '00:00:00' && !empty($row['am_timeOut']) && $row['am_timeOut'] !== '00:00:00') {
                $am_in = max(strtotime($row_date . ' ' . $row['am_timein']), strtotime($row_date . ' 07:00:00'));
                $am_out = min(strtotime($row_date . ' ' . $row['am_timeOut']), strtotime($row_date . ' 12:00:00'));
                
                $am_diff = max(0, $am_out - $am_in);
                $am_max_limit = ($day_limit <= 8) ? 4 : 5;
                $day_seconds += min($am_diff, $am_max_limit * 3600);
            }

            // 3. PM Logic: Cast PM & 30-min Grace Period
            if (!empty($row['pm_timein']) && $row['pm_timein'] !== '00:00:00' && !empty($row['pm_timeout']) && $row['pm_timeout'] !== '00:00:00') {
                $custom_stmt = $conn->prepare("SELECT custom_pm_out, is_active FROM intern_custom_limits 
                                              WHERE intern_id = ? AND effective_date <= ? 
                                              ORDER BY effective_date DESC LIMIT 1");
                $custom_stmt->execute([$intern_id, $row_date]);
                $custom_row = $custom_stmt->fetch(PDO::FETCH_ASSOC);

                $off_start = strtotime($row_date . ' 13:00:00');
                $off_end = ($custom_row && $custom_row['is_active'] == 1) 
                    ? strtotime($row_date . ' ' . $custom_row['custom_pm_out'])
                    : strtotime($row_date . ' ' . (13 + (($day_limit <= 8) ? 4 : 5)) . ':00:00');

                $grace_end = $off_end + (30 * 60); 
                $pm_in = max(strtotime($row_date . ' ' . $row['pm_timein']), $off_start);
                $pm_out = min(strtotime($row_date . ' ' . $row['pm_timeout']), $grace_end);
                
                $day_seconds += max(0, $pm_out - $pm_in);
            }

            // 4. Precision Summation & Rounding
            $actual_hrs_decimal = $day_seconds / 3600;
            if ($actual_hrs_decimal >= ($day_limit - 0.01)) {
                $day_total_final = $day_limit;
            } else {
                // Round down to the nearest minute to prevent second-drift
                $day_total_final = floor($day_seconds / 60) / 60;
            }
            
            $running_total_seconds += ($day_total_final * 3600);
            $running_total_display = $running_total_seconds / 3600;
            
            echo '<tr style="text-align: center;">';
            echo '  <td style="border: 1px solid black;">' . date('m/d/Y', strtotime($row['created_at'])) . '</td>';
            echo '  <td style="border: 1px solid black;">' . formatTo12hr($row['am_timein']) . '</td>';
            echo '  <td style="border: 1px solid black;">' . formatTo12hr($row['am_timeOut']) . '</td>';
            echo '  <td style="border: 1px solid black;">' . formatTo12hr($row['pm_timein']) . '</td>';
            echo '  <td style="border: 1px solid black;">' . formatTo12hr($row['pm_timeout']) . '</td>';
            echo '  <td style="border: 1px solid black; font-weight: bold;">' . ($day_total_final > 0 ? number_format($day_total_final, 2) : '-') . '</td>';
            echo '  <td style="border: 1px solid black;">' . number_format($running_total_display, 2) . '</td>';
            echo '  <td style="border: 1px solid black;">&nbsp;</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8" class="py-5 text-center text-muted" style="border: 1px solid black;">No time logs available.</td></tr>';
    }

    echo '  </tbody>';
    echo '</table>';

    // ── Signatories Section ──
    echo '<div class="row" style="font-family: Arial; margin-top: 60px;">';
    echo '  <div class="col-6 text-center">';
    echo '    <p style="font-size: 11px; margin-bottom: 45px; text-align: left;">I certify that the above is true and correct</p>';
    echo '    <div style="border-top: 1px solid black; width: 80%; margin: 0 auto; padding-top: 5px; font-weight: bold; font-size: 12px;">' . htmlspecialchars($intern['Intern_Name']) . '</div>';
    echo '    <div style="font-size: 10px;">Student Trainee\'s Signature</div>';
    echo '  </div>';
    echo '  <div class="col-6 text-center">';
    echo '    <p style="font-size: 11px; margin-bottom: 30px; line-height: 1.2; text-align: left;">This certifies to the correctness of the work hours entered by the trainee</p>';
    echo '    <div style="border-top: 1px solid black; width: 80%; margin: 0 auto; padding-top: 5px; font-weight: bold; font-size: 12px;">' . ($supervisor_display !== '&nbsp;' ? $supervisor_display : '____________________') . '</div>';
    echo '    <div style="font-size: 10px;">Supervisor\'s Signature</div>';
    echo '  </div>';
    echo '</div>';
}
?>