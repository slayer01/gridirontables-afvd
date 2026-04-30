# DSFOOBOO Football Data – League Tables & Schedules

WordPress-Plugin zur Anzeige von American Football Spielplänen und Tabellen. Nutzt den öffentlichen XML-Export von `vereine.football-verband.de`.

Dieses Plugin ist ein unabhängiges Projekt und steht in keiner Verbindung zum AFVD oder einem seiner Mitgliedsverbände.

## Installation

1. Repository klonen oder als ZIP herunterladen
2. Ordner nach `wp-content/plugins/` kopieren
3. Im WordPress-Admin unter **Plugins** aktivieren

## Einrichtung

### 1. Ligen konfigurieren

**DSFOOBOO Football Data → Leagues**

Für jede Liga eine Zeile anlegen:

| Feld | Beispiel | Beschreibung |
|------|----------|--------------|
| Slug | `herren` | Wird im Shortcode verwendet |
| Label | `Herren` | Anzeigename im Admin |
| Liga Code | `olm` | Liga-Kürzel aus der XML-API |
| Team Name | `Wetterau Bulls` | Teamname für Hervorhebung |
| Active | ✓ | Nur aktive Ligen werden importiert |

### 2. Daten importieren

**DSFOOBOO Football Data → Import**

- **Import** pro Liga oder **Import All Active Leagues** für alle auf einmal.
- Rohdaten können über die Buttons **Standings** / **Schedule** eingesehen werden.

### 3. Einstellungen

**DSFOOBOO Football Data → Settings**

- **API Base URL** — Standard: `http://vereine.football-verband.de/` — normalerweise nicht ändern.
- **Auto Sync** — Automatischer Datenabgleich per WP-Cron (stündlich, 2x täglich, täglich oder manuell).
- **Tabellenfarben** — Header-Hintergrund, Header-Text und Highlight-Farbe. Farben des aktiven Themes werden als Vorschläge angeboten.

## Shortcodes

```
[dsfooboo_football_data_standings league="herren"]
[dsfooboo_football_data_schedule league="herren"]
```

Die alten Shortcodes `[footballdata_standings]`, `[footballdata_schedule]`, `[afvdata_standings]` und `[afvdata_schedule]` funktionieren weiterhin als Aliase.

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
[dsfooboo_football_data_standings league="u16" group="A"]
[dsfooboo_football_data_schedule league="herren" home_only="1"]
[dsfooboo_football_data_schedule league="herren" show="upcoming" limit="5"]
```

## Migration aus älteren Versionen

Beim ersten Laden nach dem Update werden Datenbanktabellen und Optionen automatisch von den älteren Prefixes (`footballdata_*` oder `afvdata_*`) auf `dsfooboo_football_data_*` umgezogen. Bestehende Inhalte (Ligen-Konfiguration, Farben, importierte Daten) bleiben erhalten. Die alten Shortcodes funktionieren weiterhin als Aliase.

## CSS anpassen

Die Tabellen verwenden die Klasse `.dsfooboo_football_data_league_table`. Beispiel für eigene Farben im Theme-CSS:

```css
table.dsfooboo_football_data_league_table th {
    background-color: #dd3333;
    color: #fff;
}
```

## Lizenz

GPL v2 or later
