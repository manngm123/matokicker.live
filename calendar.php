<?php
/**
 * calendar.php – liefert die Spieltage als Kalender-Feed (iCalendar / .ics).
 *
 * Aufruf:
 *   calendar.php            → alle KOMMENDEN Spieltage der Abo-Teams (teams.config.php: 'abo' => true)
 *   calendar.php?id=<md_id> → nur dieser eine Spieltag (jedes Team)
 *   &dl=1                   → als Datei-Download erzwingen (statt im Kalender öffnen)
 *
 * Wird als "text/calendar; inline" ausgeliefert, damit ein per webcal://
 * geöffneter Link direkt in der Kalender-App landet (kein Datei-Download).
 *
 * Änderungs-Erkennung: Jedes VEVENT bekommt LAST-MODIFIED + SEQUENCE, damit
 * Google/Apple Änderungen an bereits abonnierten Terminen als Update erkennen
 * (nicht als Dublette). Zeiten werden mit TZID=Europe/Berlin + VTIMEZONE
 * ausgeliefert – unabhängig von der Server-Zeitzone.
 */

define('DATA_DIR', __DIR__ . '/data/');
$CFG = require __DIR__ . '/teams.config.php';

function cal_load($file) {
    $p = DATA_DIR . $file;
    if (!file_exists($p)) return [];
    return json_decode(file_get_contents($p), true) ?: [];
}
function cal_esc($s) {
    return str_replace(["\\", ";", ",", "\n"], ["\\\\", "\\;", "\\,", "\\n"], (string)$s);
}

/**
 * Ermittelt den letzten Änderungszeitpunkt (Unix-TS) eines Spieltags.
 * Bevorzugt ein Zeitstempel-Feld im Datensatz, fällt sonst auf die
 * mtime der matchdays.json zurück. Dient als Basis für LAST-MODIFIED
 * und als monoton steigende SEQUENCE.
 */
function cal_mtime($m, $fallback) {
    foreach (['updated_at', 'updated', 'modified', 'last_modified', 'mtime', 'ts'] as $k) {
        if (!empty($m[$k])) {
            $v = $m[$k];
            if (is_numeric($v)) return (int)$v;
            $t = strtotime((string)$v);
            if ($t) return $t;
        }
    }
    return $fallback;
}

// MaTo-Teams eines Spieltags als Kürzel (Haupt-Team zuerst); Teams ohne Kürzel → Name
$homeById = [];
foreach (cal_load('teams.json') as $t) if (!empty($t['is_home'])) $homeById[$t['id']] = $t['name'];
function cal_team_key($name, $cfg) {
    if (preg_match('/\(([^)]+)\)\s*$/', (string)$name, $mm) && isset($cfg['teams'][trim($mm[1])])) return trim($mm[1]);
    return null;
}
function cal_md_teams($m, $homeById, $cfg) {
    $ids = [];
    if (!empty($m['home_team_id'])) $ids[] = $m['home_team_id'];
    foreach (($m['team_ids'] ?? []) as $id) if (isset($homeById[$id]) && !in_array($id, $ids, true)) $ids[] = $id;
    $out = [];
    foreach ($ids as $id) {
        if (!isset($homeById[$id])) continue;
        $k = cal_team_key($homeById[$id], $cfg);
        $out[] = ['key' => $k, 'label' => $k ?? $homeById[$id]];
    }
    // Altes Team ohne Kürzel nur, wenn kein konfiguriertes Team (D1/D2/C1) dabei ist
    $cfgOnly = array_values(array_filter($out, fn($t) => $t['key'] !== null));
    return $cfgOnly ?: $out;
}
function cal_in_abo($teams, $cfg) {
    foreach ($teams as $t) if ($t['key'] !== null && !empty($cfg['teams'][$t['key']]['abo'])) return true;
    return false;
}
function cal_title($m, $teams, $cfg) {
    $event = trim((string)($m['event'] ?? ''));
    $keys  = array_values(array_filter(array_map(fn($t) => $t['key'], $teams)));
    $type  = $cfg['typen'][$m['type'] ?? ''] ?? '';
    $head  = 'MaTo' . ($keys ? ' ' . implode('/', $keys) : ' Kicker');
    return $head . ' – ' . ($type !== '' ? $type . ': ' : '') . $event;
}

$mds   = cal_load('matchdays.json');
$id    = isset($_GET['id']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['id']) : '';
$today = strtotime(date('Y-m-d') . ' 00:00:00');

// Fallback-Zeitstempel für Events ohne eigenes Änderungsdatum.
$fileMtime = @filemtime(DATA_DIR . 'matchdays.json') ?: time();

// Auswahl treffen: einzelner Spieltag per ID, sonst alle kommenden der Abo-Teams.
$sel = [];
foreach ($mds as $m) {
    $d = substr(str_replace('/', '-', $m['date'] ?? ''), 0, 10);
    if (strlen($d) < 10) continue;
    $teams = cal_md_teams($m, $homeById, $CFG);
    if ($id !== '') {
        if (($m['id'] ?? '') === $id) $sel[] = [$m, $d, $teams];
    } else {
        if (strtotime($d . ' 00:00:00') >= $today && cal_in_abo($teams, $CFG)) $sel[] = [$m, $d, $teams];
    }
}
usort($sel, fn($a, $b) => strcmp($a[1], $b[1]));

