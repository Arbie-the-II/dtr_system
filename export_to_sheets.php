<?php
require __DIR__.'/vendor/autoload.php';
date_default_timezone_set('Asia/Manila');

/* ───────────────────────── 1. GOOGLE SHEETS ───────────────────────── */
$spreadsheetId = '1eZhJz5uYL3Zgs3hSYsw69rdOcNJt7poiN-KirhEzQUk';

$client = new Google_Client();
$client->setApplicationName('Timesheet Export');
$client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
$client->setAuthConfig(__DIR__.'/google-credentials.json');
$client->setAccessType('offline');
$service = new Google_Service_Sheets($client);

/* ───────────────────────── 2. DATABASE ───────────────────────────── */
try {
    $db = new PDO("mysql:host=localhost;dbname=ojt_timesheet_db", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    exit("❌ DB connect failed: ".$e->getMessage());
}

/* ───────────────────────── 3. HELPERS ─────────────────────────────── */
function fmtTime($t) {
    if (!$t || $t === '00:00:00') return '-';
    return date('g:i A', strtotime($t));        // 8:44 AM, 5:07 PM
}
function minsBetween($in, $out) {
    if(!$in || !$out || $in==='00:00:00' || $out==='00:00:00') return 0;
    return (strtotime($out) - strtotime($in)) / 60;
}
function toText($mins) {
    if ($mins <= 0) return '-';
    $h = floor($mins / 60);
    $m = $mins % 60;
    $parts = [];
    if ($h) $parts[] = $h.' hr'.($h>1?'s':'');
    if ($m) $parts[] = $m.' min';
    return implode(' ', $parts);
}
function sheetSafe($s) {                       // remove / * ? [ ] and trim 100 chars
    return mb_substr(preg_replace('/[\\/*?\[\]]/', '', $s), 0, 99);
}
function nameLastFirst($full) {
    $p = explode(' ', $full);
    $last = array_pop($p);
    return $last.', '.implode(' ', $p);
}

/* ───────────────────────── 4. FETCH DATA GROUPED BY INTERN ────────── */
$sql = "
 SELECT t.*, i.intern_name, i.intern_school, i.intern_id, i.required_hours_rendered
 FROM timesheet t
 JOIN interns i ON i.intern_id = t.intern_id
 ORDER BY i.intern_name, t.created_at";
$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$by = [];
foreach ($rows as $r) {
    $id = $r['intern_id'];
    $by[$id]['meta'] = [
        'name'     => nameLastFirst($r['intern_name']),
        'school'   => $r['intern_school'],
        'required' => $r['required_hours_rendered']
    ];
    $by[$id]['rows'][] = $r;
}

/* ───────────────────────── 5. EXPORT EACH INTERN ──────────────────── */
foreach ($by as $id => $bundle) {

    $tab = sheetSafe($bundle['meta']['name']);

    /* create sheet if missing */
    try {
        $service->spreadsheets->batchUpdate($spreadsheetId,
            new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
                'requests' => [[ 'addSheet' => ['properties'=>['title'=>$tab]] ]]
        ]));
    } catch (Exception $e) { /* sheet exists → ignore */ }

    /* clear */
    $service->spreadsheets_values->clear(
        $spreadsheetId, $tab, new Google_Service_Sheets_ClearValuesRequest()
    );

    /* header row */
    $data = [[
        'Date','Name (Last, First)','School',
        'AM Time In','AM Time Out','PM Time In','PM Time Out',
        'AM in Diff','AM out Diff','PM in Diff','PM out Diff',
        'AM Hours','PM Hours','Overtime Hours','Total Hours',
        'Required Hours','Rendered Hours','Remaining Hours'
    ]];

    $running = 0;                         // cumulative rendered hrs for Remaining
    $reqHr   = $bundle['meta']['required'];

    foreach ($bundle['rows'] as $r) {

        /* minutes worked */
        $amMin = minsBetween($r['am_timein'], $r['am_timeOut']);
        $pmMin = minsBetween($r['pm_timein'], $r['pm_timeout']);
        $otMin = 0;                       // extend if you store OT separately
        $dayMin = $amMin + $pmMin + $otMin;

        $running += $dayMin / 60;         // hours

        /* push row */
        $data[] = [
            date('n/j/Y', strtotime($r['created_at'])),
            $bundle['meta']['name'],
            $bundle['meta']['school'],

            fmtTime($r['am_timein']),
            fmtTime($r['am_timeOut']),
            fmtTime($r['pm_timein']),
            fmtTime($r['pm_timeout']),

            /* ───── Diff columns (now times) ───── */
            fmtTime($r['am_timein']),  // AM in Diff
            '12:00 PM',                // AM out Diff (constant schedule)
            '1:00 PM',                 // PM in Diff  (constant schedule)
            fmtTime($r['pm_timeout']), // PM out Diff

            /* hour breakdown */
            toText($amMin),
            toText($pmMin),
            $otMin ? toText($otMin) : '-',
            toText($dayMin),

            /* required / cumulative */
            $reqHr,
            $dayMin ? toText($dayMin) : '-',
            toText(max(0, $reqHr*60 - $running*60))
        ];
    }

    /* write to Sheets */
    $service->spreadsheets_values->update(
        $spreadsheetId,
        $tab.'!A1',
        new Google_Service_Sheets_ValueRange(['values'=>$data]),
        ['valueInputOption'=>'RAW']
    );
}

echo "✅ Exported ".count($by)." interns\n";
