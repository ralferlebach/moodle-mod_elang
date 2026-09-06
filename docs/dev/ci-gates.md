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
| `phpunit` | 6 | Moodle 4.5 / 5.0 / 5.1 / 5.2 × PHP 8.1–8.4 × PostgreSQL und MariaDB |
| `behat` | 3 | Moodle 4.5, 5.1 und 5.2, Chrome über Selenium |
| `stale-files` | 1 | keine Datei aus `db/removed_files.txt` ist noch vorhanden |

`CI complete` fordert alle fünf.

## Blockierend — `main` (`.github/workflows/moodle-release.yml`)

| Job | Läufe | Was er prüft |
|---|---|---|
| `ci` | 6 | dieselbe Matrix, aber Lint, PHPUnit **und** Behat je Kombination in einem Job |
| `stale-files` | 1 | wie oben |

## Ausdrücklich **nicht** blockierend

| Prüfung | Warum |
|---|---|
| `phpunit-experimental`, `behat-experimental` (Moodle `main`) | Frühwarnung für die nächste Moodle-Version. Ein Bruch dort ist meist eine Änderung im Kern, kein Fehler hier; `continue-on-error` ist gesetzt. |
| `phpmd` | Meldet Stilhinweise, keine Fehler. |
| **Playwright** (`playwright.yml`) | Braucht eine installierte, geseedete Site. Läuft manuell und montags 03:00, nicht bei jedem Push. |
| **k6** (`load-k6.yml`) | Lastmessung, nur manuell. Ein Schwellwert im PR-Gate erzeugt auf geteilten Runnern Fehlalarme statt Erkenntnis. Szenarien und Schwellen: `docs/dev/load-testing.md`. |
| **Migration 1.3.5 → 2.0** (`migration-v1.yml`) | Installiert das Plugin von 2018, füllt es, aktualisiert auf diesen Stand und prüft Aktivität, Medium, Cues, Lücken, Einstellungen und Nutzerdaten (32 Prüfungen). Manuell und montags 04:00. Vor einer Freigabe **verpflichtend**. |

Zur Fehlerstrenge in diesem Lauf: Die **Version-1-Phase** (Installation und
Befüllung) läuft mit `error_reporting=8191`, also ohne Deprecation-Meldungen.
Version 1 ist Code von 2018 auf einem Moodle, für das er nie geschrieben wurde;
was PHP davon hält, ist nicht die Frage dieses Jobs — und der Code verschwindet
ohnehin, sobald der Migrationspfad entfällt (siehe `docs/dev/v1-legacy-exit.md`).

Unmittelbar **vor** dem Upgrade wird `debug` auf 32767 gesetzt. Ab dort ist jede
ausgeführte Zeile entweder Moodles oder unsere, und das Protokoll wird
ungefiltert auf `exception`, `fatal error` und `debugging` durchsucht. Global zu
dämpfen wäre die bequeme Variante gewesen und hätte genau das verborgen, wofür
der Job existiert.
| **JMeter** (`load-jmeter.yml`) | Zweite, unabhängige Messung derselben Lesestrecke. Nur manuell, braucht eine JVM. Vor einer Freigabe **verpflichtend**, aber nie Teil des Push-Gates. Siehe `docs/dev/load-testing.md`. |

### Was das für eine Freigabe bedeutet

Ein grüner CI-Lauf belegt Lint, Unit-, Integrations- und Browsertests über die
gesamte unterstützte Matrix. Er belegt **nicht**:

- dass die Barrierefreiheitsprüfungen liefen (**Playwright/Axe**),
- dass das Verhalten unter Last unverändert ist (**k6**),
- dass eine zweite, unabhängige Lastmessung dasselbe sagt (**JMeter**),
- dass Moodle `main` unterstützt wird.

Diese vier sind vor einer Stable-Freigabe **einzeln** anzustoßen und ihr
Ergebnis festzuhalten — mit demselben SHA, sonst vergleichen sie nichts.
Ein „CI grün" ersetzt keinen davon.

## Fresh Install, Upgrade, Backup/Restore

Alle drei sind Teil der blockierenden Läufe, aber nicht als eigene Jobs
sichtbar:

- **Fresh Install** führt `moodle-plugin-ci install` in jedem PHPUnit- und
  Behat-Job durch — 7 Läufe je Push, über beide Datenbanken.
- **Upgrade** wird von `tests/upgrade_test.php` abgedeckt, das eine V1-förmige
  Datenbank aufbaut und `xmldb_elang_upgrade()` darüber laufen lässt; dazu prüft
  `moodle-plugin-ci savepoints` die Savepoints im Lint-Job.

  Dieser Test definiert das V1-Schema allerdings **selbst** und kann damit nur
  bestätigen, womit er geschrieben wurde. Der Workflow `migration-v1.yml`
  schließt genau diese Lücke: dort stammt das Schema aus V1s eigener
  `db/install.xml`, installiert von Moodles eigenem Installer. Wenn beide
  auseinanderlaufen, zeigt es sich dort — und nur dort.
- **Backup/Restore** deckt `tests/backup/restore_test.php` ab.

Der geprüfte Pfad ist der einzige, den es gibt: **Version 1 → 2.0**. Keine
2.0-Beta wurde je veröffentlicht, es existieren also außerhalb von
Entwicklungsrechnern keine Installationen eines Zwischenstands, von denen aus
aktualisiert werden müsste. Alles andere ist eine Neuinstallation, und die
bekommt ihr Schema vollständig aus `db/install.xml` — was jeder der sieben
PHPUnit- und Behat-Läufe je Push mit `moodle-plugin-ci install` durchführt.

## Wie man den Nachweis führt

```bash
# Alle blockierenden Gates lokal, in derselben Form wie die CI:
bash mod/elang/tools/check_amd_builds.sh   # Grunt mit --max-lint-warnings=0
vendor/bin/phpunit -c mod/elang
vendor/bin/behat --config <behat.yml> --profile=chrome --tags=@mod_elang
```

`check_amd_builds.sh` aktualisiert vorher die Browserslist-Datenbank, und beide
CI-Workflows tun dasselbe im **Moodle**-Baum, in dem Grunt läuft — nicht im
Plugin-Verzeichnis. Ohne das weicht der Rollup-Build ab, und ein eingechecktes
Artefakt gilt als veraltet, obwohl sich nichts daran geändert hat.

Das ist zweimal passiert: in Inkrement 20 lokal, und danach auf `main`, weil
`moodle-release.yml` den Schritt gar nicht hatte, während `moodle-ci.yml` ihn
längst hatte. Zwei Workflows, die dasselbe Werkzeug aufrufen, brauchen dieselbe
Vorbereitung — sonst ist grün auf dem einen kein Hinweis auf den anderen.

**Rest-Risiko:** Erscheint zwischen dem lokalen Build und dem CI-Lauf eine neue
`caniuse-lite`-Version, können die Bytes erneut abweichen. Der Fix ist dann
immer derselbe: `tools/check_amd_builds.sh --sync=<Arbeitsbaum>` und die
Artefakte mit einchecken.
