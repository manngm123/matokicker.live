<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store');

define('DATA_DIR', __DIR__ . '/data/');
require_once __DIR__ . '/auth.inc.php';
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);

function loadJson($file) {
    $path = DATA_DIR . $file;
    if (!file_exists($path)) return [];
    return json_decode(file_get_contents($path), true) ?? [];
}
function saveJson($file, $data) {
    file_put_contents(DATA_DIR . $file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function resultsFile($matchday_id) {
    return 'results_' . preg_replace('/[^a-zA-Z0-9_\-]/', '', $matchday_id) . '.json';
}
/**
 * Standard-Heimmannschaft einer Begegnung:
 *  1. MaTo-Mannschaft(en) des Spieltags (team_ids ∩ Heimmannschaften) – erste Fundstelle
 *  2. Fallback: erste Heimmannschaft insgesamt
 */
function getDefaultHomeTeamId($matchdayId = '') {
    static $homeIds = null;
    if ($homeIds === null) {
        $homeIds = [];
        foreach (loadJson('teams.json') as $t) if (!empty($t['is_home'])) $homeIds[] = $t['id'];
    }
    if ($matchdayId !== '') {
        foreach (loadJson('matchdays.json') as $md) {
            if (($md['id'] ?? '') !== $matchdayId) continue;
            foreach (($md['team_ids'] ?? []) as $tid) if (in_array($tid, $homeIds, true)) return $tid;
            break;
        }
    }
    return $homeIds[0] ?? null;
}
// Pseudo-Spieler – im Backend feste Auswahloptionen, keine anlegbaren Spielernamen
const RESERVED_PLAYER_NAMES = ['eigentor', 'noname'];
function normName($n) {
    $n = trim((string)$n);
    return function_exists('mb_strtolower') ? mb_strtolower($n, 'UTF-8') : strtolower($n);
}
/**
 * Friert die aktuellen Team-Namen in den Ergebnis-Eintrag ein.
 * Nur auflösbare IDs überschreiben den Namen – bereits eingefrorene
 * Namen gelöschter Teams bleiben unangetastet.
 */
function freezeTeamNames(&$r) {
    static $byId = null;
    if ($byId === null) {
        $byId = [];
        foreach (loadJson('teams.json') as $t) $byId[$t['id']] = $t['name'];
    }
    $hid = $r['home_team_id'] ?? null;
    if ($hid && isset($byId[$hid])) $r['home_team_name'] = $byId[$hid];
    $gid = $r['guest_team_id'] ?? null;
    if ($gid && isset($byId[$gid])) $r['guest_team_name'] = $byId[$gid];
}
function migrateStatus(&$r) {
    if (!isset($r['status']))        $r['status']        = 'not_started';
    if (!isset($r['started_at']))    $r['started_at']    = null;
    if (!isset($r['ended_at']))      $r['ended_at']      = null;
    if (!isset($r['scorers']))       $r['scorers']       = [];
    if (!isset($r['scorer_scores'])) $r['scorer_scores'] = [];
    // Halbzeit-Felder (Standard: 1. Halbzeit, keine Pausen-/2.-HZ-Zeitstempel)
    if (!isset($r['current_half']))              $r['current_half']         = 1;
    if (!array_key_exists('half1_end_at', $r))   $r['half1_end_at']         = null;
    if (!array_key_exists('second_half_start_at', $r)) $r['second_half_start_at'] = null;
    // Karten als allgemeine Spielereignisse: [{player,type,team,minute}]
    if (!isset($r['cards']) || !is_array($r['cards'])) $r['cards'] = [];
    // Pokal: Tor-Metadaten [{phase:1|2|3, pen:bool}], Elfmeterschießen [{team,player,scored}]
    if (!isset($r['scorer_info']) || !is_array($r['scorer_info'])) $r['scorer_info'] = [];
    if (!isset($r['pen_shots'])   || !is_array($r['pen_shots']))   $r['pen_shots']   = [];
    // Verschossene Elfmeter im Spiel: [{team, player, minute, phase, at}]
    if (!isset($r['pen_missed'])  || !is_array($r['pen_missed']))  $r['pen_missed']  = [];
    foreach (['half2_end_at', 'et_start_at', 'et_end_at'] as $k)
        if (!array_key_exists($k, $r)) $r[$k] = null;
    // home_team_id: falls nicht gesetzt, Standard-Heimmannschaft einsetzen
    if (empty($r['home_team_id']))   $r['home_team_id']  = getDefaultHomeTeamId((string)($r['matchday_id'] ?? ''));
    if ($r['status'] === 'waiting')  $r['status'] = 'not_started';
    if ($r['status'] === 'done')     $r['status'] = 'finished';
    if (isset($r['locked']) && $r['locked'] && $r['status'] !== 'finished') $r['status'] = 'finished';
}

/**
 * Sicherheitsnetz beim Entfernen eines Teams:
 *  1. Namen in alle Results-Dateien einfrieren
 *  2. Team-ID aus allen Spielerzuordnungen entfernen
 */
function freezeAndCleanupTeam($id, $name) {
    $files = glob(DATA_DIR . 'results_*.json');
    if (file_exists(DATA_DIR . 'results.json')) $files[] = DATA_DIR . 'results.json';
    foreach ($files as $f) {
        $rs = json_decode(file_get_contents($f), true);
        if (!is_array($rs)) continue;
        $chg = false;
        foreach ($rs as &$r) {
            if (($r['home_team_id'] ?? null) === $id && ($r['home_team_name'] ?? null) !== $name) { $r['home_team_name'] = $name; $chg = true; }
            if (($r['guest_team_id'] ?? null) === $id && ($r['guest_team_name'] ?? null) !== $name) { $r['guest_team_name'] = $name; $chg = true; }
        }
        unset($r);
        if ($chg) file_put_contents($f, json_encode($rs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    $players = loadJson('players.json');
    $pchg = false;
    foreach ($players as &$p) {
        if (is_array($p['team_id'] ?? null)) {
            $new = array_values(array_filter($p['team_id'], fn($t) => $t !== $id));
            if ($new !== $p['team_id']) { $p['team_id'] = $new; $pchg = true; }
        } elseif (($p['team_id'] ?? null) === $id) {
            $p['team_id'] = []; $pchg = true;
        }
    }
    unset($p);
    if ($pchg) saveJson('players.json', $players);
}

$action = $_GET['action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// ── Rechteprüfung ─────────────────────────────────────────────────────────
// Lesen ist öffentlich (Live-Ansicht). Schreiben: Backend-Login (Session oder Admin-Cookie)
// oder – nur Begegnungen speichern – ein gültiger Reporter-Schlüssel für genau einen Spieltag.
if (isset($_COOKIE[session_name()])) session_start(['read_and_close' => true]);   // ohne Sperre, nur lesen
$IS_ADMIN  = mk_is_admin();
$RP_TOKEN  = (string)($_SERVER['HTTP_X_REPORTER_TOKEN'] ?? '');
$RP_MD     = ($RP_TOKEN !== '' && !$IS_ADMIN) ? mk_reporter_md($RP_TOKEN) : null;
$PUBLIC    = ['get_teams', 'get_players', 'get_matchdays', 'get_results', 'get_last_update', 'heartbeat'];
$REPORTER  = ['save_result'];
if (!$IS_ADMIN && !in_array($action, $PUBLIC, true)) {
    if ($RP_TOKEN !== '') {
        if (!$RP_MD) respond(['error' => 'Die Reporter-Freigabe wurde beendet oder ist abgelaufen.'], 403);
        if (!in_array($action, $REPORTER, true)) respond(['error' => 'Diese Aktion ist für Reporter nicht erlaubt.'], 403);
    } else {
        respond(['error' => 'Nicht angemeldet.'], 401);
    }
}

// TEAMS
if ($action === 'get_teams')   { respond(loadJson('teams.json')); }
if ($action === 'save_teams')  {
    $teams = $body['teams'] ?? [];
    // Mehrere Heimmannschaften (MaTo-Teams) sind erlaubt
    foreach ($teams as &$t) $t['is_home'] = !empty($t['is_home']);
    unset($t);
    // Sicherheitsnetz: Teams, die im neuen Stand fehlen, wie gelöschte behandeln
    $newIds = array_flip(array_column($teams, 'id'));
    foreach (loadJson('teams.json') as $old) {
        if (!isset($newIds[$old['id']])) freezeAndCleanupTeam($old['id'], $old['name']);
    }
    saveJson('teams.json', $teams); respond(['success' => true]);
}
if ($action === 'delete_team') {
    $id = $body['id'] ?? '';
    // Sicherheitsnetz: Namen einfrieren und Spielerzuordnungen bereinigen
    $name = null;
    foreach (loadJson('teams.json') as $t) if ($t['id'] === $id) { $name = $t['name']; break; }
    if ($id !== '' && $name !== null) freezeAndCleanupTeam($id, $name);
    $t  = array_values(array_filter(loadJson('teams.json'), fn($t) => $t['id'] !== $id));
    saveJson('teams.json', $t); respond(['success' => true]);
}

// PLAYERS — jetzt team_id als Array oder String
if ($action === 'get_players')   { respond(loadJson('players.json')); }
if ($action === 'save_players')  {
    $in = $body['players'] ?? [];
    if (!is_array($in)) respond(['error' => 'Ungültige Spielerliste.'], 400);
    $stored = [];
    foreach (loadJson('players.json') as $p) $stored[$p['id']] = $p;
    $out = []; $seen = []; $dupes = []; $reserved = [];
    foreach ($in as $p) {
        $id   = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($p['id'] ?? ''));
        $name = trim((string)($p['name'] ?? ''));
        if ($name === '') continue;
        if ($id === '') $id = substr(bin2hex(random_bytes(6)), 0, 9);
        $key = normName($name);
        // Reservierte Namen nur für bereits gespeicherte Altdatensätze gleichen Namens zulassen
        $wasSame = isset($stored[$id]) && normName($stored[$id]['name'] ?? '') === $key;
        if (in_array($key, RESERVED_PLAYER_NAMES, true) && !$wasSame) { $reserved[] = $name; continue; }
        if (isset($seen[$key])) { $dupes[] = $name; continue; }
        $seen[$key] = true;
        $tids = $p['team_id'] ?? [];
        if (!is_array($tids)) $tids = $tids !== '' && $tids !== null ? [$tids] : [];
        $tids = array_values(array_unique(array_filter(array_map('strval', $tids), fn($x) => $x !== '')));
        $out[] = ['id' => $id, 'name' => $name, 'team_id' => $tids];
    }
    if ($reserved) respond(['error' => 'Reservierter Name (feste Auswahloption): ' . implode(', ', array_unique($reserved))], 400);
    if ($dupes)    respond(['error' => 'Spielername mehrfach vergeben: ' . implode(', ', array_unique($dupes)) . '. Bitte eindeutig benennen (z. B. „Till B“).'], 400);
    // Sicherheitsnetz: gespeicherte Spieler, die im Request fehlen, bleiben erhalten (Löschen nur über delete_player)
    $sent = array_flip(array_column($out, 'id'));
    foreach ($stored as $sid => $sp) {
        if (isset($sent[$sid])) continue;
        $k = normName($sp['name'] ?? '');
        if ($k !== '' && isset($seen[$k])) respond(['error' => 'Spielername mehrfach vergeben: ' . $sp['name'] . '.'], 400);
        $out[] = $sp;
    }
    saveJson('players.json', $out); respond(['success' => true, 'players' => $out]);
}
if ($action === 'delete_player') {
    $id = $body['id'] ?? '';
    $p  = array_values(array_filter(loadJson('players.json'), fn($p) => $p['id'] !== $id));
    saveJson('players.json', $p); respond(['success' => true]);
}

// MATCHDAYS
if ($action === 'get_matchdays') {
    $mds = loadJson('matchdays.json');
    foreach ($mds as &$m) {
        if (!isset($m['reporter'])) continue;
        if ($IS_ADMIN) {   // Status für die Anzeige im Backend (wird nicht gespeichert)
            $m['reporter']['active'] = mk_reporter_active($m);
            $m['reporter']['until']  = mk_reporter_until($m);
        } else unset($m['reporter']);   // Freigabe-Schlüssel nie öffentlich ausliefern
    }
    unset($m);
    respond($mds);
}
// Reporter-Freigabe (nur Admin): Schlüssel erzeugen bzw. beenden
if ($action === 'reporter_enable' || $action === 'reporter_disable') {
    $id  = (string)($body['id'] ?? '');
    $mds = loadJson('matchdays.json');
    foreach ($mds as &$m) {
        if (($m['id'] ?? '') !== $id) continue;
        if ($action === 'reporter_disable') {
            unset($m['reporter']);
            saveJson('matchdays.json', $mds); respond(['success' => true]);
        }
        if (time() >= mk_reporter_until($m)) respond(['error' => 'Der Spieltag liegt in der Vergangenheit – eine Freigabe ist nicht mehr möglich.'], 400);
        if (!mk_reporter_active($m)) $m['reporter'] = ['token' => bin2hex(random_bytes(16)), 'created_at' => time()];
        saveJson('matchdays.json', $mds);
        respond(['success' => true, 'token' => $m['reporter']['token'], 'until' => mk_reporter_until($m)]);
    }
    unset($m);
    respond(['error' => 'Spieltag nicht gefunden.'], 404);
}
if ($action === 'save_matchday') {
    $mds = loadJson('matchdays.json'); $md = $body;
    if (empty($md['id'])) $md['id'] = uniqid('md_');
    // Reporter-Freigabe wird nur über reporter_enable/-disable geändert
    unset($md['reporter']);
    foreach ($mds as $e) if (($e['id'] ?? '') === $md['id'] && isset($e['reporter'])) { $md['reporter'] = $e['reporter']; break; }
    // Kader je MaTo-Team: {teamId: [playerIds]} – nur gültige Struktur übernehmen
    if (isset($md['squads'])) {
        $sq = [];
        if (is_array($md['squads'])) {
            foreach ($md['squads'] as $tid => $ids) {
                $tid = (string)$tid;
                if ($tid === '' || !is_array($ids)) continue;
                $sq[$tid] = array_values(array_unique(array_filter(array_map('strval', $ids), fn($x) => $x !== '')));
            }
        }
        if ($sq) $md['squads'] = $sq; else unset($md['squads']);
    }
    $found = false;
    foreach ($mds as &$e) { if ($e['id'] === $md['id']) { $e = $md; $found = true; break; } }
    if (!$found) $mds[] = $md;
    saveJson('matchdays.json', $mds); respond(['success' => true, 'id' => $md['id']]);
}
if ($action === 'delete_matchday') {
    $id  = $body['id'] ?? '';
    $mds = array_values(array_filter(loadJson('matchdays.json'), fn($m) => $m['id'] !== $id));
    saveJson('matchdays.json', $mds);
    $rfile = DATA_DIR . resultsFile($id);
    if (file_exists($rfile)) unlink($rfile);
    if (file_exists($rfile . '.lock')) @unlink($rfile . '.lock');
    respond(['success' => true]);
}

// RESULTS
if ($action === 'get_results') {
    $mid = $_GET['matchday_id'] ?? '';
    if (!$mid) respond([]);
    $rs = loadJson(resultsFile($mid));
    foreach ($rs as &$r) migrateStatus($r);
    respond($rs);
}
if ($action === 'save_result') {
    $r = $body;
    // Editor-Metadaten (werden nicht gespeichert)
    $expected  = $r['_expected_updated_at'] ?? null;   // Editor
    $expRev    = $r['_expected_rev'] ?? null;           // Reporting (Backend + Reporter)
    $keepNames = !empty($r['_keep_names']);
    unset($r['_expected_updated_at'], $r['_expected_rev'], $r['_keep_names']);
    if (empty($r['id'])) $r['id'] = uniqid('r_');
    $mid = (string)($r['matchday_id'] ?? '');
    // Reporter dürfen nur Begegnungen ihres freigegebenen Spieltags speichern
    if ($RP_MD && $mid !== (string)$RP_MD['id']) respond(['error' => 'Diese Begegnung gehört nicht zum freigegebenen Spieltag.'], 403);
    if ($mid === '') respond(['error' => 'Spieltag fehlt.'], 400);
    // Sperre je Spieltag: Lesen–Prüfen–Schreiben ohne Überschneidung zweier Geräte
    $lock = @fopen(DATA_DIR . resultsFile($mid) . '.lock', 'c');
    if ($lock) flock($lock, LOCK_EX);
    $rs  = loadJson(resultsFile($mid));
    $prev = null;
    foreach ($rs as $e) if (($e['id'] ?? null) === $r['id']) { $prev = $e; break; }
    // Konflikt, wenn die Begegnung seit dem Laden auf einem anderen Gerät geändert wurde
    if ($expected !== null && $prev !== null && (int)($prev['updated_at'] ?? 0) !== (int)$expected)
        respond(['error' => 'Die Begegnung wurde zwischenzeitlich geändert (z. B. im Reporting). Bitte neu laden und die Korrektur erneut eintragen.', 'conflict' => true], 409);
    if ($expRev !== null && $prev !== null && (int)($prev['rev'] ?? 0) !== (int)$expRev)
        respond(['error' => 'Die Begegnung wurde auf einem anderen Gerät geändert.', 'conflict' => true], 409);
    $r['rev'] = (int)($prev['rev'] ?? 0) + 1;
    $r['updated_at'] = time();
    migrateStatus($r);
    freezeTeamNames($r);   // Namen sofort einfrieren – Archiv bleibt unabhängig von teams.json
    // Editor: eingefrorene Namen unveränderter Teams behalten (Historie bleibt bei späteren Umbenennungen stabil)
    if ($keepNames && $prev !== null) {
        foreach (['home', 'guest'] as $side) {
            if (($prev[$side . '_team_id'] ?? null) === ($r[$side . '_team_id'] ?? null) && !empty($prev[$side . '_team_name']))
                $r[$side . '_team_name'] = $prev[$side . '_team_name'];
        }
    }
    $found = false;
    foreach ($rs as &$e) { if ($e['id'] === $r['id']) { $e = $r; $found = true; break; } }
    if (!$found) $rs[] = $r;
    saveJson(resultsFile($mid), $rs);
    if ($lock) { flock($lock, LOCK_UN); fclose($lock); }
    respond(['success' => true, 'id' => $r['id'], 'rev' => $r['rev'], 'updated_at' => $r['updated_at']]);
}
if ($action === 'delete_result') {
    $id  = $body['id'] ?? '';
    $mid = $body['matchday_id'] ?? '';
    if (!$mid) {
        foreach (glob(DATA_DIR . 'results_*.json') as $f) {
            $rs = json_decode(file_get_contents($f), true) ?? [];
            foreach ($rs as $r) { if ($r['id'] === $id) { $mid = $r['matchday_id']; break 2; } }
        }
    }
    if ($mid) {
        $rs = array_values(array_filter(loadJson(resultsFile($mid)), fn($r) => $r['id'] !== $id));
        saveJson(resultsFile($mid), $rs);
    }
    respond(['success' => true]);
}
if ($action === 'get_last_update') {
    $mid = $_GET['matchday_id'] ?? '';
    if (!$mid) respond(['ts' => 0]);
    $ts = 0; $sig = [];
    foreach (loadJson(resultsFile($mid)) as $r) {
        if (($r['updated_at'] ?? 0) > $ts) $ts = $r['updated_at'];
        $sig[] = ($r['id'] ?? '') . ':' . (int)($r['rev'] ?? 0);
    }
    sort($sig, SORT_STRING);
    // sig: Stand aller Begegnungen (id:rev) – gleiche Bildung wie im Reporting (rpSignature)
    respond(['ts' => $ts, 'sig' => implode('|', $sig)]);
}

// ONLINE-ZÄHLER (Heartbeat)
// Frontend pingt alle 30s mit einer Client-ID; Einträge älter als 75s gelten als offline.
if ($action === 'heartbeat') {
    $cid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $body['cid'] ?? '');
    $now = time();
    $path = DATA_DIR . 'online.json';
    $count = 1;
    $fp = @fopen($path, 'c+');
    if ($fp && flock($fp, LOCK_EX)) {
        $raw  = stream_get_contents($fp);
        $list = json_decode($raw, true);
        if (!is_array($list)) $list = [];
        // Veraltete Einträge entfernen
        $list = array_filter($list, fn($ts) => ($now - (int)$ts) <= 75);
        if ($cid) $list[$cid] = $now;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($list));
        fflush($fp);
        flock($fp, LOCK_UN);
        $count = count($list);
    }
    if ($fp) fclose($fp);
    respond(['count' => $count]);
}

respond(['error' => 'Unknown action'], 400);
