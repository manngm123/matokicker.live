# MaTo Kicker – Fußball-Spieltag-App

## Struktur1

```
mato/
├── api.php          ← JSON API (alle Datenzugriffe)
├── backend.php      ← Admin-Backend
├── frontend.php     ← Live-Ansicht (öffentlich)
└── data/            ← JSON-Datenhaltung (automatisch angelegt)
    ├── teams.json
    ├── players.json
    ├── matchdays.json
    └── results.json
```

## Installation

1. Alle Dateien in ein Verzeichnis auf dem PHP-Webserver kopieren
2. Das `data/`-Verzeichnis muss vom Webserver beschreibbar sein:
   ```bash
   mkdir data && chmod 755 data
   ```
3. Backend aufrufen: `http://deinserver/mato/backend.php`
4. Frontend: `http://deinserver/mato/frontend.php`

## Ablauf

1. **Tab 1 – Mannschaften**: Heimmannschaft (MaTo Kicker) und Gastmannschaften anlegen
2. **Tab 2 – Heimmannschaft**: Spielernamen der Heimmannschaft pflegen
3. **Tab 3 – Spieltag**: Datum, Eventname, Gastmannschaften zuordnen
4. **Tab 4 – Reporting**: Spieltag auswählen, Begegnungen anlegen, Tore eintragen

## Frontend

Das Frontend aktualisiert sich alle **10 Sekunden** automatisch, sobald ein Spieltag ausgewählt ist.
Als Android WebApp: einfach die URL im Chrome-Browser öffnen → Menü → "Zum Startbildschirm hinzufügen".