// Kalendername: Abo-Teams (z. B. „MaTo Kicker D1“)
$aboKeys = array_keys(array_filter($CFG['teams'], fn($t) => !empty($t['abo'])));
$calName = 'MaTo Kicker' . ($aboKeys ? ' ' . implode('/', $aboKeys) : '') . ' Spieltage';

// Basis-URL zur index.php (für Termin-Link)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'matokicker.live';
$dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$stamp  = gmdate('Ymd\THis\Z');
$tz     = new DateTimeZone('Europe/Berlin');

$out  = "BEGIN:VCALENDAR\r\n";
$out .= "VERSION:2.0\r\n";
$out .= "PRODID:-//MaTo Kicker//Live//DE\r\n";
$out .= "CALSCALE:GREGORIAN\r\n";
$out .= "METHOD:PUBLISH\r\n";
$out .= "X-WR-CALNAME:" . cal_esc($calName) . "\r\n";
$out .= "X-WR-TIMEZONE:Europe/Berlin\r\n";

// Zeitzonen-Definition, damit Clients die lokale Zeit korrekt (inkl. Sommer-/
// Winterzeit) interpretieren – unabhängig von X-WR-TIMEZONE.
$out .= "BEGIN:VTIMEZONE\r\n";
$out .= "TZID:Europe/Berlin\r\n";
$out .= "BEGIN:DAYLIGHT\r\n";
$out .= "TZOFFSETFROM:+0100\r\n";
$out .= "TZOFFSETTO:+0200\r\n";
$out .= "TZNAME:CEST\r\n";
$out .= "DTSTART:19700329T020000\r\n";
$out .= "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\r\n";
$out .= "END:DAYLIGHT\r\n";
$out .= "BEGIN:STANDARD\r\n";
$out .= "TZOFFSETFROM:+0200\r\n";
$out .= "TZOFFSETTO:+0100\r\n";
$out .= "TZNAME:CET\r\n";
$out .= "DTSTART:19701025T030000\r\n";
$out .= "RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\r\n";
$out .= "END:STANDARD\r\n";
$out .= "END:VTIMEZONE\r\n";

foreach ($sel as [$m, $d, $teams]) {
    $y = substr($d, 0, 4); $mo = substr($d, 5, 2); $da = substr($d, 8, 2);
    $dc = $y . $mo . $da;
    $event = $m['event'] ?? '';

    // Startzeit bestimmen: bevorzugt das Feld "time", sonst aus dem Eventnamen.
    $hh = null; $mm = null;
    if (preg_match('/^(\d{1,2}):(\d{2})$/', trim($m['time'] ?? ''), $t)) {
        $hh = $t[1]; $mm = $t[2];
    } elseif (preg_match('/(\d{1,2}):(\d{2})/', $event, $t)) {
        $hh = $t[1]; $mm = $t[2];
    }
    if ($hh !== null) {
        // Termin beginnt 45 Min früher, dauert 3 Std. Zeiten explizit in
        // Europe/Berlin rechnen, damit sie nicht von der Server-TZ abhängen.
        $start = new DateTime(sprintf('%s %02d:%02d:00', $d, $hh, $mm), $tz);
        $start->modify('-45 minutes');
        $end = (clone $start)->modify('+3 hours');
        $dt = "DTSTART;TZID=Europe/Berlin:" . $start->format('Ymd\THis') . "\r\n"
            . "DTEND;TZID=Europe/Berlin:" . $end->format('Ymd\THis') . "\r\n";
    } else {
        // keine Uhrzeit → Ganztags-Termin
        $end = date('Ymd', strtotime($d . ' +1 day'));
        $dt  = "DTSTART;VALUE=DATE:{$dc}\r\nDTEND;VALUE=DATE:{$end}\r\n";
    }

    $link = "{$scheme}://{$host}{$dir}/index.php?md=" . rawurlencode($m['id'] ?? '');
    $uid  = ($m['id'] ?? uniqid('md_')) . '@matokicker.live';

    // Änderungs-Metadaten: LAST-MODIFIED für die menschlich lesbare Zeit,
    // SEQUENCE als monoton steigende Ganzzahl (Unix-TS der letzten Änderung).
    $mtime    = cal_mtime($m, $fileMtime);
    $lastMod  = gmdate('Ymd\THis\Z', $mtime);
    $sequence = $mtime;

    $out .= "BEGIN:VEVENT\r\n";
    $out .= "UID:{$uid}\r\n";
    $out .= "DTSTAMP:{$stamp}\r\n";
    $out .= "LAST-MODIFIED:{$lastMod}\r\n";
    $out .= "SEQUENCE:{$sequence}\r\n";
    $out .= $dt;
    $out .= "SUMMARY:" . cal_esc(cal_title($m, $teams, $CFG)) . "\r\n";
    $out .= "LOCATION:" . cal_esc('MaTo Kicker Live') . "\r\n";
    $out .= "DESCRIPTION:" . cal_esc('Live-Ergebnisse: ' . $link) . "\r\n";
    $out .= "URL:{$link}\r\n";
    $out .= "END:VEVENT\r\n";
}
$out .= "END:VCALENDAR\r\n";

header('Content-Type: text/calendar; charset=utf-8');
if (isset($_GET['dl'])) {
    header('Content-Disposition: attachment; filename="mato-spieltage.ics"');
} else {
    header('Content-Disposition: inline; filename="mato-spieltage.ics"');
}
echo $out;
