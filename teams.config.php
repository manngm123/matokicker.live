<?php
/**
 * MaTo Kicker – zentrale Team-Konfiguration (zum Saisonwechsel hier anpassen)
 *
 * Schlüssel (D1, D2, C1 …) = Kürzel in Klammern im Mannschaftsnamen (Backend, Reiter 3),
 * z. B. „MaTo Kicker (D1)“. Die Reihenfolge bestimmt die Anzeige; das ERSTE Team ist
 * die Vorbelegung beim Anlegen neuer Spieltage.
 *
 *   farbe   – Farbe in Übersicht, Kalender und Badges
 *   abo     – true = Termine dieses Teams landen im Kalender-Abo / ICS-Sammeldownload
 *   fd_name – Name des Teams in der fussball.de-Tabelle (für die Hervorhebung)
 *   tabelle – fussball.de-Tabellen-URL (leer = kein Tabellen-Button für dieses Team)
 */
return [
    'teams' => [
        'D1' => [
            'farbe'   => '#960000',
            'abo'     => true,
            'fd_name' => 'MaTo Kicker',
            'tabelle' => 'https://www.fussball.de/ajax.team.table/-/saison/2627/staffel/031D2BO37K00000AVS5489BUVUR5FS5A-G/team-id/02PQCUH8UK000000VS5489B2VUGLP840',
        ],
        'D2' => [
            'farbe'   => '#1565c0',
            'abo'     => false,
            'fd_name' => 'MaTo Kicker II',
            'tabelle' => 'https://www.fussball.de/ajax.team.table/-/saison/2627/staffel/031D2BO37K00000AVS5489BUVUR5FS5A-G/team-id/03160FT9B0000000VS5489BRVVV10ESU',
        ],
        'C1' => [
            'farbe'   => '#2e7d32',
            'abo'     => false,
            'fd_name' => 'MaTo Kicker',
            'tabelle' => 'https://www.fussball.de/ajax.team.table/-/saison/2627/staffel/031D2BO37K00000AVS5489BUVUR5FS5A-G/team-id/03160B1A04000000VS5489BRVVV10ESU',
        ],
    ],
    // Farbe für MaTo-Teams ohne Eintrag oben (z. B. das alte Team „MaTo Kicker“)
    'farbe_sonst' => '#6d4c41',
    // Spieltag-Typen: Schlüssel wird gespeichert (nicht ändern), Text ist die Anzeige
    'typen' => [
        'spieltag'     => 'Spieltag',
        'pokal'        => 'Pokalrunde',
        'turnier'      => 'Turnier',
        'freundschaft' => 'Freundschaftsspiel',
        'sonstiges'    => 'Sonstiges',
    ],
];
