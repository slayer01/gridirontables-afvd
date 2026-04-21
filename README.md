# AFVD Data

WordPress-Plugin zur Anzeige von Spielplänen und Tabellen aus der AFVD-Datenbank (American Football Verband Deutschland).

## Installation

1. Repository klonen oder als ZIP herunterladen
2. Ordner nach `wp-content/plugins/afvd-data/` kopieren
3. Im WordPress-Admin unter **Plugins** aktivieren

## Einrichtung

### 1. Grundeinstellungen

**Einstellungen → AFVD Data → Settings**

- **Team Name** — Dein Teamname wie er in den AFVD-Daten steht (z.B. „Wetterau Bulls"). Wird in Tabellen fett hervorgehoben.
- **API Base URL** — Standard: `http://vereine.football-verband.de/` — normalerweise nicht ändern.
- **Auto Sync** — Automatischer Datenabgleich per WP-Cron (stündlich, 2x täglich, täglich oder manuell).

### 2. Ligen konfigurieren

**Einstellungen → AFVD Data → Leagues**

Für jede Liga eine Zeile anlegen:

| Feld | Beispiel | Beschreibung |
|------|----------|--------------|
| Slug | `mensteam` | Wird im Shortcode verwendet |
| Label | `Herren` | Anzeigename im Admin |
| Liga Code | `olm` | AFVD-Ligakürzel |
| Groups | `A,B` | Gruppen (kommagetrennt, leer lassen wenn keine) |
| Active | ✓ | Nur aktive Ligen werden importiert |

### 3. Daten importieren

**Einstellungen → AFVD Data → Import**

- **Import** pro Liga oder **Import All Active Leagues** für alle auf einmal.

## Shortcodes

```
[afvd_standings league="mensteam"]
[afvd_schedule league="mensteam"]
```

### Optionale Attribute

| Attribut | Werte | Beschreibung |
|----------|-------|--------------|
| `league` | Slug oder Liga-Code | **Pflicht** |
| `group` | `A`, `B`, ... | Nur eine bestimmte Gruppe anzeigen |
| `home_only` | `1` | Nur Heimspiele (Schedule) |
| `show` | `all`, `upcoming`, `past` | Zeitfilter (Schedule) |
| `limit` | Zahl | Max. Anzahl Spiele (Schedule) |
| `highlight` | Teamname | Überschreibt den globalen Teamnamen |

### Beispiele

```
[afvd_standings league="u16" group="A"]
[afvd_schedule league="mensteam" home_only="1"]
[afvd_schedule league="mensteam" show="upcoming" limit="5"]
```

## CSS anpassen

Die Tabellen verwenden die Klasse `.afvd-league-table`. Beispiel für eigene Farben im Theme-CSS:

```css
table.afvd-league-table th {
    background-color: #dd3333;
    color: #fff;
}
```

## Lizenz

GPL v2
