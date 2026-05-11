<?php
/**
 * Export Utility Functions for DICT Internship DTR System
 */

/**
 * Formats time strings for CSV readability (e.g., 08:00:00 -> 08:00 AM)
 */
function formatTimeForCSV($time) {
    if (empty($time) || $time == '00:00:00' || $time == '00:00:00.000000') {
        return '-';
    }
    return date('h:i A', strtotime($time));
}

/**
 * Formats duration for CSV (e.g., 04:18:00 -> 4 hr 18 min)
 */
function formatDurationForCSV($duration) {
    if (empty($duration) || $duration == '00:00:00' || $duration == '00:00:00.000000') {
        return '-';
    }
    
    // Remove microseconds if they exist in the DB string
    $duration = preg_replace('/\.\d+/', '', $duration);
    $parts = explode(':', $duration);
    
    $hours = (int)$parts[0];
    $minutes = (int)$parts[1];
    
    $output = "";
    if ($hours > 0) $output .= $hours . " hr ";
    if ($minutes > 0 || $hours == 0) $output .= $minutes . " min";
    
    return trim($output);
}

/**
 * Forces a browser download of the generated CSV content
 */
function outputCSVForDownload($filename, $content) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $content;
    exit();
}