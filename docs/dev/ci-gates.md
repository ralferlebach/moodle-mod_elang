# Welche Prüfungen die CI blockierend abdeckt

Der Code-Review führt unter P0: „sicherstellen/dokumentieren, welche Einzelgates
blockierender Bestandteil dieser grünen CI sind". Dieses Dokument beantwortet
das — und benennt ebenso deutlich, was **nicht** blockierend ist.

Maßgeblich ist der Job **`CI complete`**. Er ist der einzige, dessen Ergebnis
zählt: er verlangt ausdrücklich, dass jeder blockierende Job erfolgreich war,
und listet bei einem Fehlschlag auf, welcher es war. Ein grünes Häkchen an
einem einzelnen Job bedeutet für sich genommen nichts.

## Blockierend — Entwicklungszweige (`.github/workflows/moodle-ci.yml`)

| Job | Läufe | Was er prüft |
|---|---|---|
| `lint-php` | 2 (PHP 8.1, 8.4) | `phplint`, `phpcs --max-warnings 0` gegen den Moodle-Standard, `phpmd` (nicht blockierend) |
| `lint-js` | 1 | Grunt (ESLint, Stylelint, Rollup), `gherkinlint`, `phpdoc`, `mustache`, `validate`, `savepoints`, sowie `tsc`, Jest und die Reproduzierbarkeit des React-Bundles |
| `phpunit` | 5 | Moodle 4.5 / 5.0 / 5.2 × PHP 8.1–8.4 × PostgreSQL und MariaDB |
| `behat` | 2 | Moodle 4.5 und 5.2, Chrome über Selenium |
| `stale-files` | 1 | keine Datei aus `db/removed_files.txt` ist noch vorhanden |

`CI complete` fordert alle fünf.

## Blockierend — `main` (`.github/workflows/moodle-release.yml`)

| Job | Läufe | Was er prüft |
|---|---|---|
| `ci` | 5 | dieselbe Matrix, aber Lint, PHPUnit **und** Behat je Kombination in einem Job |
| `stale-files` | 1 | wie oben |

## Ausdrücklich **nicht** blockierend

| Prüfung | Warum |
|---|---|
| `phpunit-experimental`, `behat-experimental` (Moodle `main`) | Frühwarnung für die nächste Moodle-Version. Ein Bruch dort ist meist eine Änderung im Kern, kein Fehler hier; `continue-on-error` ist gesetzt. |
| `phpmd` | Meldet Stilhinweise, keine Fehler. |
| **Playwright** (`playwright.yml`) | Braucht eine installierte, geseedete Site. Läuft manuell und montags 03:00, nicht bei jedem Push. |
| **k6** (`load-k6.yml`) | Lastmessung, nur manuell. Ein Schwellwert im PR-Gate erzeugt auf geteilten Runnern Fehlalarme statt Erkenntnis. |
| **JMeter** | Kein Workflow. Misst dasselbe wie k6 und bräuchte zusätzlich eine JVM; `tests/load/*.jmx` bleibt liegen, wird aber nicht gepflegt. |

### Was das für eine Freigabe bedeutet

Ein grüner CI-Lauf belegt Lint, Unit-, Integrations- und Browsertests über die
gesamte unterstützte Matrix. Er belegt **nicht**:

- dass die Barrierefreiheitsprüfungen liefen (Playwright),
- dass das Verhalten unter Last unverändert ist (k6),
- dass Moodle `main` unterstützt wird.

Diese drei sind vor einer Stable-Freigabe **einzeln** anzustoßen und ihr
Ergebnis festzuhalten.

## Fresh Install, Upgrade, Backup/Restore

Alle drei sind Teil der blockierenden Läufe, aber nicht als eigene Jobs
sichtbar:

- **Fresh Install** führt `moodle-plugin-ci install` in jedem PHPUnit- und
  Behat-Job durch — 7 Läufe je Push, über beide Datenbanken.
- **Upgrade** wird von `tests/upgrade_test.php` abgedeckt, das eine echte
  V1-Datenbank aufbaut und `xmldb_elang_upgrade()` darüber laufen lässt; dazu
  prüft `moodle-plugin-ci savepoints` die Savepoints im Lint-Job.
- **Backup/Restore** deckt `tests/backup/restore_test.php` ab.

Eine Kombination bleibt ungeprüft: ein Upgrade auf einer Site, die eine
**frühere 2.0-Beta** installiert hat. Die Upgrade-Tests springen von V1 auf den
aktuellen Stand. Solange die Beta-Reihe läuft, ist das vertretbar; vor der
Stable-Freigabe gehört ein Upgrade-Pfad von der ersten öffentlichen Beta
geprüft.

## Wie man den Nachweis führt

```bash
# Alle blockierenden Gates lokal, in derselben Form wie die CI:
bash mod/elang/tools/check_amd_builds.sh   # Grunt mit --max-lint-warnings=0
vendor/bin/phpunit -c mod/elang
vendor/bin/behat --config <behat.yml> --profile=chrome --tags=@mod_elang
```

`check_amd_builds.sh` aktualisiert vorher die Browserslist-Datenbank. Ohne das
weicht der lokale Rollup-Build vom Build der CI ab, und die CI meldet ein
eingechecktes Artefakt als veraltet, obwohl lokal alles stimmte — siehe
`docs/sessions/session-008.md`, Inkrement 20.
