<?php
/**
 * MaTo Kicker – gemeinsame Anmelde- und Freigabe-Logik
 * Genutzt von backend.php (Login), api.php (Rechteprüfung) und reporter.php (Freigabe-Link).
 */
if (!defined('MK_PASSWORD')) define('MK_PASSWORD', 'hier_passwort');   // Backend-Passwort
if (!defined('MK_DATA_DIR')) define('MK_DATA_DIR', __DIR__ . '/data/');
date_default_timezone_set('Europe/Berlin');

function mk_https() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}
function mk_cookie_path() {
    $p = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $p . '/';
}
// Schlüssel für das Admin-Cookie; ändert sich mit dem Passwort → alte Cookies werden ungültig
function mk_secret() { return hash('sha256', 'mk-admin|' . MK_PASSWORD . '|' . __DIR__); }

/**
 * Admin-Cookie (30 Tage, signiert). Unabhängig von der PHP-Session, damit ein langes
 * Spiel oder ein gesperrtes Handy in der Halbzeit nicht zum Abmelden führt.
 */
function mk_admin_cookie_set() {
    $exp = time() + 30 * 86400;
    $val = $exp . '.' . hash_hmac('sha256', (string)$exp, mk_secret());
    setcookie('mk_admin', $val, ['expires' => $exp, 'path' => mk_cookie_path(),
        'httponly' => true, 'samesite' => 'Lax', 'secure' => mk_https()]);
    $_COOKIE['mk_admin'] = $val;
}
function mk_admin_cookie_clear() {
    setcookie('mk_admin', '', ['expires' => time() - 3600, 'path' => mk_cookie_path(),
        'httponly' => true, 'samesite' => 'Lax', 'secure' => mk_https()]);
    unset($_COOKIE['mk_admin']);
}
function mk_is_admin() {
    if (!empty($_SESSION['auth'])) return true;
    $c = (string)($_COOKIE['mk_admin'] ?? '');
    if (!preg_match('/^(\d{9,11})\.([a-f0-9]{64})$/', $c, $m)) return false;
    if ((int)$m[1] < time()) return false;
    return hash_equals(hash_hmac('sha256', $m[1], mk_secret()), $m[2]);
}

// ── Reporter-Freigabe je Spieltag ─────────────────────────────────────────
// Gültig bis Mitternacht nach dem Spieltag (Europe/Berlin)
function mk_reporter_until($md) {
    $d = substr(str_replace('/', '-', (string)($md['date'] ?? '')), 0, 10);
    $ts = strtotime($d . ' 00:00:00');
    return $ts ? $ts + 86400 : 0;
}
function mk_reporter_active($md) {
    $tok = $md['reporter']['token'] ?? '';
    return $tok !== '' && time() < mk_reporter_until($md);
}
/** Liefert den Spieltag zu einem gültigen Reporter-Schlüssel, sonst null. */
function mk_reporter_md($token) {
    $token = (string)$token;
    if (!preg_match('/^[a-f0-9]{32}$/', $token)) return null;
    $path = MK_DATA_DIR . 'matchdays.json';
    if (!is_file($path)) return null;
    $mds = json_decode((string)file_get_contents($path), true);
    if (!is_array($mds)) return null;
    foreach ($mds as $md) {
        $t = (string)($md['reporter']['token'] ?? '');
        if ($t !== '' && hash_equals($t, $token)) return mk_reporter_active($md) ? $md : null;
    }
    return null;
}
