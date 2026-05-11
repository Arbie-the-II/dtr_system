<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

date_default_timezone_set('Asia/Manila');

// Check for email parameter
if (!isset($_GET['email']) || empty($_GET['email']) || !filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)) {
    exit('❌ Invalid or missing email address.');
}
$email = $_GET['email'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=ojt_timesheet_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $filename = "all_interns_timesheet_" . date('Y-m-d') . ".csv";
    $csvPath = __DIR__ . '/' . $filename;

    $output = fopen($csvPath, 'w');
    fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

    fputcsv($output, [
        'Date', 'Name (Last, First)', 'School', 'AM Time In', 'AM Time Out',
        'PM Time In', 'PM Time Out', 'AM Hours', 'PM Hours', 'Overtime Hours',
        'Total Hours', 'Required Hours', 'Rendered Hours', 'Remaining Hours'
    ]);

    function formatTimeStrict($time) {
        if (empty($time) || $time === '00:00:00') return '-';
        $timestamp = strtotime($time);
        return ($timestamp === false || date('H:i:s', $timestamp) === '00:00:00') ? '-' : date('h:i A', $timestamp);
    }

    function durationToMinutes($start, $end) {
        if (!$start || !$end || $start === '-' || $end === '-') return 0;
        $startDT = new DateTime($start);
        $endDT = new DateTime($end);
        if ($endDT < $startDT) return 0;
        return (int) round(($endDT->getTimestamp() - $startDT->getTimestamp()) / 60);
    }

    function parseOvertimeToMinutes($str) {
        if (empty($str) || $str === '00:00.0' || $str === '-') return 0;
        sscanf($str, "%d:%f", $h, $m);
        return ((int)$h * 60) + (int)$m;
    }

    function formatMinutesAsText($minutes) {
        if ($minutes <= 0) return '-';
        $hrs = floor($minutes / 60);
        $mins = $minutes % 60;
        $parts = [];
        if ($hrs > 0) $parts[] = $hrs . ' hr' . ($hrs > 1 ? 's' : '');
        if ($mins > 0) $parts[] = $mins . ' min';
        return implode(' ', $parts);
    }

    function formatHoursAsText($hoursFloat) {
        $totalMinutes = (int) round($hoursFloat * 60);
        return formatMinutesAsText($totalMinutes);
    }

    function formatNameLastFirst($fullName) {
        $parts = explode(' ', $fullName);
        $last = array_pop($parts);
        $first = implode(' ', $parts);
        return "$last, $first";
    }

    $rendered_stmt = $conn->query("SELECT interns.intern_id, interns.required_hours_rendered AS required_hours FROM interns");
    $hours_map = [];
    while ($r = $rendered_stmt->fetch(PDO::FETCH_ASSOC)) {
        $hours_map[$r['intern_id']] = ['required' => $r['required_hours']];
    }

    $stmt = $conn->prepare("SELECT t.*, i.Intern_Name as intern_name, i.Intern_School as intern_school, i.Intern_id 
        FROM timesheet t 
        JOIN interns i ON t.intern_id = i.Intern_id 
        ORDER BY t.created_at ASC, i.Intern_School ASC, i.Intern_Name ASC");
    $stmt->execute();

    $running_hours = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $created_at = !empty($row['created_at']) ? date('n/j/Y', strtotime($row['created_at'])) : '-';

        $am_in = $row['am_timein'] ?? null;
        $am_out = $row['am_timeOut'] ?? null;
        $pm_in = $row['pm_timein'] ?? null;
        $pm_out = $row['pm_timeout'] ?? null;

        $formatted_am_in = formatTimeStrict($am_in);
        $formatted_am_out = formatTimeStrict($am_out);
        $formatted_pm_in = formatTimeStrict($pm_in);
        $formatted_pm_out = formatTimeStrict($pm_out);

        $am_minutes = durationToMinutes($am_in, $am_out);
        $pm_minutes = durationToMinutes($pm_in, $pm_out);
        $overtime_minutes = parseOvertimeToMinutes($row['overtime_hours'] ?? '00:00.0');
        $total_minutes = $am_minutes + $pm_minutes + $overtime_minutes;

        $intern_id = $row['Intern_id'];
        $required = $hours_map[$intern_id]['required'] ?? 0;
        $today_rendered_hours = $total_minutes / 60.0;

        if (!isset($running_hours[$intern_id])) {
            $running_hours[$intern_id] = 0.0;
        }
        $running_hours[$intern_id] += $today_rendered_hours;

        $rendered_text = formatHoursAsText($today_rendered_hours);
        $remaining_text = formatHoursAsText(max(0, $required - $running_hours[$intern_id]));

        fputcsv($output, [
            $created_at,
            formatNameLastFirst($row['intern_name'] ?? '-'),
            $row['intern_school'] ?? '-',
            $formatted_am_in,
            $formatted_am_out,
            $formatted_pm_in,
            $formatted_pm_out,
            formatMinutesAsText($am_minutes),
            formatMinutesAsText($pm_minutes),
            formatMinutesAsText($overtime_minutes),
            formatMinutesAsText($total_minutes),
            $required,
            $rendered_text,
            $remaining_text
        ]);
    }

    fclose($output);

    // 📤 Email the CSV
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'dtc.dictr9@gmail.com'; // your Gmail
    $mail->Password   = 'nlne jvsl xcmi jxmw';    // your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('dtc.dictr9@gmail.co', 'DICT Timesheet System');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your Timesheet CSV';
    $mail->Body    = 'Please find the attached CSV file of your timesheet.';
    $mail->addAttachment($csvPath, $filename);

    $mail->send();
    echo "✅ Email has been sent to <b>$email</b> with the attached timesheet.";

} catch (PDOException $e) {
    echo "<b>Database Error:</b> " . $e->getMessage();
} catch (Exception $e) {
    echo "<b>Mailer Error:</b> " . $e->getMessage();
}
?>
