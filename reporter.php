<?php
/**
 * reporter.php – Reporting für einen einzelnen, freigegebenen Spieltag
 *
 * Zugriff nur über den Freigabe-Link aus dem Backend (Reiter 2, Knopf 📣): reporter.php?t=<Schlüssel>
 * Kein Passwort – der Link ist der Schlüssel. Die Freigabe endet um Mitternacht nach dem Spieltag
 * oder sofort per „Beenden“ im Backend. Die API lässt mit dem Schlüssel nur das Speichern von
 * Begegnungen dieses Spieltags zu (kein Löschen, keine Stammdaten).
 * Reporting-Oberfläche und -Logik kommen aus reporting.inc.php – identisch zum Backend.
 */
require_once __DIR__ . '/auth.inc.php';
require_once __DIR__ . '/reporting.inc.php';
header('X-Robots-Tag: noindex, nofollow');
header('Referrer-Policy: no-referrer');   // Schlüssel nicht über den Referer an fremde Seiten weitergeben

$token = preg_replace('/[^a-f0-9]/', '', strtolower((string)($_GET['t'] ?? '')));
$md    = $token !== '' ? mk_reporter_md($token) : null;
function rp_h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$dateTxt = $untilTxt = '';
if ($md) {
    $dateTxt  = date('d.m.Y', strtotime(substr(str_replace('/', '-', (string)$md['date']), 0, 10)));
    $untilTxt = date('d.m.Y', mk_reporter_until($md) - 1) . ' 24:00';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#960000">
<title>MaTo Kicker – Reporter</title>
<link rel="icon" href="icon-192.png">
<style>
<?php mk_css(); ?>
  .rp-info{font-size:.85rem;color:var(--gray);margin:-4px 0 12px}
  .rp-badge{background:rgba(255,255,255,.18);border-radius:12px;padding:3px 10px;font-size:.78rem;font-weight:600}
</style>
</head>
<body>
<?php if (!$md): ?>
<div class="login-wrap">
  <div class="login-box">
    <img src="icon-192.png" alt="MaTo Kicker">
    <h1>MaTo Kicker Reporter</h1>
    <p>Dieser Reporter-Link ist nicht (mehr) gültig.<br>Die Freigabe wurde beendet oder ist abgelaufen – bitte frag nach einem neuen Link.</p>
    <a href="index.php" class="btn btn-red" style="display:inline-block;text-decoration:none">Zur Live-Ansicht</a>
  </div>
</div>
<?php else: ?>
<header>
  <div class="hdr-l">
    <a href="index.php?md=<?= rp_h($md['id']) ?>" title="Zur Live-Ansicht"><img class="hdr-logo" src="icon-192.png" alt="Logo"></a>
    <h1>MaTo Kicker <span style="font-weight:300;font-size:.8rem">Reporter</span></h1>
  </div>
  <span class="rp-badge">📣 Freigabe bis <?= rp_h($untilTxt) ?></span>
</header>

<div class="content">
<div class="card">
  <h2><?= rp_h($md['event'] ?? 'Spieltag') ?></h2>
  <p class="rp-info"><?= rp_h($dateTxt) ?> · Änderungen erscheinen sofort in der Live-Ansicht.</p>
  <div class="match-cards" id="matchCards"></div>
  <button id="addMatchBtn" class="btn btn-blue btn-save" onclick="addMatchRow()" style="display:none">+ Begegnung hinzufügen</button>
</div>
</div>

<div class="toast" id="toast">✓ Gespeichert!</div>

<script>
window.RP_TOKEN=<?= json_encode($token) ?>;
window.RP_FIXED_MD=<?= json_encode((string)$md['id']) ?>;
<?php mk_shared_js(); ?>
<?php mk_reporting_js(); ?>
</script>
<?php endif; ?>
</body>
</html>
