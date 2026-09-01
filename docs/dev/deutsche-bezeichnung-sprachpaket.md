# Die deutsche Aktivitätsbezeichnung auf einer laufenden Site ändern

## Das Problem

`lang/de/elang.php` im Plugin sagt seit Version 2.0.0-beta.2:

```php
$string['modulename'] = 'Video-Diktat';
$string['modulenameplural'] = 'Video-Diktate';
$string['pluginname'] = 'Video-Diktat';
```

Auf einer Site, die das deutsche Sprachpaket installiert hat, kommt davon
trotzdem nichts an. Dort steht in der Aktivitätsauswahl weiterhin die alte
Bezeichnung — bei `mod_elang` in aller Regel **„Hör-Garten"**, die AMOS-
Übersetzung des Vorgängers 1.x der Université de La Rochelle.

## Warum

`core_string_manager` lädt die Strings einer Komponente in dieser Reihenfolge,
wobei jede Stufe die vorherige überschreibt:

1. `mod/elang/lang/en/elang.php` — die englische Fassung im Plugin
2. `mod/elang/lang/de/elang.php` — die deutsche Fassung **im Plugin**
3. `$CFG->langotherroot/de/elang.php` — das **heruntergeladene Sprachpaket**
   (`moodledata/lang/de/`)
4. `$CFG->langlocalroot/de_local/elang.php` — die **lokale Sprachanpassung**
   (`moodledata/lang/de_local/`)

Das Sprachpaket steht damit **über** dem, was das Plugin selbst mitbringt.
Solange `moodledata/lang/de/elang.php` einen Wert für `modulename` enthält,
gewinnt dieser — unabhängig davon, was wir ausliefern.

Prüfen lässt sich das direkt:

```bash
grep -n "modulename\|pluginname" "$(php -r "define('CLI_SCRIPT',true); require('config.php'); echo \$CFG->langotherroot;")/de/elang.php"
```

Existiert die Datei nicht, greift die Plugin-Fassung und es ist nichts zu tun.

## Die Lösung: lokale Sprachanpassung

Sie ist die einzige Stufe, die über dem Sprachpaket liegt, und sie übersteht
jede Sprachpaket-Aktualisierung.

1. **Website-Administration → Sprache → Sprachanpassung**
2. Deutsch (`de`) auswählen, **„Sprachpaket zum Bearbeiten öffnen"**
3. Als Sprachdatei `elang.php` (Komponente `mod_elang`) wählen
4. Diese Strings anpassen und speichern:

   | String-ID | Wert |
   |---|---|
   | `modulename` | `Video-Diktat` |
   | `modulenameplural` | `Video-Diktate` |
   | `pluginname` | `Video-Diktat` |
   | `pluginadministration` | `Video-Diktat-Administration` |
   | `noinstances` | `In diesem Kurs gibt es keine Video-Diktate.` |
   | `elang:addinstance` | `Neues Video-Diktat anlegen` |
   | `elang:attempt` | `Video-Diktat bearbeiten` |
   | `elang:view` | `Video-Diktat ansehen` |

5. **„Sprachpaket speichern"** — Moodle schreibt nach
   `moodledata/lang/de_local/elang.php` und leert die String-Caches.

Vorhandene Aktivitäts**instanz**namen (etwa „Test elang") sind Datensätze in
`mdl_elang.name` und von alldem nicht betroffen.

## Alternative: den String aus dem Sprachpaket entfernen

Technisch möglich, aber nicht empfohlen: Wird `modulename` aus
`moodledata/lang/de/elang.php` gelöscht, greift wieder die Plugin-Fassung. Die
nächste automatische Sprachpaket-Aktualisierung stellt die Datei jedoch
vollständig wieder her, und die Änderung ist verloren.

## Der saubere langfristige Weg

Die Übersetzung gehört nach AMOS (`https://lang.moodle.org`), damit alle Sites
sie bekommen und keine lokale Anpassung mehr nötig ist. Bis dahin bleibt die
Sprachanpassung oben die verlässliche Lösung.
