<?php
/**
 * Export Handler
 * Processes requests for both individual and master CSV exports
 */

require_once 'connection/conn.php'; 
require_once 'utilities/exportInterns_utils.php'; 

// 1. Handle Master Export (Full Internship Record)
if (isset($_POST['export_master_report'])) {
    $filename = "Daily_Time_Record_DICT_Internship_" . date('Y-m-d') . ".csv";
    
    $query = "SELECT i.Intern_Name, i.Intern_School, t.created_at, t.am_timein, 
                     t.am_timeOut, t.pm_timein, t.pm_timeout, t.am_hours_worked, 
                     t.pm_hours_worked, t.overtime_hours, t.day_total_hours 
              FROM interns i 
              LEFT JOIN timesheet t ON i.Intern_id = t.intern_id 
              ORDER BY i.Intern_Name ASC, t.created_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $output = fopen('php://temp', 'r+');
    
    // Headers matching your CSV requirements exactly
    fputcsv($output, ['Date', 'Student Name', 'School', 'AM Time In', 'AM Time Out', 'PM Time In', 'PM Time Out', 'AM Hours', 'PM Hours', 'Overtime Hours', 'Total Hours']);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['created_at'] ? date('Y-m-d', strtotime($row['created_at'])) : '-',
            $row['Intern_Name'],
            $row['Intern_School'],
            formatTimeForCSV($row['am_timein']),
            formatTimeForCSV($row['am_timeOut']),
            formatTimeForCSV($row['pm_timein']),
            formatTimeForCSV($row['pm_timeout']),
            formatDurationForCSV($row['am_hours_worked']),
            formatDurationForCSV($row['pm_hours_worked']),
            formatDurationForCSV($row['overtime_hours']),
            formatDurationForCSV($row['day_total_hours'])
        ]);
    }
    
    rewind($output);
    $content = stream_get_contents($output);
    fclose($output);
    outputCSVForDownload($filename, $content);
}

// 2. Handle Individual Export
if (isset($_POST['export_csv']) && isset($_POST['intern_id'])) {
    $intern_id = $_POST['intern_id'];
    
    $stmt = $conn->prepare("SELECT Intern_Name, Intern_School FROM interns WHERE Intern_id = ?");
    $stmt->execute([$intern_id]);
    $intern = $stmt->fetch();
    
    $name = str_replace(' ', '_', $intern['Intern_Name']);
    $filename = "{$name}_timesheet_" . date('Y-m-d') . ".csv";

    $query = "SELECT created_at, am_timein, am_timeOut, pm_timein, pm_timeout, 
                     am_hours_worked, pm_hours_worked, overtime_hours, day_total_hours 
              FROM timesheet WHERE intern_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute([$intern_id]);

    $output = fopen('php://temp', 'r+');
    fputcsv($output, ['Date', 'Student Name', 'School', 'AM Time In', 'AM Time Out', 'PM Time In', 'PM Time Out', 'AM Hours', 'PM Hours', 'Overtime Hours', 'Total Hours']);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            date('Y-m-d', strtotime($row['created_at'])),
            $intern['Intern_Name'],
            $intern['Intern_School'],
            formatTimeForCSV($row['am_timein']),
            formatTimeForCSV($row['am_timeOut']),
            formatTimeForCSV($row['pm_timein']),
            formatTimeForCSV($row['pm_timeout']),
            formatDurationForCSV($row['am_hours_worked']),
            formatDurationForCSV($row['pm_hours_worked']),
            formatDurationForCSV($row['overtime_hours']),
            formatDurationForCSV($row['day_total_hours'])
        ]);
    }

    rewind($output);
    $content = stream_get_contents($output);
    fclose($output);
    outputCSVForDownload($filename, $content);
}