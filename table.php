<?php
/**
 * table.php – liest die aktuelle Tabelle eines MaTo-Teams von fussball.de und speichert sie in
 * data/tabelle_<team>.json. Gibt die Tabelle als JSON aus.
 *
 * Aufruf: table.php?team=D1   (Teams + URLs in teams.config.php; ohne/ungültig → erstes Team)
 *
 * CACHING / DROSSELUNG:
 *   Ein erneuter Live-Abruf bei fussball.de erfolgt je Team frühestens nach $maxAge
 *   Sekunden (Standard: 180 = 3 Minuten). Innerhalb dieses Zeitfensters wird die bereits
 *   gespeicherte JSON-Datei des Teams direkt ausgeliefert. Massgeblich ist der
 *   Aenderungszeitstempel (mtime) der JSON-Datei.
 *
 *   Ist fussball.de nicht erreichbar, wird - falls vorhanden - die zuletzt
 *   gespeicherte Tabelle ausgeliefert (statt einer Fehlermeldung).
 */

$cfg   = require __DIR__ . '/teams.config.php';
$teams = array_filter($cfg['teams'] ?? [], fn($t) => !empty($t['tabelle']));
$team  = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', (string)($_GET['team'] ?? '')));
if (!isset($teams[$team])) $team = (string)array_key_first($teams);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($team === '') {
    http_response_code(404);
    echo json_encode(['table' => [], 'updated_at' => null, 'error' => 'no_team_configured']);
    exit;
}

$url        = $teams[$team]['tabelle'];
$outputFile = __DIR__ . '/data/tabelle_' . strtolower($team) . '.json';
$maxAge     = 180; // Sekunden bis zum naechsten erlaubten Live-Abruf (3 Minuten)

/**
 * Gespeicherte Tabelle ausliefern (nur wenn sie gueltiges JSON enthaelt).
 */
function serveCached($outputFile) {
    if (!is_file($outputFile)) return false;
    $cached = file_get_contents($outputFile);
    if ($cached === false || $cached === '') return false;
    if (!is_array(json_decode($cached, true))) return false;
    echo $cached;
    return true;
}

/**
 * Abbruch bei Live-Abruf: zuletzt gespeicherte Tabelle ausliefern,
 * sonst eine gueltige (leere) JSON-Fehlerstruktur.
 */
function failSoft($outputFile, $reason, $team) {
    if (serveCached($outputFile)) exit;              // letzte bekannte Tabelle (stale)
    http_response_code(503);
    echo json_encode(['team' => $team, 'table' => [], 'updated_at' => null, 'error' => $reason], JSON_UNESCAPED_UNICODE);
    exit;
}


/**
 * 0. Cache-Gate: frische JSON (< $maxAge) direkt ausliefern, ohne fussball.de.
 */
if (is_file($outputFile)) {
    $age = time() - filemtime($outputFile);
    if ($age < $maxAge && serveCached($outputFile)) {
        exit;
    }
}


/**
 * 1. HTML von fussball.de laden
 */
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/120 Safari/537.36',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: de-DE,de;q=0.9,en;q=0.8',
    ],
]);

$html = curl_exec($ch);

if ($html === false) {
    $err = curl_error($ch);
    curl_close($ch);
    failSoft($outputFile, 'fetch_failed: ' . $err, $team);
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    failSoft($outputFile, 'http_' . $httpCode, $team);
}


/**
 * 2. HTML parsen
 */
libxml_use_internal_errors(true);

$dom = new DOMDocument();
$dom->loadHTML(
    '<?xml encoding="UTF-8">' . $html,
    LIBXML_NOERROR | LIBXML_NOWARNING
);

libxml_clear_errors();

$xpath = new DOMXPath($dom);


/**
 * 3. Nur die Tabellenzeilen auslesen
 */
$rows = $xpath->query(
    '//div[@id="team-fixture-league-tables"]//table/tbody/tr'
);

if ($rows === false || $rows->length === 0) {
    failSoft($outputFile, 'no_rows', $team);
}

$table = [];

foreach ($rows as $row) {
    $cells = $xpath->query('./td', $row);

    if ($cells === false || $cells->length < 10) {
        continue;
    }

    $clubNode = $xpath->query(
        './/td[contains(@class, "column-club")]//div[contains(@class, "club-name")]',
        $row
    )->item(0);

    if (!$clubNode) {
        continue;
    }

    $name = trim(preg_replace('/\s+/', ' ', $clubNode->textContent));

    $rankRaw = trim($cells->item(1)->textContent);
    $rank = (int) rtrim($rankRaw, '.');

    $games  = (int) trim($cells->item(3)->textContent);
    $wins   = (int) trim($cells->item(4)->textContent);
    $draws  = (int) trim($cells->item(5)->textContent);
    $losses = (int) trim($cells->item(6)->textContent);

    $goalRatio = trim(preg_replace('/\s+/', ' ', $cells->item(7)->textContent));

    $goalDifference = (int) trim($cells->item(8)->textContent);
    $points = (int) trim($cells->item(9)->textContent);

    $goalsFor = 0;
    $goalsAgainst = 0;

    if (preg_match('/(\d+)\s*:\s*(\d+)/', $goalRatio, $matches)) {
        $goalsFor = (int) $matches[1];
        $goalsAgainst = (int) $matches[2];
    }

    $table[] = [
        'platz'          => $rank,
        'mannschaft'     => $name,
        'spiele'         => $games,
        'gewonnen'       => $wins,
        'unentschieden'  => $draws,
        'verloren'       => $losses,
        'tore'           => $goalsFor,
        'gegentore'      => $goalsAgainst,
        'torverhaeltnis' => $goalRatio,
        'tordifferenz'   => $goalDifference,
        'punkte'         => $points,
    ];
}

// Falls trotz Zeilen nichts Verwertbares extrahiert wurde: alte Tabelle behalten
if (empty($table)) {
    failSoft($outputFile, 'empty_parse', $team);
}


/**
 * 4. JSON-Struktur erzeugen
 */
$result = [
    'team' => $team,
    'updated_at' => date(DATE_ATOM),
    'source' => 'fussball.de',
    'source_url' => $url,
    'table' => $table,
];

$json = json_encode(
    $result,
    JSON_PRETTY_PRINT |
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

if ($json === false) {
    failSoft($outputFile, 'json_encode_failed', $team);
}


/**
 * 5. JSON-Datei speichern (aktualisiert zugleich den mtime = Basis fuers Caching)
 */
if (file_put_contents($outputFile, $json) === false) {
    echo $json; // konnte nicht speichern -> zumindest frisches Ergebnis ausgeben
    exit;
}


/**
 * 6. JSON ausgeben
 */
echo $json;
