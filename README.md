# AFVData – League Tables & Schedules

WordPress-Plugin zur Anzeige von American Football Spielplänen und Tabellen. Nutzt den öffentlichen XML-Export von `vereine.football-verband.de`.

Dieses Plugin ist ein unabhängiges Projekt und steht in keiner Verbindung zum AFVD oder einem seiner Mitgliedsverbände.

## Installation

1. Repository klonen oder als ZIP herunterladen
2. Ordner nach `wp-content/plugins/` kopieren
3. Im WordPress-Admin unter **Plugins** aktivieren

## Einrichtung

### 1. Ligen konfigurieren

**AFVData → Leagues**

Für jede Liga eine Zeile anlegen:

| Feld | Beispiel | Beschreibung |
|------|----------|--------------|
| Slug | `herren` | Wird im Shortcode verwendet |
| Label | `Herren` | Anzeigename im Admin |
| Liga Code | `olm` | Liga-Kürzel aus der XML-API |
| Team Name | `Wetterau Bulls` | Teamname für Hervorhebung |
| Active | ✓ | Nur aktive Ligen werden importiert |

### 2. Daten importieren

**AFVData → Import**

- **Import** pro Liga oder **Import All Active Leagues** für alle auf einmal.
- Rohdaten können über die Buttons **Standings** / **Schedule** eingesehen werden.

### 3. Einstellungen

**AFVData → Settings**

- **API Base URL** — Standard: `http://vereine.football-verband.de/` — normalerweise nicht ändern.
- **Auto Sync** — Automatischer Datenabgleich per WP-Cron (stündlich, 2x täglich, täglich oder manuell).
- **Tabellenfarben** — Header-Hintergrund, Header-Text und Highlight-Farbe. Farben des aktiven Themes werden als Vorschläge angeboten.

## Shortcodes

```
[afvdata_standings league="herren"]
[afvdata_schedule league="herren"]
```

### Optionale Attribute

| Attribut | Werte | Gilt für | Beschreibung |
|----------|-------|----------|--------------|
| `league` | Slug oder Liga-Code | Beide | **Pflicht** |
| `group` | `A`, `B`, ... | Beide | Nur eine bestimmte Gruppe anzeigen |
| `highlight` | Teamname | Beide | Überschreibt den konfigurierten Teamnamen |
| `class` | CSS-Klasse | Beide | Eigene CSS-Klasse für den Wrapper |
| `home_only` | `1` | Schedule | Nur Heimspiele des konfigurierten Teams |
| `show` | `all`, `upcoming`, `past` | Schedule | Zeitfilter |
| `limit` | Zahl | Schedule | Max. Anzahl Spiele |

### Beispiele

```
[afvdata_standings league="u16" group="A"]
[afvdata_schedule league="herren" home_only="1"]
[afvdata_schedule league="herren" show="upcoming" limit="5"]
```

## CSS anpassen

Die Tabellen verwenden die Klasse `.afvdata-league-table`. Beispiel für eigene Farben im Theme-CSS:

```css
table.afvdata-league-table th {
    background-color: #dd3333;
    color: #fff;
}
```

## Kontakt

- **Entwickler:** Daniel Schmidt-Richert
- **E-Mail:** afvdata@foo.boo
- **Source Code:** [github.com/slayer01/afvdata](https://github.com/slayer01/afvdata)

## Lizenz

GPL v2 or later
