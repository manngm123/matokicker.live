/**
 * MaTo Kicker – minimaler Service Worker.
 *
 * Zweck: macht die Seite als PWA installierbar (Android/Chrome benötigt dafür
 * einen registrierten Service Worker mit fetch-Handler).
 *
 * WICHTIG: Es werden bewusst KEINE Live-Daten zwischengespeichert – jede Anfrage
 * geht direkt ins Netz, damit Ergebnisse/Spielstände immer aktuell sind.
 * Nur bei fehlender Verbindung wird eine einfache Offline-Meldung angezeigt.
 */
const SW_VERSION = 'mato-v1';

self.addEventListener('install', (e) => {
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (e) => {
  e.respondWith(
    fetch(e.request).catch(() => {
      if (e.request.mode === 'navigate') {
        return new Response(
          '<!DOCTYPE html><meta charset="utf-8"><body style="font-family:sans-serif;text-align:center;padding:40px;color:#960000">' +
          '<h1>Offline</h1><p>Keine Internetverbindung. Bitte erneut versuchen.</p></body>',
          { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
      }
      return new Response('', { status: 503, statusText: 'Offline' });
    })
  );
});
