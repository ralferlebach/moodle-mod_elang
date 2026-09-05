# Session 008 — UI-Verbesserungen (Issues #2 bis #9)

**Ausgangsstand:** `2.0.0-beta.1` (2026081303), alle P0/P1/P2 geschlossen,
CI von Ralf grün bestätigt.

**Ziel der Sitzung:** Abarbeitung der acht UI-Issues
[#2](https://github.com/ralferlebach/moodle-mod_elang/issues/2) bis
[#9](https://github.com/ralferlebach/moodle-mod_elang/issues/9).

---

## Inkrement 0 — Verifikationsumgebung (kein Version-Bump)

Der Sandbox-Container startete während des ersten Aufbaus neu; die Umgebung
wurde danach vollständig neu aufgesetzt.

```
Moodle    4.5.13+ (Build 20260818), MOODLE_405_STABLE
PHP       8.3.6 (pgsql, mbstring, curl, intl, zip, gd, soap, iconv)
          max_input_vars = 5000, Locale en_AU.UTF-8
DB        PostgreSQL 16.15
moodle-cs v3.7.0
```

### Baseline auf beta.1 (real gemessen)

| Prüfung | Ergebnis |
|---|---|
| PHPUnit | OK — 369 Tests, 1052 Assertions, 1 skipped |
| phpcs `--standard=moodle --severity=1` | 0 Errors / 0 Warnings |
| moodlecheck | 0 `<e>`-Tags |
| tsc `--noEmit` | sauber |
| Jest | 36/36 |
| esbuild-Bundle | byte-identisch reproduzierbar (`d80b6f53…`) |

### Klärung der Testzahl-Differenz zu Session 007

Session 007 protokollierte **394 Tests / 1268 Assertions**, hier zählte die
Suite **369 / 1052**. Gegengeprüft über ein JUnit-Log: 48 Testdateien auf der
Platte, 49 Suiten im Log, **keine Datei fehlt in der Discovery**. Die Differenz
stammt aus der Umgebung, nicht aus dem Code — auf Moodle 5.x zählt PHPUnit 11
Data-Provider-Zeilen anders und der `overview_test` läuft dort statt zu
skippen. 369/1052 ist die korrekte 4.5-Zahl und ab jetzt die Referenz.

**Lehre:** Eine Testzahl ohne Angabe des Moodle-Zweigs ist nicht vergleichbar.
Suite-Zahlen im Sessionprotokoll immer mit dem Zweig notieren, gegen den sie
gemessen wurden.

---

## Inkrement 1 — Issue #2: Aktivitätsnavigation und Benennung (beta.2)

**Version:** `2026090101`, Release `2.0.0-beta.2`.

### Der zentrale Fund: die Navigation hat nie funktioniert

`elang_extend_settings_navigation()` begann seit Session 004 mit:

```php
global $PAGE;
if (empty($PAGE->cm) || $PAGE->cm->modname !== 'elang') {
    return;
}
```

Diese Wächterzeile ist **immer** wahr. `moodle_page` liefert jede Eigenschaft
über `__get()` und definiert **kein `__isset()`**. `empty()` und `isset()`
fragen aber zuerst `__isset()` — fehlt das, gilt die Eigenschaft als nicht
gesetzt, unabhängig vom tatsächlichen Wert:

```php
class A {
    private $_cm = 'yes';
    public function __get($n) { $m = 'magic_get_' . $n; return $this->$m(); }
    protected function magic_get_cm() { return $this->_cm; }
}
$a = new A();
var_dump($a->cm);          // string(3) "yes"
var_dump(empty($a->cm));   // bool(true)   <-- immer
var_dump(isset($a->cm));   // bool(false)  <-- immer
```

Die Funktion stieg damit auf **jeder** Seite sofort aus. Die drei
Navigationseinträge, die Issue #2 im Ist-Zustand als „bereits vorhanden"
beschreibt, wurden nie gerendert; erreichbar waren die Bereiche ausschließlich
über die drei Buttons auf `view.php`. Das erklärt den Eindruck einer
„Sammlung technischer Einstiege" unmittelbar.

Gefunden wurde der Fehler nur, weil der neue PHPUnit-Test die Knoten wirklich
aus dem gebauten Navigationsbaum liest, statt die Funktion isoliert
aufzurufen.

**Zwei Lehren:**

1. **Auf `moodle_page` (und jeder Klasse mit `__get()` ohne `__isset()`) sind
   `empty()` und `isset()` bedeutungslos.** Wert in eine Variable lesen und
   diese prüfen: `$cm = $page->cm; if ($cm === null) …`.
2. **`global $PAGE` ist im Navigations-Callback die falsche Quelle.** Moodle
   baut den Baum für seine eigene `moodle_page`-Instanz. Richtig ist
   `$settingsnav->get_page()`, so wie es Moodles eigener `secondary`-View tut.

### Reiterreihenfolge — drei Kern-Fallstricke

Vor dem Bau wurde `lib/classes/navigation/views/secondary.php` gelesen:

1. **Plugin-Nodes landen ohne Zutun ganz hinten.**
   `load_module_navigation()` ordnet zuerst die Kern-Nodes nach
   `get_default_module_mapping()` (`modedit` = 1, Rollen, Filter, Logs, Backup,
   Restore) und hängt alles Plugin-Eigene danach als „leftover" an.
2. **Der dokumentierte Weg ist eine Subklasse.** `moodle_page` bevorzugt
   `mod_{modname}\navigation\views\secondary` gegenüber der Kernklasse — das
   Muster von `mod_quiz` und `mod_assign`. Keine CSS-/DOM-Manipulation nötig.
3. **Gebrochene Gewichte sind Verschachtelung, keine Feinsortierung.**
   `add_ordered_nodes()` behandelt jeden String-Key als Kind von
   `floor($key)`. Ein Gewicht `1.1` macht den Reiter zum Unterpunkt von
   Reiter 1. Nur ganze Zahlen ergeben Top-Level-Reiter. (Deshalb stehen
   `rolecheck` = 7.1 und `roleassign` = 7.2 im Kern unter `roleoverride` = 7.)

Dazu kam ein vierter Punkt: `MAX_DISPLAYED_NAV_NODES = 5` schiebt alles ab dem
sechsten Knoten in „Mehr". Die von Ralf bevorzugte Struktur belegt diese fünf
bereits vollständig, der Transkriptexport wäre also zwangsläufig unter „Mehr"
gelandet — er hat aber den Reiter favorisiert. Gelöst durch Überschreiben von
`force_nodes_into_more_menu()` in der Subklasse (Limit 6). Die
breitenabhängige Verschmälerung durch Boost bleibt unberührt.

Ergebnis: **Video-Diktat · Medien · Untertitel & Lücken · Berichte ·
Einstellungen · Transkriptexport**, alles Weitere unverändert unter „Mehr".

### Benennung: „Video-Diktat"

Nach Ralfs Entscheidung ersetzt **Video-Diktat** die bisherige deutsche
Bezeichnung in neun Strings (`modulename`, `modulenameplural`, `pluginname`,
`pluginadministration`, `noinstances`, `modulename_help`, `elang:addinstance`,
`elang:attempt`, `elang:view`). Englisch bleibt „Language exercise".

**Wichtig — das reicht auf einer laufenden Site nicht.** `core_string_manager`
lädt in der Reihenfolge Plugin-`en` → Plugin-`de` → **heruntergeladenes
Sprachpaket** (`moodledata/lang/de/`) → **lokale Anpassung**
(`moodledata/lang/de_local/`). Das Sprachpaket steht über dem Plugin. Solange
dort ein `modulename` steht (bei `mod_elang` die AMOS-Übersetzung des
Vorgängers 1.x, „Hör-Garten"), gewinnt dieser Wert. Der verlässliche Weg ist
die lokale Sprachanpassung; das ist die einzige Stufe über dem Sprachpaket und
sie übersteht Sprachpaket-Aktualisierungen. Anleitung:
`docs/dev/deutsche-bezeichnung-sprachpaket.md`.

### Transkript-Export für Lernende (Ralfs Entscheidung zu 2b)

Lernende halten `mod/elang:exporttranscript` per Archetype. Ohne weitere
Regelung hätte jede Aktivität ihr Arbeitsblatt an jeden Lernenden ausgegeben
und der Export-Reiter wäre für alle sichtbar gewesen — im Widerspruch zu
Issue #2. Statt die Capability zu ändern (Verhaltensänderung für Bestand)
entscheidet jetzt die Aktivität:

| Spalte | Typ | Default | Bedeutung |
|---|---|---|---|
| `allowtranscriptdownload` | INT(1) | `0` | Lernende dürfen das Arbeitsblatt (mit Lücken) laden |
| `solutionavailability` | CHAR(20) | `never` | `never` / `aftersubmission` / `always` |

Drei Helfer in `lib.php` sind die einzige Stelle, an der diese Entscheidung
fällt — `transcript.php` ruft sie **vor** jeder Ausgabe auf, UI-Sichtbarkeit
ist kein Sicherheitsmechanismus:

- `elang_can_export:worksheet()` — Capability `exportsolution` **oder** die Aktivitätseinstellung
- `elang_can_export:solution()` — Capability **oder** `always` **oder** `aftersubmission` mit **eigenem** abgeschlossenen Versuch
- `elang_can_export_transcript()` — eines von beiden, steuert die Reiter-Sichtbarkeit

`aftersubmission` prüft ausdrücklich die eigenen Versuche: ein abgeschlossener
Versuch einer anderen Person öffnet nichts, ein eigener laufender ebenso wenig.
Beides ist getestet.

### Weitere Änderungen

- `view.php`: die drei `single_button`-Aktionen entfernt; Lernende landen
  direkt auf dem Player bzw. dem fachlichen Leerzustand.
- `edit.php`: serverseitiger Medien-Guard. Ohne Medium im aktuellen Draft wird
  der React-Editor **nicht gemountet**; stattdessen ein Moodle-Hinweis mit
  Button „Zu den Medien". Direkter URL-Aufruf wird ebenso abgefangen.
- `set_secondary_active_tab()` auf allen fünf Seiten, damit der aktive Reiter
  und `aria-current` unabhängig vom URL-Vergleich korrekt sind.
- `mod_form.php`: Abschnitt „Transkript für Lernende" mit beiden Einstellungen
  und Whitelist-Validierung von `solutionavailability`.
- `backup_elang_stepslib.php`: beide neuen Felder in der Feldliste.
- `export:solutionhint` sagte „Lernende können dies nicht herunterladen" — das
  stimmt jetzt nicht mehr und wurde in beiden Sprachen korrigiert.

### Tests

- **`tests/navigation_test.php` (neu, 11 Tests):** Reiter je Rolle aus dem real
  gebauten Navigationsbaum gelesen (editingteacher, teacher, student,
  prohibited `viewreports`), plus die vollständige Zugriffsmatrix der drei
  Export-Helfer inklusive der `aftersubmission`-Semantik und der Prüfung, dass
  die Reitergewichte ganzzahlig sind.
- **`tests/upgrade_test.php`:** beide neuen Spalten in `V2_ELANG_FIELDS`
  aufgenommen. Der bestehende Test baut die V1-Datenbank real nach und lässt
  `xmldb_elang_upgrade()` laufen — der neue Savepoint ist damit gegen einen
  echten Upgrade-Pfad geprüft, nicht nur gegen `install.xml`.
- **Behat:** `edit_content.feature` vollständig neu (11 Szenarien) auf
  `I select "…" from secondary navigation` statt Buttons; `report.feature` und
  `authoring_studio.feature` nachgezogen. Neuer Step
  `elang "…" has a draft medium`, weil der Editor jetzt ein Medium verlangt.

### Verifikation (real gegen Moodle 4.5.13)

```
PHPUnit:     OK — 380 Tests, 1090 Assertions, 1 skipped   (Baseline 369/1052)
PHPCS:       0 Errors / 0 Warnings
moodlecheck: 0 <e>-Tags
Behat:       dry-run 26 Szenarien / 243 Steps, 0 undefined
Upgrade:     realer V1→V2-Lauf grün (34 Assertions)
```

`amd/src/*.js` wurde in diesem Inkrement nicht angefasst, daher kein
Grunt-Lauf nötig; die AMD-Builds bleiben unverändert.

---

## Inkrement 2 — Issues #3 und #4: Datenmodell, Formular und Payload (beta.2, 2026090102)

Beide Player-Issues teilen sich einen Abschnitt im Aktivitätsformular und
brauchen je eine Spalte. Sie kommen deshalb als **ein** Schema-Schritt mit
**einem** Savepoint; die Umsetzung im Player folgt in den nächsten
Inkrementen.

| Spalte | Typ | Default | Werte |
|---|---|---|---|
| `subtitleposition` | CHAR(20) | `below` | `below`, `overlaybottom`, `overlaytop` |
| `cuepausemode` | CHAR(20) | `auto` | `auto`, `stop`, `nostop` |

Beide Defaults entsprechen exakt dem Verhalten vor ihrer Existenz, ein
aktualisiertes Video-Diktat spielt also unverändert.

### `playback_settings` — gespeichert und wirksam getrennt halten

Nicht jedes Medium kann jede Einstellung erfüllen. Issue #3 fordert
ausdrücklich, dass die **gespeicherte** Einstellung dabei unverändert bleibt.
`\mod_elang\local\player\playback_settings` beantwortet deshalb getrennt, was
die Aktivität möchte und was der Player tatsächlich tun soll:

- **Audio** hat kein Bild, auf dem eine Einblendung liegen könnte →
  `overlay*` wird zu `below`. Der Pausemodus bleibt: ein Audio-Element meldet
  seine Zeit wie jedes andere.
- **Provider-Embed** (YouTube/Vimeo) meldet keine Wiedergabezeit und nimmt
  kein Pause-Kommando entgegen → `below` **und** `nostop`. Das ist Ralfs
  Entscheidung zu Rückfrage 4.6 (Option A); ein postMessage-Adapter bleibt
  einem eigenen Issue vorbehalten, weil er in dieser Sandbox nicht
  cross-origin verifizierbar ist.
- **Kein Medium** → dieselbe sichere Kombination.

`get_attempt_exercise` liefert beide Wertepaare (`subtitleposition` /
`cuepausemode` und `effective…`). Der Player rendert das wirksame Paar und
kann trotzdem erklären, warum keine Einblendung verwendet wurde — statt
Lehrende rätseln zu lassen, ob die Einstellung überhaupt gespeichert wurde.

Im Formular steht unter beiden Auswahlfeldern ein statischer Hinweis
(`playbackproviderhint`), der die Provider-Degradation benennt, bevor jemand
sie im Betrieb entdeckt.

### Verifikation

```
PHPUnit:     OK — 393 Tests, 1148 Assertions, 1 skipped
PHPCS:       0 Errors / 0 Warnings
moodlecheck: 0 <e>-Tags
Behat:       dry-run 28 Szenarien / 261 Steps, 0 undefined
```

Auch hier keine Änderung an `amd/src/*.js`, also kein Grunt-Lauf nötig.

---

## Inkrement 3 — Behat-Reparatur und CI-Ausbau (kein Version-Bump)

Reine Test- und Tooling-Arbeit, kein Laufzeitcode: nach der Bump-Regel kein
Version-Bump.

### Behat: der Fix aus Inkrement 1 war zu kurz gedacht

Ralfs CI meldete in **allen drei Branches** (4.5, 5.2, main) dieselben drei
Szenarien rot — nach der CI-Diagnoseregel sofort Verdacht auf echten Code, nicht
auf Umgebung. PHPUnit war überall grün.

Ursache: der Step `elang "…" has a draft medium` legte über
`get_or_create_draft()` **Draft 1** mit Medium an. Der Publish-Helper rief
danach `create_draft()` — und das branched *immer* frisch von der
veröffentlichten Version und **ignoriert einen offenen Draft**. Draft 2 wurde
befüllt und veröffentlicht, Draft 1 blieb verwaist liegen. `edit.php` ruft
`get_or_create_draft()` und bekam **Draft 1** zurück: ohne Medium und ohne
Cues. Der neue Medien-Guard schlug zu, „Transcript" fehlte.

Fix: `publish_elang_version()` verwendet `get_or_create_draft()`.

**Lehre:** Einen Behat-Step in den Background zu setzen, ohne zu prüfen, was die
nachfolgenden Steps mit dem erzeugten Zustand machen, erzeugt genau solche
Waisen. Bei `version_manager` gilt: `create_draft()` ist „neu von veröffentlicht
abzweigen", `get_or_create_draft()` ist „auf dem weiterarbeiten, was offen ist".

### Erstmals eine echte Browser-Verifikation in der Sandbox

Behat-`@javascript` lief bisher nur in Ralfs CI. Jetzt lokal möglich:

- Chromium 141 über Playwright beziehen — die Ubuntu-24.04-Pakete
  `chromium-browser` / `chromium-chromedriver` sind reine Snap-Weiterleitungen
  und im Container nicht benutzbar.
- Passenden ChromeDriver über die Chrome-for-Testing-JSON-Endpunkte auf die
  **gleiche Hauptversion** ziehen.
- `$CFG->behat_profiles` mit `wd_host` auf den lokalen ChromeDriver und
  `goog:chromeOptions.binary` auf das Playwright-Chromium.
- **`setsid`** für Webserver und ChromeDriver: ohne das sterben beide beim Ende
  des Shell-Aufrufs, und Behat meldet „http://localhost:8001 is not available".

Ergebnis: **28 Szenarien / 261 Steps, alle grün**, in 1m27s.

### CI: was tatsächlich kaputt war

**(a) Behat-Fehlerscreenshots wurden nie eingesammelt.** Beide Workflows luden
`moodle/behatfaildumps/` hoch — ein Pfad, den es nicht gibt. Im Log stand jedes
Mal nur `No files were found with the provided path`. `moodle-plugin-ci` schreibt
seine Fail-Dumps nach `$CFG->behat_faildump_path`, das es in
`MoodleConfig::BEHATDUMP` auf `<data dir>/behat_dump` setzt, also
`$GITHUB_WORKSPACE/moodledata/behat_dump`. Verifiziert durch Lesen der
installierten `moodle-plugin-ci`-Quellen, nicht geraten.

**(b) Der experimentelle Job scheiterte an PostgreSQL, nicht am Plugin.** Moodle
`main` (5.3) verlangt PostgreSQL 17; die Services stellten `postgres:16` bereit.
`moodle-plugin-ci` gibt nach jedem Install-Fehler seinen Usage-Text aus, was wie
ein ungültiges Argument aussieht — die eigentliche Meldung steht darüber:
`[System] version 17 is required and you are running 16.15`.

**Lehre:** Bei `moodle-plugin-ci install` ist die Usage-Ausgabe *nie* die
Fehlerursache. Immer die Zeilen darüber lesen.

**(c) Jest lief nirgends.** `package.json` hat `npm test` (36 Tests), aber kein
Workflow rief es auf. Ebenso `tsc --noEmit` und die Reproduzierbarkeit des
eingecheckten React-Bundles. Alle drei wurden bisher nur von Hand vor einem
Release geprüft.

### Was jetzt drin ist

| Bereich | Änderung |
|---|---|
| Diagnose | `ci-logs/` je Job; jeder Prüfschritt `set -o pipefail; … \| tee` |
| Vollständigkeit | `if: always()` auf jedem Prüfschritt — ein früher Fehler verdeckt die übrigen nicht mehr |
| Artefakte | Lint/PHPUnit/Behat **nur bei Fehlschlag**; Playwright und k6 **immer** |
| Behat-Dumps | korrigierter Pfad, plus `--dump` (Fehler-HTML direkt im Joblog) |
| Frontend | Node 22, `npm ci`, `tsc`, **Jest**, Bundle-Reproduzierbarkeitsgate |
| Experimentell | PostgreSQL 17 für die `main`-Jobs; die blockierenden bleiben auf 16 |
| Laufzeit | `concurrency` mit `cancel-in-progress`, Composer-Cache |
| Gate | `stale-files`-Job + `db/removed_files.txt`; Gate prüft jetzt alle blockierenden Jobs einzeln und benennt sie |
| Playwright | `.github/workflows/playwright.yml` (neu) |
| k6 | `.github/workflows/load-k6.yml` (neu), `workflow_dispatch` auf jedem Branch |

Die Assets für Playwright (`tests/playwright/`, inkl. axe) und k6
(`tests/load/elang-read-endpoints.k6.js`) lagen seit Session 006 bereit — es
fehlten nur die Workflows. Aus dem Plugintemplate übernommen und angepasst: die
vier Geschwister-Plugins und `SIBLING_REF` entfallen (mod_elang ist
eigenständig), das Layout-Gate prüft eine statt vier Plugin-Wurzeln, `seed.php`
exportiert `ELANG_*`, und beim k6-Plan werden `TOKEN` / `VERSIONID` / `P95`
durchgereicht — mit `::add-mask::` auf dem Token. Der `capacity-race`-Teil des
Templates entfällt, mod_elang hat keine Kapazitätsgrenze.

k6 schreibt zusätzlich `k6-run-context.txt` mit Ref, SHA, VUs, Dauer und
Runner-Ausstattung: eine Lastzahl ohne ihre Bedingungen ist nicht vergleichbar.

**JMeter** wurde bewusst *nicht* als Workflow aufgenommen. Es misst dasselbe wie
k6, braucht XML-Testpläne und eine JVM im Runner. `elang-read-endpoints.jmx`
bleibt liegen, wird aber nicht weitergepflegt.

### Nachträge aus dem ersten CI-Lauf

Der erste Lauf gegen die neuen Pipelines legte drei Fehler offen — zwei davon
in meinen eigenen Workflows. Die Instrumentierung hat dabei genau geleistet,
wofür sie gebaut wurde: die Artefakte enthielten die Ursache.

**(a) `moodle-release.yml` war syntaktisch ungültig.** Die Datei hatte bereits
einen `concurrency:`-Block; ich hatte einen zweiten eingefügt. GitHub lehnt
solche Workflows vollständig ab — sichtbar an „Total duration –", „Artifacts –"
und „This workflow graph cannot be shown". Das erklärt auch, warum sie auf
`development` lief, obwohl sie nur auf `main` triggert: ungültige Workflows
meldet GitHub bei jedem Push.

**Lehre:** PyYAML ist **kein** GitHub-Actions-Validator — es akzeptiert
doppelte Keys stillschweigend (der letzte gewinnt). Ab jetzt gilt
**`actionlint`** als Prüfwerkzeug für Workflows; es findet genau diese Klasse
von Fehlern:

```
moodle-release.yml:56:1: key "concurrency" is duplicated in workflow.
                        previously defined at line:42,col:1  [syntax-check]
```

**(b) Die vier Editor-Schritte verloren ihre Logs.** Sie laufen mit
`working-directory: plugin`; mein `tee ci-logs/…` war relativ und zeigte damit
auf `plugin/ci-logs/`, das es nicht gibt. `tee` scheiterte und riss den Schritt
mit — der Job starb an genau der Stelle, an der der Beweis dafür hätte landen
sollen. Alle vier Ziele sind jetzt auf `$GITHUB_WORKSPACE/ci-logs/` verankert.

**(c) Eine phpcs-Warnung aus meinem eigenen Behat-Kommentar**
(`moodle.Commenting.InlineComment.NotCapital`). Ich hatte nach der Änderung nur
`php -l` laufen lassen, nicht phpcs. Gegengeprüft: das lokale phpcs erkennt die
Regel — sie war einfach nicht mehr ausgeführt worden.

**(d) Selenium-Image-Pull scheiterte an Docker Hub.** In Moodle 4.5:

```
Unable to find image 'selenium/standalone-chrome:4' locally
docker: Error response from daemon: received unexpected HTTP status: 500
In BehatCommand.php line 183: Can't start Selenium server
```

Moodle 5.2 lief mit demselben Image durch — also Registry-Infrastruktur, kein
Code. `moodle-plugin-ci` startet Selenium mit `docker run`, das implizit zieht
und beim ersten Fehlschlag aufgibt; ein fremder 500er liest sich dann wie ein
Behat-Problem. Alle vier Behat-Jobs ziehen das Image jetzt in einem eigenen
Schritt vorher, mit fünf Versuchen und linear wachsender Wartezeit
(15/30/45/60s). Danach findet `docker run` es lokal und spricht die Registry
gar nicht mehr an. Der Tag ist über `MOODLE_BEHAT_SELENIUM_IMAGE` auf
Workflow-Ebene gepinnt, damit Vorab-Pull und `moodle-plugin-ci` nicht
auseinanderlaufen können. Scheitern alle fünf Versuche, sagt die Meldung
ausdrücklich, dass es Infrastruktur ist.

**(e) Der experimentelle Behat-Job scheiterte am MariaDB-Mindeststand.** Exakt
dieselbe Klasse wie (b), nur die andere Datenbank:

```
!! database mariadb (10.11.19-MariaDB-ubu2204) !!
[System] version 11.4.0 is required and you are running 10.11.19
```

Auf `mariadb:11.4` angehoben. Die blockierenden Jobs bleiben auf ihren
bewährten Ständen.

Der Folgefehler im selben Job — `Not enough arguments (missing: "plugin")` —
war **keine** eigenständige Ursache: `moodle-plugin-ci` nimmt das
Plugin-Verzeichnis aus der Umgebung, die der Install-Schritt schreibt. Ohne
gelungenen Install wird das Argument zwingend und der Behat-Schritt endet in
einem Usage-Fehler, der den eigentlichen Install-Fehlschlag verdeckt. Der
Schritt läuft jetzt nur noch bei `steps.install.outcome == 'success'` und
übergibt `./plugin` ausdrücklich.

**Lehre:** In einer Kette mit `continue-on-error` erzeugt jeder gescheiterte
Schritt Folgefehler in den nachfolgenden. Immer den **ersten** Fehler im Job
suchen, nicht den lautesten.

### Verifikation

```
actionlint (4 Workflows): exit 0, keine Befunde
phpcs --standard=moodle:  0 Errors / 0 Warnings über 137 Dateien
Behat (echter Browser):  28 Szenarien / 261 Steps  — alle grün
Jest:                    36/36
tsc --noEmit:            sauber
Bundle:                  byte-identisch reproduzierbar (d80b6f53…)
Workflows:               alle vier YAML-validiert
```

---

## Inkrement 4 — Issue #9: Transkriptexport als Exportoberfläche (2026090103)

Ralf hat sieben Mockups geliefert. Für #9 war der Entwurf vollständig, deshalb
zuerst umgesetzt.

**Vorher:** eine Überschrift je Produkt und darunter `PDF · DOCX · ODT · TXT`
als vier gleichrangige Links. Die Seite las sich als Formatliste, nicht als
zwei Dinge, die man mitnehmen kann.

**Jetzt:** zwei Karten, jede mit Titel, Beschreibung, PDF als primärer
Schaltfläche und den übrigen Formaten in einem Dropdown. Markup in
`templates/transcript_page.mustache`, Aufbereitung in
`mod_elang\output\transcript_page` — `transcript.php` ruft nur noch beides auf.
Reine Bootstrap-Klassen, damit die Seite dem Theme folgt.

**Eine bewusste Abweichung vom Mockup.** Dort trägt die Lösungskarte das feste
Abzeichen „Nur für Lehrende · Nicht sichtbar für Lernende". Seit Inkrement 1
stimmt das nur noch in einem von drei Fällen: `solutionavailability` kann
`never`, `aftersubmission` oder `always` sein. Das Abzeichen leitet sich jetzt
aus der Einstellung ab und sagt für jeden Fall die Wahrheit. Ein statischer
Text hätte Lehrende über ihre eigene Konfiguration getäuscht.

Der Reiter heißt außerdem nur noch **„Export"** statt „Transkriptexport", wie
im Mockup; der volle Name steht als Seitenüberschrift.

### Verifikation

```
PHPUnit:     OK — 393 Tests, 1148 Assertions, 1 skipped
PHPCS:       0 Errors / 0 Warnings (138 Dateien)
moodlecheck: 0 <e>-Tags
Mustache:    3 Templates, 0 Fehler
Behat:       28 Szenarien / 262 Steps, alle grün (echter Browser)
```

Ein Behat-Befund am Rande: `I should not see "Export" in the
".secondary-navigation" "css_element"` schlug fehl, weil Moodle einem
Lernenden **ohne eigenen Modus gar keine** Sekundärnavigation rendert — der
Locator fand das Element nicht und der Test scheiterte aus dem falschen Grund.
Als fehlender Link formuliert prüft er jetzt das, was gemeint war.

### CI-Nachtrag: der experimentelle Behat-Job

`mariadb:11.4` startete sauber („ready for connections"), der Runner meldete
trotzdem `Failed to initialize container`. Ursache ist die Health-Probe:
MariaDB 11 hat die `mysql*`-Befehlsaliase entfernt, `mysqladmin ping` existiert
dort nicht mehr. Die 10.11-Services der blockierenden Jobs sind davon nicht
betroffen — deshalb traf es nur diesen einen Job. Jetzt wird MariaDBs eigenes
`healthcheck.sh --connect --innodb_initialized` verwendet, das im Image
mitgeliefert wird und zusätzlich auf InnoDB wartet.

**Lehre:** Eine Datenbank-Hauptversion anzuheben heißt nicht nur, die Bildmarke
zu ändern — die Health-Probe gehört mitgeprüft. „Container startet, wird aber
nie healthy" sieht wie ein Infrastrukturfehler aus und ist eine
Konfigurationsfrage.

---

## Inkrement 5 — Issue #5: Medienverwaltung als eigener Reiter (2026090104)

Mockup 5 zeigt zwei Spalten: links die Auswahl, rechts das aktuell eingestellte
Medium mit Vorschau, Dateiname, Typ, Dauer und Größe. Genau so gebaut.

**Warum die rechte Spalte mehr ist als Zierde:** Der Reiter existiert, weil der
Untertiteleditor ohne Medium nicht öffnet — er ist die erste Station des
Autorenablaufs. Ein Medium zu *ersetzen*, während schon Cues daran hängen, ist
dagegen eine Entscheidung mit Folgen. Die vorher/nachher-Ansicht macht sie
sichtbar. Der Hinweis zu erhaltenen Untertiteln erscheint nur, wenn der Draft
tatsächlich Cues hat: einer Übung ohne Cues kann nichts kaputtgehen, und eine
Warnung ohne Gegenstand stumpft ab.

**Abweichung von Ralfs Antwort 4.11 — nach seinem eigenen Mockup.** Er hatte
gesagt, URL/Provider solle je nach Selektor ein-/ausgeblendet werden. Mockup 5
zeigt stattdessen einen dauerhaft sichtbaren, klar untergeordneten Abschnitt
„Andere Quelle (optional)". Das ist besser: kein Moduswechsel, die Hierarchie
trägt die Aussage. Umgesetzt als eingeklappter `header` unter dem Filepicker.

**Ein Feld statt zwei.** Der bisherige Weg über die WS-API verlangt
`provider` **und** `providerref` getrennt. Auf der Seite gibt es nur „Adresse
der Quelle": Lehrende fügen ein, was in ihrer Adresszeile steht.
`provider_registry::detect()` beantwortet, welcher Anbieter das ist — die
Klasse kennt die URL-Formen ohnehin schon.

Ein Detail, das ohne Test durchgerutscht wäre: eine **nackte Video-ID**
(`dQw4w9WgXcQ`) normalisiert unter *jedem* Anbieter. Die Erkennung darf sie
deshalb nicht dem erstbesten zuordnen; sie verlangt, dass die Referenz den
Anbieter auch benennt — inklusive der Kurzdomain `youtu.be`, die die
Zeichenfolge „youtube" nicht enthält.

**Kein `create_module()`-Fehler mehr im Blindflug:** `get_or_create_draft()`
liefert bei einem frisch angelegten Draft ein Objekt, auf dem die nullbaren
Medienspalten nicht gesetzt sind. Direkter Zugriff warnt dort. Behat hat das
sofort aufgedeckt — mit `Undefined property: stdClass::$mediakind` und einem
roten Szenario, nicht mit einer stillen leeren Seite.

Nach dem Speichern führt die Seite jetzt **auf sich selbst** zurück statt in
den Editor: das Ergebnis steht rechts und kann bestätigt werden, bevor es
weitergeht.

### Verifikation

```
PHPUnit:     OK — 403 Tests, 1168 Assertions, 1 skipped   (vorher 393/1148)
PHPCS:       0 Errors / 0 Warnings (139 Dateien)
moodlecheck: 0 <e>-Tags
Mustache:    4 Templates, 0 Fehler
Behat:       32 Szenarien / 309 Steps, alle grün (echter Browser)
```

---

## Inkrement 6 — Playwright lauffähig machen (kein Version-Bump)

Der erste geplante Playwright-Lauf scheiterte. k6 lief im selben Anlauf grün
durch und lieferte ein brauchbares Ergebnis:

```
25 VUs, 90 s, 4-CPU-Runner
5259 Requests, 45,7 req/s
p95 414 → 574 ms, max 1283 ms   (Schwelle 1500 ms)
```

Der Kontext-Anhang (`k6-run-context.txt`) tut genau das, wofür er gedacht war:
die Zahlen sind ohne „4 CPUs, 25 VUs, 90 s" nicht mit dem nächsten Lauf
vergleichbar.

### Drei Fehler, einer sichtbar, zwei dahinter

**(a) `npm ci` ohne Lockfile.**

```
npm error The `npm ci` command can only install with an existing package-lock.json
```

Ursache steht in unserer eigenen `.gitignore`:
`/tests/playwright/package-lock.json`. Aus Session 006, als die Browsertests ein
rein lokales Werkzeug waren. Jetzt läuft CI damit, also gehört der Lockfile
eingecheckt — er pinnt zusätzlich `@playwright/test` und `@axe-core/playwright`,
damit ein Release des Testwerkzeugs nicht stillschweigend ändert, was der
geplante Lauf prüft.

**(b) Der Fixture hätte danach sofort wieder gerissen.** `seed.php`
veröffentlicht eine Version **ohne Medium**. Seit Inkrement 1 verweigert
`edit.php` genau das — beide Editor-Tests wären auf dem Hinweis „Legen Sie
zuerst im Reiter Medien die Datei an" gelandet. Der Seed setzt jetzt vor dem
Veröffentlichen ein URL-Medium.

**Lehre:** Eine neue serverseitige Vorbedingung betrifft *jeden* Fixture, nicht
nur den, an dem sie auffällt. Behat wurde in Inkrement 1 nachgezogen, der
Playwright-Seed nicht — weil er damals nirgends lief.

**(c) Der Erfolgsartefakt wäre leer gewesen.** `playwright.config.ts` deklarierte
nur den `list`-Reporter. Der Workflow lädt bei Erfolg `playwright-report/` hoch —
ein Verzeichnis, das ohne HTML-Reporter nie entsteht. Ebenso waren Video und
Trace aus, obwohl der Workflow bei Fehlschlag ausdrücklich `.webm` löscht und bei
Erfolg die Videos behält. Jetzt: `list` + `html`, `video: 'on'`,
`trace: 'retain-on-failure'`.

Das war Ralfs Vorgabe „bei Playwright immer hochladen" — die Konfiguration hat
sie nicht erfüllt, und aufgefallen wäre es erst beim ersten grünen Lauf mit
leerem Artefakt.

### Verifikation

```
npx playwright test --list:  5 Tests in 2 Dateien, Konfiguration gültig
npm ci (mit Lockfile):       erfolgreich
php -l seed.php:             sauber
PHPCS:                       0 Errors / 0 Warnings
```

Die Tests selbst kann ich hier nicht ausführen — dafür braucht es eine
installierte Moodle-Site mit Seed. Das entscheidet der nächste CI-Lauf.

---

## Inkrement 7 — Issue #6: Untertitelimport im Modal (2026090105)

Vorher: ein `<details>`-Block mit Textarea, eingeklemmt zwischen Timeline und
Cue-Liste. Nur Text, keine Datei, kein Vorher-Wissen — wer „Importieren"
drückte, erfuhr erst danach, wie viele Cues jetzt in seiner Arbeit stehen.

Jetzt ein Modal nach Mockup 6, mit drei Entscheidungen dahinter:

**Ein Parserpfad für beide Reiter.** Datei und eingefügter Text sind nach dem
Einlesen dasselbe: WebVTT- oder SubRip-Inhalt. Beide Reiter füttern deshalb
**einen** String in **einen** serverseitigen Parse. Das ist die Bedingung
dafür, dass eine Datei und ihr Inhalt als Text nie unterschiedlich verstanden
werden. Die Datei wird per `FileReader` clientseitig gelesen — kein Upload,
kein zweiter Endpunkt.

**Import ist zweistufig.** „Inhalt prüfen" ruft `preview_import` und zeigt
Quelle, Format, erkannte Cues, erkannte Lücken und Dauer; erst danach sind
„Anhängen" und „Alle Cues ersetzen" überhaupt aktiv. Genau das macht die Wahl
zwischen beiden zu einer informierten. Angewandt wird das **bereits geparste**
Ergebnis, nicht ein zweiter Parse — sonst könnten Vorschau und Ergebnis
auseinanderlaufen.

**Das Format kommt aus dem Inhalt, nicht aus der Endung.** Eine Dateiendung ist
eine Behauptung, eingefügter Text hat gar keine. WebVTT muss mit seiner
Signatur beginnen; alles andere, was der Parser annimmt, ist SubRip.

„Alle Cues ersetzen" erscheint nur, wenn es Cues zu verlieren gibt. Auf einer
leeren Version täten beide Schaltflächen dasselbe, und die Wahl wäre Lärm.

Kein `core/modal`: der Editor ist ein React-Baum, der sein Rendering selbst
besitzt. Einen Teilbaum an Moodles Modal-API zu übergeben hieße, zwei
Lebenszyklen für einen Dialog zu führen. Fokusführung beim Öffnen, Escape und
Klick auf den Hintergrund sind stattdessen in der Komponente umgesetzt.

### Verifikation

```
tsc --noEmit:  sauber
Jest:          36/36 (Mount-Test auf den zweistufigen Ablauf umgeschrieben)
Grunt amd:     gelaufen, Artefakte zurückkopiert, zweiter Lauf byte-identisch
               editor.min.js  e99c3b54 → e42af04c
               player.min.js  ca65f884 → unverändert
esbuild:       d80b6f53 → 6507e4b6
PHPUnit:       403 Tests, 1168 Assertions, 1 skipped
PHPCS:         0 Errors / 0 Warnings
moodlecheck:   0 <e>-Tags
Behat:         32 Szenarien / 309 Steps, alle grün (echter Browser)
```

Der Jest-Mount-Test hat den Umbau sofort erwischt: er klickte auf
`[data-action="import"]`, das es nicht mehr gibt. Umgeschrieben prüft er jetzt
zusätzlich, dass „Importieren" **vor** der Prüfung deaktiviert ist und das
Modal nach dem Anwenden schließt.

---

## Inkrement 8 — Issues #3 und #4 im Player (2.0.0-beta.3, 2026090106)

Erst hier springt der Release-String: seit beta.2 wurde nur die Versionsnummer
hochgezählt, wie in Rückfrage 13 vereinbart. Ralf hat den Sprung auf **beta.3**
angefordert.

### Rücknahme: keine Nutzerpräferenz

Der in der vorigen Runde abgestimmte Präferenz-Layer ist auf Ralfs Korrektur
hin **entfallen** — Untertitelposition und Auto-Scroll bleiben reine
Aktivitätseinstellungen („Die visuelle Darstellung ist an dieser Stelle
ungenau"). Damit entfällt die Schemaänderung, der Privacy-Provider bleibt
unangetastet, und die Arbeit aus Inkrement 2 trägt unverändert. Der
Issue-Nachtrag, den ich dafür geschrieben hatte, ist hinfällig.

**Lehre:** Ein Mockup ist eine Absichtserklärung, keine Spezifikation. Dass
eine Einstellung im Player *dargestellt* wird, heißt nicht, dass sie dort
*geändert* werden soll. Rückfragen vor der Schemaänderung war richtig — die
Umsetzung wäre sonst gebaut und wieder ausgebaut worden.

### #3: eine Cue-Darstellung, drei Orte

Die zentrale Entscheidung: in den Overlay-Modi wird das **aktive Cue-Element
verschoben**, nicht neu gerendert. Es trägt bereits die Gap-Inputs mit ihren
wiederhergestellten Werten, ihren Listenern und ihrem Bewertungszustand. Eine
zweite Darstellung wären zwei Gap-Implementierungen, die sich darüber uneinig
werden können, was jemand getippt hat — genau das schließt Issue #3 unter
„Technische Hinweise" aus. Ein verstecktes Anker-Element hält den Platz in der
Liste, damit die Reihenfolge erhalten bleibt.

Beim Auto-Scroll gab es eine Falle: unser eigenes `scrollIntoView()` löst
ebenfalls ein `scroll`-Ereignis aus. Ohne Unterscheidung hätte der erste
automatische Scroll jeden weiteren unterdrückt. Ein Flag trennt beides;
manuelles Scrollen setzt eine Karenz von 4 s.

Zur Formulierung im Issue („erst wieder aufgenommen, wenn sich die Wiedergabe
zu einer neuen aktiven Zeile bewegt **oder** eine Inaktivitätszeit abgelaufen
ist"): Wörtlich genommen hebt der nächste Cue-Wechsel die Karenz sofort auf —
dann ist sie wirkungslos, denn `scrollIntoView()` läuft ohnehin nur bei
Cue-Wechseln. Umgesetzt ist deshalb: **die Karenz gilt, bis sie abgelaufen
ist**; ein Cue-Wechsel während der Karenz scrollt nicht. Das erfüllt die
Hauptforderung „nicht permanent bekämpft".

### #4: eine Frage, zwei Verhaltensweisen

Enter-Fluss und Pausemodus wirken getrennt, teilen aber eine Frage: **welcher
Cue wird gerade bearbeitet?** Beides liegt deshalb in einem Controller.

- `stop` hält an jeder Cue-Grenze, `nostop` nie, `auto` nur am Ende des Cues,
  der bearbeitet wird — angeklickt oder mit dem Tastaturfokus in einer seiner
  Lücken.
- Nach dem Anhalten wird exakt auf die Grenze zurückgesetzt. `timeupdate`
  feuert nur ein paar Mal pro Sekunde, die Wiedergabe steht also schon ein
  Stück dahinter; ohne Korrektur verschluckt das Fortsetzen das erste Wort des
  nächsten Cues.
- `play` löscht die Merkung „für diesen Cue schon angehalten", sonst käme man
  an einer Grenze nie vorbei.
- Enter ist an das Promise des Submits gebunden, nicht daneben ausgelöst: der
  Fokuswechsel triggert den Blur-Handler, und ohne Warten ginge dieselbe
  Antwort zweimal raus.
- Nach Ralfs Antwort 4.5 springt die Wiedergabe zur nächsten Lücke und läuft
  bis zum nächsten End-Marker — aber nur, wenn sie nicht ohnehin schon in
  diesem Cue steht; sonst würde eine zweite Lücke im selben Satz ihn
  zurückspulen.

### Zwei Fehler beim Bauen

**ESLint im Grunt-Lauf** fand drei fehlende JSDoc-Parameter — genau wofür der
Schritt da ist.

**Ein Behat-Lauf gegen einen veralteten Build.** Der Sync
`rm -rf moodle/mod/elang && cp -a work/elang` überschreibt die frisch gebauten
`amd/build/`-Artefakte mit der älteren Kopie aus dem Arbeitsbaum. Der erste
37/366-Lauf prüfte deshalb noch den Stand vor #4 — er war grün, aber er belegte
nicht, was er zu belegen schien.

**Lehre:** Nach `grunt amd` **zuerst** in den Arbeitsbaum zurückkopieren, dann
erst wieder synchronisieren. Ein grüner Lauf gegen den falschen Build ist
schlimmer als ein roter.

### Verifikation

```
Grunt amd:   ESLint sauber, zweiter Lauf byte-identisch
             player.min.js  ca65f884 → 7833b664
             editor.min.js  e42af04c unverändert
PHPUnit:     403 Tests, 1168 Assertions, 1 skipped
PHPCS:       0 Errors / 0 Warnings
moodlecheck: 0 <e>-Tags
Jest:        36/36
Behat:       37 Szenarien / 366 Steps, alle grün (echter Browser)
```

Die fünf neuen Behat-Szenarien belegen alle drei Positionen und die
Audio-Degradation gegen einen echten Browser.

---

## Inkrement 9 — Issue #8: Berichte als Auswertungsoberfläche (2026090107)

Der einzige Issue ohne Mockup. Umgesetzt entlang seiner drei Ebenen:
Kennzahlen, filter-/sortierbarer Überblick, Detailansicht.

### Eine Query-Wahrheitsquelle für alle drei Ansichten

`build_list_query()` liefert jetzt FROM und WHERE getrennt und wird von
`list_for_activity()`, `count_for_activity()`, `aggregate_for_activity()` **und**
`export_rows()` verwendet. Damit können Kopfzahlen, Tabelle, Seitenzähler und
Export nicht mehr verschiedene Mengen beschreiben. Der Export respektiert die
Filter: täte er das nicht, gäbe er mehr heraus, als die Lehrkraft gerade
ansieht — im Separate-Groups-Modus wäre das eine Offenlegung.

Der Durchschnitt zählt nur abgeschlossene Versuche. Ein laufender Versuch hat
einen Score, der schlicht noch nicht vergleichbar ist; ihn einzurechnen würde
die Zahl bewegen, wenn jemand *anfängt*, nicht wenn jemand *leistet*.

### Kein Request wählt SQL

Sortierspalten stehen in einer Konstante `SORT_COLUMNS`, Filter laufen durch
`clean_filters()`. Ein Test schickt `'a.id; DROP TABLE'` als Sortierschlüssel
und erwartet die Standardsortierung.

Zusätzlich verwirft `clean_filters()` einen umgekehrten Zeitraum, statt ihn
durchzureichen: eine leere Tabelle nach einem Tippfehler sieht aus wie eine
Aktivität, die niemand bearbeitet hat.

### Drei Fehler, die Behat gefunden hat

**(a) `date_selector` liefert ein Array.** `optional_param('filterfrom', 0,
PARAM_INT)` wirft darauf eine `coding_exception`. Gelöst durch getrennte
Namensräume: das Formular postet seine eigenen Feldnamen, die Seite liest
ausschließlich kanonische Skalare (`ffrom`, `fto`, …), und beim Absenden wird
auf die kanonische URL umgeleitet. Nebeneffekt: eine gefilterte Ansicht ist ein
Link, den man weitergeben kann — genau was das Issue verlangt.

**(b) Ein `redirect()` nach der Header-Ausgabe.** Die erste Fassung baute das
Formular im Ausgabezweig. Formularaufbau und Umleitung stehen jetzt vor
`$OUTPUT->header()`.

**(c) `moodleform` wird nicht autoloaded.** `report.php` fehlte
`require_once($CFG->libdir . '/formslib.php')` — acht Szenarien rot mit
„Class moodleform not found".

Und ein vierter, den phpcs fand: mein eigener Kommentar begann klein
(`moodle.Commenting.InlineComment.NotCapital`) — dieselbe Regel wie in
Inkrement 3. Der Reflex „nur `php -l`" hält sich hartnäckig.

### Verifikation

```
PHPUnit:     OK — 411 Tests, 1193 Assertions, 1 skipped   (vorher 403/1168)
PHPCS:       0 Errors / 0 Warnings (141 Dateien)
moodlecheck: 0 <e>-Tags
Mustache:    5 Templates, 0 Fehler
Behat:       42 Szenarien / 426 Steps, alle grün (echter Browser)
```

Acht neue PHPUnit-Tests decken Statusfilter, Personenfilter, verworfene Werte,
Sortierung in beide Richtungen, den Fallback bei unbekanntem Sortierschlüssel,
die Kennzahlen (gefiltert und leer) und den gefilterten Export ab. Sechs neue
Behat-Szenarien decken Kennzahlen, Filtern, Zurücksetzen, das Exportmenü und
die Verlagerung von „Löschen" ins Aktionsmenü ab.

---

## Inkrement 10 — Playwright real repariert (2.0.0-beta.4, 2026090108)

Zweimal hatte ich vorher „vermutlich behoben" geliefert. Diesmal habe ich in
der Sandbox eine **echte Moodle-Site** installiert
(`admin/cli/install_database.php`), den Seed laufen lassen und die Suite
tatsächlich ausgeführt — und den Fehler damit reproduziert statt erraten.

### Der Fehler: das Passwort wurde leer abgeschickt

Die Instrumentierung des POST-Bodys zeigte es in einer Zeile:

```
POST BODY: anchor=&logintoken=el5R…&username=elang_pw_1788267415&password=
```

Das Feld **war** gefüllt — `inputValue('#password')` gab den richtigen Wert
zurück. Beim Absenden war es leer. Ursache: Moodle initialisiert auf dem
Login-Feld nach dem Laden ein „Passwort anzeigen"-Bedienelement, und diese
Initialisierung setzt das Feld zurück. Der Helper tippte vorher.

Mit `await page.waitForLoadState('networkidle')` vor dem Ausfüllen:

```
POST BODY: …&username=elang_pw_1788267415&password=Elang-pw-1788267415%21
URL AFTER: http://localhost:8000/my/
```

### Warum es zweimal an mir vorbeigelaufen ist

Der Helper prüfte den Erfolg so:

```ts
await expect(page).toHaveURL(/\/(my|course)\b|\/index\.php/);
```

`\/index\.php` matcht **`/login/index.php?loginredirect=1`** — genau die
Seite, auf der ein *fehlgeschlagener* Login landet. Der Login galt als
erfolgreich, und die eigentliche Ursache trat drei Assertions später als
„Element nicht gefunden" auf einer Seite auf, die niemand erreicht hatte.

**Lehre:** Eine Erfolgsprüfung, die auch den Misserfolg akzeptiert, ist
schlimmer als keine — sie verschiebt den Fehler an eine Stelle, an der er
falsch aussieht. Positiv formulierte Muster („wir sind auf /my") sind hier
sicherer als lockere Alternativen.

### Zwei echte Barrierefreiheitsfehler

Nach dem Login-Fix meldete axe, was vorher nie zur Ausführung kam: die
Autoren-Timeline erfüllt WCAG AA nicht. Weiße Beschriftung auf `#4a90d9`
erreicht 3,3:1, auf `#d9534f` 4,0:1 — verlangt sind 4,5:1. Die `opacity: 0.6`
des inaktiven Cues verwässerte den Text zusätzlich gegen das, was dahinter
liegt, sodass der reale Kontrast nicht einmal vorhersagbar war.

Jetzt `#2b6cb0` (5,4:1) und `#9b2c2c` (7,5:1), ohne Opacity, und der aktive Cue
trägt zusätzlich eine Outline — die Unterscheidung hängt damit nicht mehr an
der Farbe allein.

Ein Detail, das mich fast erneut fehlleiten hätte: nach dem CSS-Fix blieb der
Test rot, bis `php admin/cli/purge_caches.php` lief. Moodle liefert
gecachtes CSS aus.

### Der Grunt-Fehler: ein Auslieferungsfehler von mir

```
File is stale and needs to be rebuilt: amd/build/editor.min.js
```

`patch-2.0.97` enthielt `amd/src/editor.js` mit der erweiterten String-Liste,
aber **nicht** den dazugehörigen Build. Ich hatte ihn lokal erzeugt
(`e99c3b54` → `e42af04c`) und dann nicht in die Dateiliste des Patches
aufgenommen.

**Lehre:** Nach `grunt amd` gehört das Build-Artefakt in **dieselbe**
Patch-Dateiliste wie die Quelle. Die Idempotenzprüfung, die ich durchgeführt
hatte, beweist nur, dass der Build korrekt ist — nicht, dass er ausgeliefert
wurde.

### Verifikation

```
Playwright:  5/5 grün, gegen eine echte Moodle-Site in dieser Sandbox
PHPUnit:     411 Tests, 1193 Assertions, 1 skipped
PHPCS:       0 Errors / 0 Warnings
moodlecheck: 0 <e>-Tags
stylelint:   0 Fehler
Grunt amd:   nicht mehr stale, byte-identisch
Behat:       42 Szenarien / 426 Steps, alle grün
```

---

## Inkrement 11 — Issue #7: Editor als synchronisierter Workspace (2.0.0-beta.5, 2026090109)

Der letzte der acht Issues und der größte. Kein Neubau: Timeline, Waveform,
`addGapFromSelection()`, `resyncGaps()`, `utf16ToCodepoint()`, `createAutosave()`
und der Publish-Weg bleiben unverändert. Was sich ändert, ist die Präsentation.

### Eine Auswahl, drei Ansichten

`EditorApp` bleibt Owner von `cues`; neu ist allein `selectedcuekey`. Der
geöffnete Cue wird **abgeleitet**, nicht gespeichert:

```ts
const selectedindex = cues.findIndex((cue) => cue.cuekey === selectedcuekey);
```

Eine Kopie des ausgewählten Cues im State wäre eine zweite Wahrheitsquelle, die
von der Liste abdriften kann — genau das, was der Issue unter „nicht in
parallele Wahrheitsquellen zerlegen" ausschließt. `seekToCue()` ist der einzige
Einstiegspunkt für „diesen Cue bearbeiten", egal ob der Klick aus der Liste oder
aus der Timeline kam; Listenmarkierung, Inspector und Medienposition bewegen
sich dadurch zwangsläufig gemeinsam.

### Zeiten, die man lesen kann

`studio/time.ts` ist bewusst ein eigenes Modul mit eigenen Tests. Zwei
Entscheidungen darin:

- **`parseTime()` gibt bei Unlesbarem `null` zurück, nicht `0`.** Null ist eine
  legitime Cue-Zeit; sie als Fehlerwert zu verwenden würde einen Tippfehler in
  einen Cue verwandeln, der stillschweigend an den Anfang springt.
- **„1:75" wird abgelehnt.** Sonst gäbe es zwei Schreibweisen für denselben
  Moment, und zwei Schreibweisen für einen Wert sind der Weg, auf dem aus einer
  Rundungsdifferenz ein Fehler wird.

`TimeField` hält beim Tippen einen eigenen Entwurf und übernimmt erst bei Blur
oder Enter. Bei jedem Tastendruck neu zu formatieren hieße, gegen die
schreibende Person zu arbeiten: „1:0" würde zu „01:00.000", bevor die zweite
Ziffer der Minute ankommt.

### Autosave nach vorn, Speichern nach hinten

Der dominante „Speichern"-Button stand neben einem laufenden Autosave. Das lehrt
Autor:innen, dem Autosave nicht zu trauen. Jetzt führt der Speicherzustand die
Toolbar an, „Speichern" ist ein Link, und „Cue hinzufügen" steht bei den Cues
statt neben „Veröffentlichen" — Cue anlegen und Veröffentlichen sind keine
gleichrangigen Schritte.

### Zwei Befunde aus den Tests

**Behat fand eine echte Lücke:** „Cue hinzufügen" hängte einen Cue an, wählte ihn
aber nicht aus — der Inspector blieb leer, die Aktion wirkte folgenlos.
`handleAddCue()` delegiert jetzt an `insertCueAt(cues.length)`; eine
Implementierung statt zweier, die auseinanderlaufen können.

**jsdom kennt `scrollIntoView` nicht.** Statt die Komponente mit einer
`typeof`-Prüfung zu belasten — die ein Testanliegen in Produktionscode trägt und
außerdem einen echten Fehler verdecken würde — steht der Stub in
`js/tests/setup.ts`.

### Bewusst nicht gemacht

`MediaPanel.tsx` ist entfernt: Medienkonfiguration gehört seit beta.2 in den
Medienreiter, und der Issue schließt sie hier ausdrücklich aus. **Erfordert ein
explizites `git rm`.**

Offen aus #7: die Verlagerung von Varianten und Hinweisen in einen eigenen
`GapInspector` mit „Erweiterte Einstellungen", sowie der Playwright-Visual-Test
für lange Cue-Listen.

### Verifikation

```
tsc --noEmit: sauber
Jest:         47/47   (vorher 36/36; +12 Zeit, +3 Workspace, −4 MediaPanel)
esbuild:      6507e4b6 → de8a4b07, idempotent
Grunt amd:    e42af04c → 03af31c1, idempotent
stylelint:    0 Fehler
PHPUnit:      411 Tests, 1193 Assertions, 1 skipped
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
Behat:        42 Szenarien / 426 Steps, alle grün (echter Browser)
```

---

## Inkrement 12 — Zwei Lint-Befunde aus dem CI-Lauf (kein Version-Bump)

Reine Stilkorrektur an einem Kommentar und an Leerzeilen; nach der Bump-Regel
kein Version-Bump, und das Build-Artefakt ist unverändert.

```
player.js 703:17  warning  Comments should not begin with a lowercase character
report.feature 93          Multiple empty lines are not allowed
```

Der Kommentar begann mit `// timeupdate fires …` — der Ereignisname in
Kleinschreibung als erstes Wort. Umformuliert zu „The timeupdate event fires …".
Und `report.feature` endete mit einer Leerzeile zu viel; mein
Python-Anhängeskript hatte `\n` an einen bereits mit Zeilenumbruch endenden
String gehängt.

### Die eigentliche Lehre: `grunt amd` ist nicht `grunt`

Ich habe die ganze Sitzung über nur `grunt amd` ausgeführt. Das deckt
`eslint:amd` und `rollup:dist` ab — aber **nicht** `gherkinlint`, und es
scheitert nicht an Warnungen, weil `--max-lint-warnings 0` fehlte. Die CI führt
`moodle-plugin-ci grunt --max-lint-warnings 0` aus, also den vollständigen
Standardtask.

Ab jetzt gilt lokal der **vollständige** `grunt`-Lauf im Plugin-Verzeichnis als
Prüfung, nicht `grunt amd`. Verifiziert: Exit 0 über `gherkinlint`,
`eslint:amd`, `rollup:dist`, `eslint:yui`, `stylelint:css`.

Ein Nebenbefund, der beim Prüfen auffiel: der Hash von `player.min.js` bleibt
nach der Kommentaränderung `7833b664`. Rollup entfernt Kommentare, das
Minifikat ist also byte-identisch.

**Und genau daraus wurde der nächste Fehler.** Ich schloss aus dem
unveränderten `.min.js`, es gäbe nichts auszuliefern — und übersah die
Source Map:

```
File is stale and needs to be rebuilt: amd/build/player.min.js.map
```

Eine Source Map trägt in `sourcesContent` den **vollständigen Originalquelltext
samt Kommentaren**. Eine reine Kommentaränderung lässt das Minifikat
byte-identisch und ändert die Map. Zweimal in dieser Sitzung ist mir das
durchgegangen.

Deshalb ist die Prüfung jetzt ein Skript statt einer Handlung:
`tools/check_amd_builds.sh` sichert `amd/build/`, lässt den vollständigen
Grunt-Lauf laufen und vergleicht **jede** Datei — auch die Maps, und auch
Dateien, die Grunt nicht mehr erzeugt.

**Lehre:** Ein Build-Artefakt ist nie nur die eine Datei, die man im Kopf hat.
Nach `grunt` das gesamte Verzeichnis vergleichen, nicht die Datei, deren
Änderung man erwartet hat.

Alle fünf Feature-Dateien wurden zusätzlich auf dasselbe Muster geprüft.

---

## Inkrement 13 — Rückmeldungen aus dem laufenden Betrieb (2.0.0-beta.6, 2026090110)

Zehn Anforderungen aus Ralfs Test am echten System, in dieser Reihenfolge
abgearbeitet.

**1. Reiterreihenfolge.** Einstellungen direkt nach der Aktivität, dann Medien,
Untertitel & Lücken, Versuche, Export. Eine Zeile in der `secondary`-Subklasse,
Test nachgezogen.

**2. Overlay erzwingt den Pausemodus.** Eine Einblendung zeigt nur den gerade
laufenden Cue. Liefe die Wiedergabe weiter, verschwände genau der Satz vom
Bildschirm, den jemand gerade ausfüllt. Das ist keine Wahl, also wird sie nicht
angeboten: `hideIf` im Formular **und** `playback_settings::resolve()` erzwingt
`auto`. Nur eines von beidem hätte eine Lücke zwischen Formular und Verhalten
gelassen.

**3. Untertitel verschwanden beim Anhalten.** Der eigentliche Bug, und die
Ursache lag in meinem Fix aus Inkrement 8: Beim Anhalten setzte ich
`currentTime = endOf(crossed)`. Der Cue-Test ist `ms >= start && ms < end` —
genau auf der Kante ist **kein** Cue aktiv, `activate(null)` leerte das Overlay.
Ralfs Beobachtung „exakt in der Millisekunde des Anhaltens" war wörtlich
zutreffend.

Zwei Korrekturen: die Wiedergabe landet 1 ms **innerhalb** des Cues, und im
Overlay-Modus wird die Einblendung bei fehlendem aktiven Cue gar nicht mehr
geleert — auch zwischen zwei Cues bliebe sonst kurz nichts stehen.

**Lehre:** Eine Bereichsprüfung mit halboffenem Intervall und ein Seek genau auf
die obere Grenze schließen sich aus. Wer auf eine Kante springt, muss wissen, zu
welcher Seite sie gehört.

**4. Ausgefüllte Lücken halten nicht mehr an.** `hasOpenGaps()` entscheidet, ob
eine Cue-Grenze überhaupt anhält, und die Enter-Navigation überspringt bereits
beantwortete Lücken. Eine fertige Lücke ist erledigte Arbeit; an ihr anzuhalten
verlangt einen Tastendruck für nichts.

**5. Fokus in die erste offene Lücke bei Overlay.** Damit steht der Cursor
sofort dort, wo gearbeitet wird — und das ist zugleich, was den ersten Cue
„engaged" macht und die Wiedergabe an seinem Ende anhalten lässt.

**6. Kein doppeltes Transkript.** Bei Overlay wird die Liste unter dem Medium
ausgeblendet. Sie existiert weiter (der aktive Cue wird aus ihr heraus- und
wieder hineinbewegt), sie steht nur nicht mehr auf der Seite. Dieselben Lücken
an zwei Stellen ließen offen, welche zählt.

**7. Größe.** Video und Transkript waren beide unbegrenzt. Jetzt `max-height:
45vh` für das Medium (mit `object-fit: contain`, damit nichts weggeschnitten
wird) und `35vh` für den Transkriptbereich, auf niedrigen Viewports weicht
zuerst das Bild.

**8. Vollbild.** Ein im Vollbild dargestelltes Medienelement wird **allein**
gezeichnet; das Geschwister-Overlay mit den Lücken ist dort schlicht nicht
vorhanden. `attachFullscreenRedirect()` hört auf `fullscreenchange` und hebt die
Anforderung auf die Bühne, die beides enthält. Auf iOS ist das Vollbild ein
Systemplayer, der kein HTML aufnehmen kann — dort spielt das Medium ohne
Einblendung, und beim Verlassen geht nichts verloren. Issue #3 lässt den
dokumentierten Fallback ausdrücklich zu.

**9. Symbole statt Text.** Haken, Kreuz und Warndreieck statt „Richtig/Falsch",
Prüfen und Hinweis als dezente Icon-Buttons. Die Wortlaute sind **nicht**
verschwunden, sondern in den zugänglichen Namen und den Tooltip gewandert — der
Zustand darf nicht an Form und Farbe allein hängen.

**10. „Versuch abschließen".** Vor der Schaltfläche steht jetzt „x von y Lücken
beantwortet". Sind alle gefüllt, ist die Schaltfläche primär; sind welche offen,
ist sie eine Umriss-Schaltfläche und fragt einmal nach. Bewusst **nicht**
gesperrt: eine Übung, die man unfertig nicht abgeben kann, gibt man gar nicht
ab.

### Verifikation

```
tools/check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
                           player.min.js  7833b664 → c3d4b025
Grunt (vollständig):       Exit 0
stylelint:                 0 Fehler
PHPUnit:                   413 Tests, 1200 Assertions, 1 skipped
PHPCS:                     0 Errors / 0 Warnings
moodlecheck:               0 <e>-Tags
Behat:                     42 Szenarien / 426 Steps, alle grün
```

Das neue `check_amd_builds.sh` hat beim ersten Lauf sofort zugeschlagen und
`player.min.js` **und** die Map als veraltet gemeldet — genau der Fehler, den es
verhindern soll.

---

## Inkrement 14 — Testabdeckung im Player, CI-Fix, Review-Befunde (2.0.0-beta.7, 2026090111)

### Die Player-Logik ist jetzt unit-testbar

In dieser Sitzung sind vier Playback-Fehler aufgetreten — verschwindende
Einblendung, anhalten an fertigen Cues, Enter auf beantworteten Lücken, Seek
innerhalb desselben Cues. **Alle vier wurden im Browser gefunden, keiner von
einem Test.** Der Grund war strukturell: die Entscheidungen steckten in
`attachSync()` und `attachPlaybackFlow()` und waren nur über ein Medienelement,
eine Cue-Liste und einen Browser erreichbar.

`amd/src/playback.js` zieht sie heraus — bewusst **ohne jeden Import**, denn
genau das macht sie testbar:

| Funktion | Frage |
|---|---|
| `activeCueIndex()` | Welcher Cue läuft gerade? |
| `pauseLandingTime()` | Wo parkt die Wiedergabe nach dem Anhalten? |
| `shouldStopAtBoundary()` | Hält diese Cue-Grenze an? |
| `nextOpenGapIndex()` | Welche Lücke kommt als Nächste? |
| `needsSeekToCue()` | Muss überhaupt gesprungen werden? |
| `autoScrollSuppressed()` | Ist Auto-Scroll gerade unerwünscht? |

18 Jest-Tests, geschrieben **aus den Fehlermeldungen**: ein Cue besitzt seinen
Start und nicht sein Ende; das Anhalten parkt innerhalb des Cues und nicht auf
der Kante (`activeCueIndex(cues, pauseLandingTime(cue))` muss denselben Cue
liefern); ein vollständig beantworteter Cue hält nie an; die Lückennavigation
läuft nicht im Kreis.

`player.js` ruft dieselben Funktionen auf — die Tests prüfen also das reale
Verhalten und nicht eine zweite Kopie davon.

Damit Jest ein AMD-JS-Modul lädt: `allowJs` in `tsconfig.json` und eine
`transform`-Regel für `.js` in der Jest-Konfiguration.

**Lehre:** Wenn Fehler nur im Browser auffallen, ist das ein Struktur- und kein
Sorgfaltsproblem. Entscheidungslogik, die nur über DOM und Medienelement
erreichbar ist, wird nicht getestet — egal wie gut man es vorhat.

### CI: Ralfs Fassung war besser

Ralf lieferte eine korrigierte `moodle-ci.yml`. Sein Fix: **Node 22 vor**
Moodles eigenem `npm install`, dazu `npm ci` und
`npx browserslist@latest --update-db`.

Mein Aufbau setzte Node erst *nach* dem Moodle-Install — Moodles Grunt lief also
auf dem Standard-Node des Runners, und nur der Editor-Build bekam 22. Datei
übernommen und dieselbe Korrektur in `moodle-release.yml` nachgezogen (dort steht
sie vor `moodle-plugin-ci install`, das Moodles npm-Abhängigkeiten mitinstalliert).

### Review-Befunde

**esbuild (Abschnitt 4.6 des Reviews):** `^0.23.0` fällt unter
GHSA-67mh-4wv8-2f99 (Dev-Server, moderate). Auf `^0.25` gehoben; `npm audit`
meldet **0 vulnerabilities**, das Bundle baut weiterhin reproduzierbar.

**Playwright-Kontrast:** Der letzte Fehlschlag war von mir verursacht. Bootstraps
`.text-muted` (#6a737b) erreicht auf dem getönten Hintergrund der ausgewählten
Cue-Zeile nur 4,36:1 — unter der 4,5:1-Schwelle, und die Tönung stammt aus
meinem eigenen Stylesheet. Eigene Farbe (#565e66), die auf beiden Hintergründen
besteht. Real verifiziert: **Playwright 5/5 gegen eine echte Site**.

**Zum Reviewdokument selbst:** Es beschreibt den `development`-Stand *vor* den
Patches 2.0.88 ff. — es führt `MediaPanel.tsx` und `ImportPanel.tsx` als
vorhanden und die Aktionsbuttons auf `view.php` als offen. Die Issues 01 bis 08
sind seither umgesetzt. Verwertbar sind vor allem Abschnitt 4.5 (Testgates) und
4.6 (Bibliotheken).

### Verifikation

```
npm audit:                 0 vulnerabilities
Jest:                      65/65   (vorher 47/47, +18 Playback)
tsc --noEmit:              sauber
check_amd_builds.sh:       6 Artefakte, alle entsprechen ihren Quellen
esbuild-Bundle:            de8a4b07 → 3e1cf12a, idempotent
PHPUnit:                   413 Tests, 1200 Assertions, 1 skipped
PHPCS:                     0 Errors / 0 Warnings
moodlecheck:               0 <e>-Tags
Behat:                     42 Szenarien / 426 Steps, alle grün
Playwright:                5/5 gegen eine echte Moodle-Site
actionlint:                Exit 0
```

---

## Inkrement 15 — Failure-Injection und ein Datenverlust-Bug (2.0.0-beta.8, 2026090112)

Abschnitt 4.5 des Reviews nennt „Failure-Injection für Autosave/Publish/Import"
als offenes Gate. Beim Schreiben dieser Tests kam ein echter Fehler heraus.

### Der Bug: eine abgelehnte Eingabe löschte die Arbeit

`save_draft_content()` lief in dieser Reihenfolge:

```
delete_version_content()      // der gesamte Inhalt des Drafts
insert_version_content()      // → validate_content_shape() ganz am Anfang
```

Die Validierung stand **innerhalb** des Inserts, also **nach** dem Löschen. Ein
doppelter Cue-Key oder ein unbekannter Bewertungsalgorithmus — beides Zustände,
die eine Bearbeitungssitzung erzeugen kann — ließen den Autor mit **weder dem
alten noch dem neuen Inhalt** zurück.

Der Kommentar an `validate_content_shape()` sagte sogar, sie halte „die
Save-Transaktion davon ab, an einem DB-Fehler abzubrechen" — genau das konnte
sie an dieser Stelle nicht leisten.

Behoben: validieren, dann löschen. Eine abgelehnte Eingabe ist hier der
Normalfall, nicht der Ausnahmefall, und darf nichts anfassen.

### Der zweite Fund: keine einzige Transaktion rollte zurück

Zehn `start_delegated_transaction()` im Plugin, **kein einziges `rollback()`**.
Moodles delegierte Transaktionen wickeln sich nicht von selbst ab: Wer eine
öffnet und eine Exception entkommen lässt, hinterlässt die bereits gelaufenen
Statements. Nur die Übergabe des Fehlers an `moodle_transaction::rollback()`
macht sie rückgängig.

`transaction_trait` kapselt das. `save_draft_content()` nutzt es jetzt als
Auffangnetz für einen echten DB-Fehler mitten im Insert.

### Warum der erste Testversuch nicht funktionierte

Mein erster Test wollte das Rollback direkt nachweisen — und schlug fehl, auch
nach dem Fix. Die Instrumentierung zeigte:

```
AT TEST START tx=OPEN
TX START depth=nested
TX ROLLBACK
after=0
```

**Moodles `advanced_testcase` öffnet selbst eine Transaktion pro Test**
(`$this->testdbtransaction = $DB->start_delegated_transaction();`). Jede
Transaktion im Test ist damit verschachtelt, und ein verschachteltes Rollback
markiert nur den Stapel — die eigentliche Rücknahme passiert erst, wenn die
äußere Transaktion abgewickelt wird, also nach dem Test. **Ein Unit-Test kann
das Rollback nicht beobachten.**

Statt einen Test zu schreiben, der etwas Unbeobachtbares behauptet, prüfen die
Tests jetzt die Garantie, die tatsächlich zählt und beobachtbar ist: eine
abgelehnte Eingabe verändert nichts.

**Lehre:** Wenn ein Test nach einem korrekten Fix weiterhin fehlschlägt, ist die
nächste Frage, ob der Test das Behauptete überhaupt sehen kann. Unter PHPUnit
ist jede Moodle-Transaktion verschachtelt.

### Autosave-Failure-Injection

Vier neue Jest-Tests. Der wichtigste: ein abgelehnter Speichervorgang muss das
`saving`-Flag freigeben. Bliebe es gesetzt, hielte der Controller jeden späteren
Versuch für „läuft schon" und stellte ihn ewig in die Warteschlange — der Autor
sähe „Fehler" und käme nie wieder heraus, ohne Hinweis darauf, dass weitere
Änderungen ins Leere laufen.

### Verifikation

```
PHPUnit:      418 Tests, 1213 Assertions, 1 skipped   (vorher 413/1200)
Jest:         69/69                                    (vorher 65/65)
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
check_amd_builds.sh: alle Artefakte aktuell
Behat:        42 Szenarien / 426 Steps, alle grün
```

---

## Inkrement 16 — `no-alert`, und warum es zweimal durchkam (2026090113)

```
player.js 1063:26  warning  Unexpected confirm  no-alert
```

Das `window.confirm()` stammt aus Inkrement 13 („Versuch abschließen"). ESLint
hat recht, und zwar nicht nur formal: ein natives Confirm ist ungestylt, trägt
keine übersetzte Schaltflächenbeschriftung und gibt den Fokus nirgendwohin
zurück. Ersetzt durch Moodles `Notification.saveCancelPromise()` mit
`triggerElement`, damit der Fokus zur Schaltfläche zurückkehrt.

### Der eigentliche Fehler war mein Prüfskript

In Inkrement 12 hatte ich notiert, dass die CI `--max-lint-warnings 0` übergibt
und mein lokaler Lauf nicht — und es dann **nicht umgesetzt**. Beim Versuch
scheiterte ich damals an

```
Warning: Task "0" not found.
```

und ließ es liegen, statt die Ursache zu suchen. Moodles `.grunt/tasks/eslint.js`
liest `grunt.option('max-lint-warnings')`; Grunt erwartet den Wert **mit
Gleichheitszeichen**:

```
grunt --max-lint-warnings=0
```

Mit Leerzeichen hält Grunt die `0` für einen Tasknamen. `check_amd_builds.sh`
verwendet jetzt die richtige Schreibweise, scheitert also lokal an genau dem,
woran die CI scheitert. Gegengeprüft: der aktuelle Baum läuft damit sauber
durch.

**Lehre:** Eine Prüfung, die eine Fehlerklasse nicht sieht, ist keine Prüfung.
Ich hatte die Lücke erkannt, benannt und dann eine Fehlermeldung als
Sackgasse hingenommen — sie war eine Syntaxfrage.

### Verifikation

```
check_amd_builds.sh (mit --max-lint-warnings=0): sauber, Artefakte aktuell
                    player.min.js  c3d4b025 → neu gebaut
PHPUnit:            418 Tests, 1213 Assertions, 1 skipped
Jest:               69/69
PHPCS:              0 Errors / 0 Warnings
Behat:              42 Szenarien / 426 Steps, alle grün
```

---

## Inkrement 17 — Die übrigen neun Transaktionen (2026090114)

Inkrement 15 hatte den bewiesenen Pfad abgesichert und neun weitere Stellen mit
demselben Fehler stehen lassen. Jetzt sind alle zehn auf `transaction_trait`
umgestellt; `grep start_delegated_transaction` findet im Plugin nichts mehr.

Jede dieser Transaktionen schreibt mehrere Zeilen, die zusammen **eine** Sache
beschreiben — und die Hälfte davon ist jeweils ein Zustand, für den das Plugin
keinen Namen hat:

| Stelle | Was ein halber Durchlauf hinterließe |
|---|---|
| `publish()` | vorige Version archiviert, nichts an ihrer Stelle veröffentlicht — Lernende ohne Übung |
| `set_draft_media()` | Spalten behaupten eine Medienart, die Dateiablage hält eine andere |
| `create_draft_locked()` | Draft angelegt, ohne den Inhalt, von dem er abzweigen sollte |
| `start_attempt()` | Versuch neu gepinnt, aber `totalgaps` noch vom alten Stand |
| `submit_response()` | Antwort gespeichert, Aggregate nicht — Bericht widerspricht dem Versuch |
| `request_hint()` | Hinweisstufe vermerkt, Abzug nie in der Bewertung angekommen |
| `finish_attempt()` | Status gesetzt, Zeitstempel nicht |
| `delete_attempt()` | Antworten weg, Versuch bleibt (oder umgekehrt) |

### Zwei Fehler beim Umstellen, beide von den Tests gefangen

Beim Einwickeln von `submit_response()` fehlten der Closure zuerst
`$responsetext`, dann `$currenttries` in der `use`-Liste. PHP meldet das als
`Undefined variable` erst zur Laufzeit — dreizehn rote Tests, zweimal.

**Lehre:** Code in eine Closure zu verschieben ist keine rein mechanische
Umformung. Jede Variable von außerhalb muss ausdrücklich mitgegeben werden, und
`php -l` sieht davon nichts. Ohne die vorhandene Testabdeckung wäre das als
Laufzeitfehler bei einer lernenden Person aufgeschlagen.

Bei `finish_attempt()` fiel dabei zusätzlich auf, dass der Frühausstieg für
einen bereits abgeschlossenen Versuch vorher ein eigenes `allow_commit()`
brauchte — jetzt ist es schlicht ein `return`, und der Helfer schließt die
Transaktion.

### Verifikation

```
grep start_delegated_transaction: 0 Treffer im Plugin
PHPUnit:      418 Tests, 1213 Assertions, 1 skipped
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
check_amd_builds.sh: sauber
Behat:        42 Szenarien / 426 Steps, alle grün
```

---

## Inkrement 18 — Verlorenes Build-Artefakt und Attempt-Nebenläufigkeit (2026090115)

### Warum die CI trotz grüner lokaler Prüfung „stale" meldete

Diagnose vor Reparatur: Repo geklont, Quelle und Artefakt verglichen.

```
amd/src/player.js        Repo == Arbeitsbaum   (c244dbcc)
amd/build/player.min.js  Repo 4e91f074, Arbeitsbaum c4e369d9
```

Ein Neubau **aus der Repo-Kopie** ergab `c4e369d9` — also genau das, was mein
Arbeitsbaum hatte. Die Quelle war richtig, das Artefakt in der Auslieferung
schlicht nie angekommen.

Ursache ist ein Konstruktionsfehler meines eigenen Ablaufs:
`check_amd_builds.sh` baut und prüft in `moodle/mod/elang` — einer
Wegwerf-Kopie. Das Patch-ZIP wird aus `work/elang` gepackt. Zwischen beiden
liegt ein manueller `cp`, und genau der ist einmal ausgefallen. Das Skript
konnte es nicht bemerken: es sah nur die Kopie, in der es selbst gebaut hatte.

> **Nachtrag aus Inkrement 20: diese Diagnose war falsch.** Der Unterschied kam
> nicht von einem vergessenen `cp`, sondern von der Browserslist-Datenbank.
> Siehe unten. Die `--sync`-Option bleibt trotzdem sinnvoll, sie beseitigt nur
> nicht das Problem, das sie zu beseitigen schien.

Behoben nicht durch mehr Sorgfalt, sondern durch Wegfall des Schritts: das
Skript nimmt jetzt `--sync=<Arbeitsbaum>` und kopiert die frisch gebauten
Artefakte selbst dorthin.

**Lehre:** Eine Prüfung, die ein anderes Verzeichnis betrachtet als das, aus dem
ausgeliefert wird, prüft die falsche Sache. Zwei Kopien und ein Handgriff
dazwischen sind eine Fehlerquelle, keine Vorsichtsmaßnahme.

### Attempt-Nebenläufigkeit

Aus Abschnitt 4.5 des Reviews. Die Optimistic-Guards (`expectedtries`,
`expectedlevel`) waren einzeln getestet, das Zusammenspiel mit den Schreiblocks
nicht. Fünf neue Tests entlang realer Rennen:

- **Zweimal abschließen** darf `timefinish` nicht verschieben. Ein Doppelklick
  oder eine wiederholte Anfrage würde sonst umschreiben, wann jemand abgegeben
  hat.
- **Antwort verliert das Rennen gegen „abschließen"** → abgelehnt, und es bleibt
  weder eine Response-Zeile noch ein veränderter Punktestand zurück. Eine
  nachträglich angenommene Antwort würde eine bereits gemeldete Note ändern.
- **Hinweis verliert das Rennen** → abgelehnt, ohne Abzug. Ein Punktestand, der
  nach der Abgabe fällt, wäre nicht vertretbar.
- **Wiederholtes Starten** (zwei Tabs, Reload) ergibt **einen** Versuch. Zwei
  laufende Versuche einer Person sind ein Zustand, zwischen dem die
  Resume-Logik nicht wählen könnte.
- **Löschen** nimmt die Antworten mit.

### Verifikation

```
PHPUnit:      423 Tests, 1230 Assertions, 1 skipped   (vorher 418/1213)
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        42 Szenarien / 426 Steps, alle grün
```

---

## Inkrement 19 — Issue #8: die Versuchdetailansicht (2.0.0-beta.9, 2026090116)

Die letzte Ebene aus Issue #8 und damit der letzte Teil, der noch auf
`html_table` lief.

**Vorher:** eine Zeile Kopftext („Name — Status — Ergebnis") und eine flache
Tabelle mit sieben Spalten, in der die Transkriptspalte auf 60 Zeichen gekürzt
für jede Lücke desselben Satzes wiederholt wurde.

**Jetzt:** Kopfbereich mit Status-Badge, Punktzahl und Zeitstempeln, darunter
vier Kennzahlen (beantwortet, akzeptiert, genau richtig, mit Hinweis), darunter
die Lücken **gruppiert unter ihrem Cue**.

Die Gruppierung ist der eigentliche Punkt. „Bei welchen Sätzen hat diese Person
gehangen?" ist die Frage, mit der eine Lehrkraft in eine Detailansicht geht —
und die flache Tabelle konnte sie nur beantworten, indem man jede Zeile las und
im Kopf neu gruppierte. Ein Cue mit noch offenen oder falschen Lücken trägt eine
farbige Kante, damit ein langer Versuch überflogen statt gelesen werden kann;
die Symbole je Lücke tragen dieselbe Information, die Kante ist eine Abkürzung
und kein alleiniges Signal.

Gruppiert wird über den Transkripttext aufeinanderfolgender Zeilen, nicht über
eine Cue-ID: `detail()` liefert bereits in Cue-Reihenfolge, und so bleibt die
Klasse ohne zweite Abfrage. Zwei benachbarte Cues mit identischem Text läsen
sich ohnehin gleich.

Die beiden Label-Closures in `report.php` sind entfallen — sie sind jetzt dort,
wo sie gebraucht werden.

### Ein Testbefund

Mein Behat-Szenario erwartete „Back to the overview". Der String heißt seit
jeher „Back to all attempts". Ich hatte die Formulierung aus dem Kopf
geschrieben statt nachgesehen — die Art Fehler, die ein Test in Sekunden findet
und ein Review nicht.

### Verifikation

```
PHPUnit:      423 Tests, 1230 Assertions, 1 skipped
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
Mustache:     6 Templates, 0 Fehler
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 20 — Die wahre Ursache der „stale"-Meldungen (2026090117)

Ralfs Hinweis, die caniuse-Datenbank zu aktualisieren, hat einen Fehler
aufgedeckt, den ich zweimal falsch diagnostiziert hatte.

### Der Befund

```
caniuse-lite installiert: 1.0.30001312   (aus Moodles package-lock, von 2022)
caniuse-lite aktuell:     1.0.30001810
```

Nach `npx update-browserslist-db@latest` — ohne **eine** Änderung an der Quelle:

```
player.min.js  vorher  c4e369d9
player.min.js  nachher 4e91f074
```

Und `4e91f074` ist **exakt das, was im Repository liegt**.

### Was das bedeutet

Rollups Ausgabe hängt von der installierten `caniuse-lite`-Version ab. Moodles
`package-lock.json` pinnt eine mehrere Jahre alte; Ralfs CI-Workflow ruft nach
`npm ci` ausdrücklich `npx browserslist@latest --update-db` auf. Damit bauen CI
und ich aus **identischer Quelle verschiedene Artefakte** — und die CI meldet
mein Artefakt völlig zu Recht als „stale".

Bemerkenswert: das Update meldet „No target browser changes". Die Zielbrowser
sind also gleich, das Minifikat trotzdem nicht. Auf die Meldung zu vertrauen
hätte in die Irre geführt.

### Zwei falsche Diagnosen davor

- **Inkrement 12/16** hielt ich es für fehlende Warnungsprüfung. Die war
  tatsächlich ein Problem, aber ein anderes.
- **Inkrement 18** schrieb ich es einem vergessenen `cp` zwischen Arbeitsbaum
  und Prüfkopie zu und baute `--sync` dagegen. Die Erklärung war plausibel und
  falsch: ein Neubau aus der Repo-Kopie ergab „mein" Artefakt, was ich als
  Beweis für die Quelle nahm — dabei belegte es nur, dass **derselbe Rechner mit
  derselben veralteten DB** dasselbe herausbekommt. Der Vergleich, der die Sache
  entschieden hätte, wäre einer gegen die **CI-Umgebung** gewesen, nicht gegen
  meine eigene.

**Lehre:** Reproduzierbarkeit eines Builds gilt immer nur relativ zu seiner
Werkzeugkette. „Ich baue dasselbe wie vorhin" ist kein Beleg dafür, dass jemand
anders dasselbe baut. Wenn ein Artefakt anderswo abweicht, gehört die
Werkzeugversion zu den ersten Verdächtigen — nicht zu den letzten.

### Behoben

`check_amd_builds.sh` aktualisiert die Browserslist-DB, **bevor** es baut, und
bricht ab, wenn das nicht geht. Damit entspricht meine Werkzeugkette der der CI,
und die Artefakte können nicht mehr auseinanderlaufen.

Die Artefakte im Arbeitsbaum sind auf den korrekten Stand gezogen
(`player.min.js` `4e91f074`) — identisch mit dem, was Ralf inzwischen selbst
ins Repository gebaut hat.

**patch-2.0.110 lieferte an dieser Stelle das falsche Artefakt aus** und würde
den Grunt-Schritt erneut brechen; dieser Patch ersetzt es.

### Verifikation

```
check_amd_builds.sh (mit DB-Update): alle Artefakte entsprechen ihren Quellen
player.min.js: 4e91f074 == Repository == CI-Neubau
```

---

## Inkrement 21 — Der Gap-Inspector (2.0.0-beta.10, 2026090118)

Der letzte offene Teil aus Issue #7. Der Cue-Inspector nutzte `GapRow`
unverändert: Lösung, Abgleich, Varianten und Hinweise untereinander, jede
Variante eine volle Zeile mit eigenem „Entfernen"-Link.

**Jetzt** teilen sich Lösung, Abgleich und akzeptierte Varianten eine Zeile.
Die Varianten lesen sich als kurze Liste von Schreibweisen — eine Lücke hat
üblicherweise ein oder zwei, und jede belegte vorher eine eigene Zeile.

### Ein Nebenbefund, der den Ausschlag gab

Beim Zuschneiden der „Erweiterten Einstellungen" fiel auf, dass drei Felder im
Datenmodell **und** im Webservice existieren, für die es **überhaupt kein
Bedienelement gab**:

- `maxlength` — begrenzt, wie viel Lernende eingeben können
- `linkurl` — Nachschlage-Link neben der Lücke
- `isregex` je Variante

Sie ließen sich bisher nur über einen Import oder direkt in der Datenbank
setzen. Damit war klar, was in den Aufklappbereich gehört: nicht etwas
Vorhandenes zum Verstecken, sondern etwas Fehlendes an einer Stelle, die es
nicht aufdrängt.

### Ein Testbefund

Mein erster Test suchte den Aufklappbereich direkt nach dem Mount — der
Fixture-Cue hat aber gar keine Lücke. Der Test importiert jetzt erst eine über
das Import-Modal und prüft dann, dass der Bereich existiert, **geschlossen**
ist, und die beiden bislang unerreichbaren Felder enthält.

### Verifikation

```
tsc --noEmit: sauber
Jest:         70/70                     (vorher 69/69)
esbuild:      de8a4b07 → 3fafe8ee
check_amd_builds.sh (mit DB-Update): editor.min.js neu gebaut, danach stabil
stylelint:    0 Fehler
PHPUnit:      423 Tests, 1230 Assertions, 1 skipped
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
Behat:        43 Szenarien / 440 Steps, alle grün
```

Damit ist Issue #7 vollständig und alle acht UI-Issues sind abgeschlossen.

---

## Inkrement 22 — Playwright für Player und Cue-Liste (2.0.0-beta.11, 2026090119)

Die beiden Dinge, die weder Unit-Tests noch Behat klären können: **wo** etwas
gezeichnet wird, und wie sich vierzig Cues auf einer gerenderten Seite
verhalten. Von 5 auf **13 Tests**.

Der Seed erzeugt jetzt je eine Aktivität pro Untertitelposition, eine
Audio-Variante, ein Transkript mit 40 Cues — und eine **lernende Person**.

### Drei Befunde beim Schreiben

**(1) Der Fixture-Nutzer konnte den Player gar nicht öffnen.** Alle fünf
Player-Tests scheiterten mit „The exercise could not be loaded". Ursache:
`mod/elang:attempt` hält ausschließlich der Archetyp `student`; der Seed-Nutzer
ist Lehrender. Das ist **korrektes Verhalten** — und es zeigt, dass bis dahin
kein Test den Player tatsächlich starten ließ. Die bestehenden a11y-Tests
prüften nur Barrierefreiheit und waren auf der Fehlermeldung genauso grün.

**(2) Ein echter Fehler: das Overlay blieb leer.** Der aktive Cue wandert erst
bei `timeupdate` in die Einblendung. Vor dem ersten Abspielen war sie also
leer — und die Transkriptliste, die den Satz sonst trüge, ist in diesem Modus
ausgeblendet. Eine lernende Person sah ein Bild und keinen Satz. Behoben durch
einen einmaligen Abgleich beim Rendern.

**(3) Der Fokus landete auf einem unsichtbaren Feld.** „Cursor in die erste
Lücke" suchte in der Cue-Liste — die im Overlay-Modus `display: none` ist. Jetzt
wird über den gesamten Player gesucht, also dort, wo der Cue inzwischen liegt.

Beide Fehler stammen aus Inkrement 13, waren durch Behat und PHPUnit nicht
erreichbar und wären einer lernenden Person sofort aufgefallen.

### Eine Betriebsnotiz

Der Seed brach auf der echten Site mit Exit 255 und **ohne jede Ausgabe** ab:

```
PHP Fatal error: Trait "mod_elang\local\domain\transaction_trait" not found
```

Eine neu hinzugefügte Klassendatei steht nicht im Klassen-Cache von Moodle.
PHPUnit baut den Cache bei jedem Lauf neu, deshalb war dort nichts zu sehen. Auf
einer laufenden Site erledigt das der Upgrade-Schritt beim Versionssprung; wer
Dateien ohne Bump einspielt, braucht `php admin/cli/purge_caches.php`.

**Lehre:** Ein grüner PHPUnit-Lauf sagt nichts über Moodles Caches aus. Neue
Klassendateien gehören auf einer echten Site verifiziert.

### Verifikation

```
Playwright:   13/13 gegen eine echte Moodle-Site   (vorher 5/5)
PHPUnit:      423 Tests, 1230 Assertions, 1 skipped
Jest:         70/70
PHPCS:        0 Errors / 0 Warnings
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 23 — Ein echtes Sicherheitsloch beim Ausliefern von Dateien (2.0.0-beta.12, 2026090120)

Der Review führt unter P0: „den doppelten/abweichenden `mod_elang_pluginfile`-Pfad
entfernen bzw. nachweislich unaufrufbar machen". Beim Nachsehen war es kein
Aufräumpunkt, sondern eine Lücke.

### Der Befund

`lib.php` enthielt **zwei** Datei-Callbacks:

| Funktion | Prüfung |
|---|---|
| `elang_pluginfile()` | `mod/elang:view` **und** `user_can_access_version_file()` |
| `mod_elang_pluginfile()` | nur `mod/elang:view` und „Version gehört zu dieser Aktivität" |

Moodles `file_pluginfile()` (lib/filelib.php:5310) versucht zuerst
`{component}_pluginfile` und fällt erst dann auf `{modname}_pluginfile` zurück:

```php
$filefunction = $component.'_pluginfile';      // mod_elang_pluginfile
$filefunctionold = $modname.'_pluginfile';     // elang_pluginfile
if (function_exists($filefunction)) { ... }
```

Es lief also **die schwächere**. Die sorgfältige Versionsprüfung — mit sieben
eigenen Tests — war unerreichbarer Code.

**Folge:** Eine lernende Person hält `mod/elang:view`. Damit ließ sich über eine
geratene Versions-ID das **Medium eines unveröffentlichten Entwurfs** abrufen.
Kein Datenverlust, aber eine Offenlegung: Autoren erwarten, dass ein Entwurf
ein Entwurf bleibt.

### Behoben

Die schwächere Funktion ist ersatzlos entfernt; die versionsbewusste trägt jetzt
den Namen, den Moodle tatsächlich aufruft. Ihre Regeln und ihre Tests bleiben
unverändert — sie greifen nur endlich.

Dazu ein Test, der genau diese Falle bewacht:

```php
$this->assertTrue(function_exists('mod_elang_pluginfile'));
$this->assertFalse(function_exists('elang_pluginfile'));
```

Und einer, der die Wirkung belegt: eine lernende Person hält `mod/elang:view`,
kommt aber nicht an die Entwurfsversion; die Lehrkraft schon.

**Lehre:** Zwei Funktionen, die dasselbe tun sollen, sind kein Duplikat, sondern
eine Auswahl — und wer sie trifft, steht im Kern, nicht im Plugin. Bei
namensbasierten Callbacks gehört die Auflösungsreihenfolge nachgelesen, bevor
man den Zweitpfad für harmlos hält. Dass die strengere Prüfung gut getestet war,
hat den Fehler eher verdeckt: die Tests waren grün, der Code lief nie.

### Verifikation

```
PHPUnit:      425 Tests, 1235 Assertions, 1 skipped   (vorher 423/1230)
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
Playwright:   13/13 gegen eine echte Site — der Player lädt sein Medium über
              genau diesen Callback, der Weg ist also wirklich durchlaufen
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 24 — External-API-Audit und CI-Gate-Dokumentation (2.0.0-beta.13, 2026090121)

Zwei P0-Punkte aus dem Review.

### Das Audit der fünfzehn External Functions

Ergebnis: **kein Befund.** Alle fünfzehn laufen über `attempt_helper` oder
`authoring_helper`, und diese prüfen Kontext, Capability **und** das übergebene
Objekt:

- `require_owned_attempt()` — der Versuch gehört wirklich der aufrufenden
  Person; `mod/elang:attempt` allein genügt nicht, weil es jede lernende Person
  im Kurs hält
- `require_gap_in_attempt_version()` — die Lücke gehört zur Version, die der
  Versuch angeheftet hat
- `require_manage_version()` — die Version gehört zu der Aktivität, für die
  `mod/elang:manage` geprüft wurde

Nach dem Fund in Inkrement 23 wollte ich das nicht als Behauptung stehen lassen.
`tests/external/security_contract_test.php` liest **`db/services.php`** statt
einer handgeschriebenen Liste und prüft für jede darin deklarierte Funktion die
Vollständigkeit — Klasse, drei Pflichtmethoden, Capability-Angabe, Typ. Dazu
drei Verhaltenstests entlang der realen Angriffe: fremder Versuch, Autoren-
funktion als Lernende, Lücke aus einer anderen Übung.

**Der Punkt daran:** Der Fehler aus Inkrement 23 war nicht, dass eine Prüfung
fehlte, sondern dass niemand nachsah, ob sie **aufgerufen** wird. Ein Test, der
über `db/services.php` iteriert, fängt die nächste Funktion, die ohne
Absicherung hinzukommt — ein Review fängt sie nur, wenn jemand hinsieht.

### Welche Gates blockieren

`docs/dev/ci-gates.md` beantwortet die Frage aus dem Review und benennt ebenso
deutlich, was **nicht** blockiert:

| blockierend | nicht blockierend |
|---|---|
| `lint-php` (2), `lint-js` (1), `phpunit` (5), `behat` (2), `stale-files` (1) | Moodle-`main`-Jobs, `phpmd`, Playwright, k6, JMeter |

Ein grüner Lauf belegt Lint, Unit-, Integrations- und Browsertests über die
gesamte unterstützte Matrix. Er belegt **nicht**, dass die
Barrierefreiheitsprüfungen liefen, dass das Verhalten unter Last unverändert ist
oder dass Moodle `main` unterstützt wird. Diese drei sind vor einer
Stable-Freigabe einzeln anzustoßen.

Dabei glaubte ich, eine Lücke gefunden zu haben: die Upgrade-Tests springen von
**V1** auf den aktuellen Stand, ein Upgrade von einer früheren 2.0-Beta sei
nicht abgedeckt.

> **Nachtrag aus Inkrement 31: das war keine Lücke.** Es wurde nie eine 2.0-Beta
> veröffentlicht. Außerhalb von Entwicklungsrechnern existiert kein
> Zwischenstand, von dem aus aktualisiert werden müsste; alles andere ist eine
> Neuinstallation. Der einzige reale Upgrade-Pfad ist V1 → 2.0, und genau den
> prüft `tests/upgrade_test.php`.

Zwei Angaben habe ich beim Schreiben gegengeprüft und korrigiert: die
`lint-php`-Matrix läuft über PHP 8.1 und 8.4 (nicht eine Version), und der
Backup-Test heißt `tests/backup/restore_test.php`.

### Verifikation

```
PHPUnit:      429 Tests, 1376 Assertions, 1 skipped   (vorher 425/1235)
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
```

---

## Inkrement 25 — Flache String-IDs (2.0.0-beta.14, 2026090122)

Der größte P1-Posten: **356 Sprach-IDs** verlieren ihre Doppelpunkte.
`player:ready` → `player_ready`, `report:heading` → `report_heading`.

### Warum das kein Kosmetikpunkt ist

Moodle und AMOS akzeptieren in einer String-ID nur `[a-z0-9_]`. In der
Doppelpunktform ließe sich das Plugin **nicht ins Plugin-Verzeichnis
veröffentlichen und nicht auf lang.moodle.org übersetzen** — was für ein Plugin,
das genau diesen Weg gehen soll, ein Ausschlusskriterium ist.

### Die zehn Ausnahmen

`elang:manage` und seine neun Geschwister bleiben. Sie benennen die
Capabilities selbst und müssen ihnen buchstabengleich entsprechen.

### Drei Fallen beim Umbau

**(1) Historie nicht umschreiben.** Der erste Durchlauf hat auch `CHANGELOG.md`
und die Sessionprotokolle 001–008 angefasst — Dokumente, die vergangene Stände
beschreiben. Ein Eintrag „`report:heading` umbenannt" darf nicht rückwirkend
„`report_heading` umbenannt" heißen. Rückgängig gemacht, die Historie steht
wieder so da, wie sie war.

**(2) Zur Laufzeit zusammengesetzte IDs.** Drei Stellen bauen ihre ID erst im
Betrieb:

```php
get_string('provider:' . $key, 'mod_elang')     // media_page, get_version_content, media.php
get_string('report:' . $column, 'mod_elang')    // report_overview
```

Für eine Textersetzung unsichtbar. Gefunden mit einer Suche nach *„Präfix in
Anführungszeichen, endet auf Doppelpunkt, wird verkettet"* — dieselbe Suche hat
bestätigt, dass die zwei verbleibenden Treffer ein Lock-Name und ein
Test-Fixture sind, keine String-IDs. Ohne sie hätte PHPUnit `Invalid
get_string() identifier: 'provider:youtube'` gemeldet, was es dann auch tat.

**(3) ESLint hatte anschließend recht.** Mit den Doppelpunkten sind die
Schlüssel gültige Bezeichner, also verlangt `dot-notation` Punktzugriff:
`strings.player_ready` statt `strings['player_ready']`. 21 Stellen umgestellt.

### Auswirkung für den Betrieb

Eine **lokale Sprachanpassung** dieser Strings muss gegen die neuen IDs neu
angelegt werden. Betrifft insbesondere die Anleitung in
`docs/dev/deutsche-bezeichnung-sprachpaket.md` — die dort genannten IDs sind
Capability- und `modulename`-Strings und damit **nicht** betroffen.

### Verifikation

```
Sprach-IDs:   402 gesamt, davon 10 Capability-IDs mit Doppelpunkt, sonst flach
Parität:      en 402 / de 402, keine Differenz
Referenzen:   jede im Code angeforderte ID existiert (Prüfskript über den Baum)
PHPUnit:      429 Tests, 1376 Assertions, 1 skipped
Jest:         70/70
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        43 Szenarien / 440 Steps, alle grün
Playwright:   13/13 gegen eine echte Site — der Player lädt seine Strings zur
              Laufzeit, der Umbau ist damit wirklich durchlaufen
```

Der phpcs-Befund aus Ralfs Lauf (`lib.php`: zwei Leerzeilen am Dateiende) war
zu diesem Zeitpunkt bereits behoben; er stammt aus dem Zwischenstand vor dieser
Korrektur.

---

## Inkrement 26 — Player-Performance bei langen Transkripten (2.0.0-beta.15, 2026090123)

P1-Punkt „Player-Performance für lange Transkripte optimiert". Erst gemessen,
dann geändert — und die erste Änderung war die falsche.

### Messung vor der Optimierung

Fixture auf **400 Cues** vergrößert (Länge einer Unterrichtsaufnahme) und im
Browser instrumentiert:

```
goto=2031ms  playerready=3954ms
```

Meine erste Vermutung — acht serielle Cue-Abrufe und 400 einzelne
`appendChild`-Aufrufe — führte zu parallelem Laden und einem
`DocumentFragment`. Nachgemessen: **keine Verbesserung.**

### Die Phasenmessung

Statt weiter zu raten, die Phasen einzeln gemessen:

```
head=126ms  fetch=706ms  build=82ms  restore=791ms  controls=1ms  wire=53ms
```

**`build` kostet 82 ms.** Meine „Optimierung" zielte auf die billigste Phase.
Die teuerste war `restore` mit 791 ms.

### Der eigentliche Fehler

`restoreState()` lief so:

```js
state.responses.forEach((response) => {
    const wrap = list.querySelector(`.mod_elang-gapwrap[data-gapid="${response.gapid}"]`);
```

Der Attempt-State enthält einen Eintrag **je Lücke**. Jeder davon durchsuchte
das gesamte Transkript — der Aufwand wächst mit dem **Quadrat** der
Transkriptlänge. Ersetzt durch **eine** indizierte Durchmusterung in eine `Map`.

```
restore: 791ms → 419ms
```

Die parallelen Abrufe und das Fragment bleiben: beide sind für sich richtig,
nur eben nicht der Engpass.

### Was ich nicht behaupte

Die Gesamtzeit schwankt in diesem Container zu stark für eine belastbare Zahl
(2,25 / 3,13 / 3,61 s über drei Läufe). Belastbar ist die **Phasenmessung**, und
die ist es, die ich berichte.

Der Regressionstest enthält deshalb **keine** Zeitzusicherung, sondern prüft
das Strukturelle: alle 400 Cues kommen an, und es sind acht Anfragen zu je
fünfzig — nicht vierhundert.

**Lehre:** „Das sieht teuer aus" ist keine Messung. Zwei plausible Kandidaten
waren zusammen 82 ms wert; der wirkliche Engpass stand in einer Funktion, die
ich gar nicht angesehen hatte, weil sie nach Aufräumarbeit aussah.

Ein Nebenbefund: die 400-Cue-Aktivität wurde vom Seed nie **veröffentlicht**,
also zeigte `view.php` seinen Leerzustand und der Player lief gar nicht. Die
vorhandenen Cue-Listen-Tests waren trotzdem grün — sie öffnen `edit.php`, nicht
den Player.

### Verifikation

```
Playwright:   14/14 gegen eine echte Site, mit 400 Cues   (vorher 13/13 bei 40)
PHPUnit:      429 Tests, 1376 Assertions, 1 skipped
Jest:         70/70
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 27 — Berichtsleistung, Rechte- und Roadmap-Dokumentation (2.0.0-beta.16, 2026090124)

Drei P1-Punkte. Einer endete mit „nichts zu tun" — belegt statt behauptet.

### Berichtsabfragen: gemessen, kein Handlungsbedarf

Der Verdacht war die Sortierung über `u.lastname` ohne eigenen Index. 20 000
Versuche in **eine** Aktivität eingefügt und gemessen:

| Fall | Zeit |
|---|---|
| Standardliste (Seite 1) | 10,9 ms |
| Seite 300 | 15,4 ms |
| Sortierung nach Name | 43,3 ms |
| Sortierung nach Punktzahl | 11,4 ms |
| Zähler | 1,7 ms |
| Kennzahlen | 4,1 ms |
| Filter nach Status | 10,0 ms |

`EXPLAIN (ANALYZE)` zeigt einen Index-Scan auf `elangid`, danach einen Top-N-Sort
über rund 15 000 Zeilen in 5 ms. 20 000 Versuche in einer einzigen Aktivität
sind bereits unrealistisch viel.

**Kein Index hinzugefügt.** Ein Index ist keine kostenlose Vorsichtsmaßnahme: er
verlangsamt jedes Schreiben und muss migriert werden. Ohne belegten Nutzen wäre
das Aufwand gegen ein Gefühl.

### Roadmap raus aus dem Code

Drei Kommentare beschrieben **umgesetzten** Code als „das 2.1-Feature" —
`gap_rule_generator`, `special_characters` — und ein Schematest begründete sich
mit einer Meilensteinnummer statt mit der Eigenschaft, die er belegt.

Solche Vermerke altern schlecht: sie werden zur Unwahrheit, sobald sich der Plan
ändert oder die Funktion früher kommt. Sie stehen jetzt in
`docs/dev/roadmap.md`, wo sie gepflegt werden können, und die Kommentare sagen,
was der Code tut.

`docs/dev/roadmap.md` hält außerdem fest, was **bewusst** nicht gemacht wurde
und warum — etwa der postMessage-Adapter für Anbieter-Embeds, der cross-origin
in keiner hier verfügbaren Umgebung verifizierbar ist.

### Rechte-Dokumentation

`docs/dev/capabilities.md` leitet die Matrix aus `db/access.php` ab und benennt
die drei Stellen, an denen eine Capability **nicht** die ganze Antwort ist:

- `attempt` hält jede lernende Person im Kurs — ohne Eigentumsprüfung könnte
  jede den Versuch jeder anderen lesen und beschreiben
- `useregex` liegt höher als das übrige Autorenrecht, weil ein unglücklicher
  regulärer Ausdruck gegen Lernendeneingaben ausgewertet wird
- beide Transkript-Exporte hängen zusätzlich an Aktivitätseinstellungen

Dokumentiert ist auch der Befund aus Inkrement 23 als Warnung: zwei Callbacks
sind eine Auswahl, keine Redundanz.

### Verifikation

```
Messung:      20 000 Versuche, EXPLAIN geprüft
PHPUnit:      429 Tests, 1376 Assertions, 1 skipped
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
```

---

## Inkrement 28 — RTL, schmale Bildschirme und die V1-Ausstiegsstrategie (2.0.0-beta.17, 2026090125)

Die letzten beiden P1-Punkte.

### Der RTL-Test fand einen sichtbaren Fehler

Umgestellt auf logische Eigenschaften (`border-inline-start`,
`margin-inline-start`, `inset-inline`) — mit **einer bewussten Ausnahme:** Die
Timeline-Griffe bleiben physisch. Die Timeline zeichnet **Zeit, nicht Text**;
die Wellenform läuft links nach rechts und die Cue-Positionen sind Prozentsätze
der verstrichenen Zeit. Gespiegelt läge der „Start"-Griff am Ende des Klangs,
zu dem er gehört.

Der neue Test prüft zuerst den Zustand **vor** dem Umschalten — und genau das
schlug fehl:

```
Expected: "4px"   Received: "0px"
```

Der Auswahlrand der markierten Cue-Zeile war **überhaupt nie sichtbar**.
Bootstraps `.list-group-flush > .list-group-item` setzt `border-width: 0 0 1px`
bei gleicher Spezifität wie ein Zwei-Klassen-Selektor, und im kompilierten Theme
stand Bootstrap zuletzt. Behoben durch Benennen des Containers im Selektor —
ohne `!important`.

**Lehre:** Ein Test, der den Ausgangszustand mitprüft, bevor er die Änderung
prüft, findet Dinge, die niemand gesucht hat. Hätte ich nur „nach dem Umschalten
rechts" geprüft, wäre der Fehler grün geblieben — beide Seiten wären `0px`
gewesen.

Dazu drei Tests für schmale Bildschirme (390 × 844): kein seitliches Scrollen,
Medium und Transkript passen gemeinsam auf den Schirm, und die Einblendung
bleibt im Bild.

Der RTL-Test braucht **kein arabisches Sprachpaket**, nur die Richtung, die
eines setzen würde — `document.documentElement.dir = 'rtl'`. Das prüft genau
die Frage, die unsere Sache ist: ist die Regel logisch oder physisch?

### V1-Ausstiegsstrategie

Der Mechanismus war vollständig und getestet; es fehlte die **dokumentierte
Bedingung**. `docs/dev/v1-legacy-exit.md` hält fest:

- **Bedingung:** `v1_decommissioner::blockers()` ist die einzige Autorität —
  keine unmigrierte Aktivität, keine unabgenommene Aktivität, und für
  `elang.options` zusätzlich mindestens eine je erfolgte Abnahme. Die dritte
  Bedingung sieht überflüssig aus, verhindert aber, dass eine Site **ohne**
  V1-Vergangenheit die Spalte verliert: „nichts zu migrieren" und „alles
  migriert" sind nicht dasselbe.
- **Wer:** ausschließlich `cli/decommission_v1.php`, von Hand. Kein Upgrade,
  kein Cron, kein Web-Aufruf. Ein Datenverlust, den ein Cron ohne Zutun
  auslöst, ist keine Migration, sondern ein Unfall.
- **Wann der Code selbst geht:** mit der ersten Hauptversion, deren
  Mindest-Moodle-Version über 3.4 liegt — dann existiert kein V1-Pfad mehr. Die
  Abnahmespalten bleiben trotzdem: sie sind der Nachweis, nicht die Quelle.

### Verifikation

```
Playwright:   18/18 gegen eine echte Site   (vorher 14/14)
PHPUnit:      429 Tests, 1376 Assertions, 1 skipped
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
stylelint:    0 Fehler
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 29 — Datenschutz- und Lebenszyklus-Tests (2.0.0-beta.18, 2026090126)

P0-Punkt „Privacy-/Data-Lifecycle-Tests". Elf Tests gab es bereits; die Frage
war, was sie **nicht** abdecken.

### Die Lücke: die Autorenspur

Die vorhandenen Tests prüfen das Löschen von Versuchen und Antworten. Bei der
**Autorenspur** tut der Provider aber etwas anderes als löschen — er
**anonymisiert**: `elang_version.usermodified` und
`elang.migrationapproveduserid` werden geleert, die Inhalte bleiben stehen.

Das ist die richtige Entscheidung: die Versionen gehören dem Kurs, nicht der
Person. Sie mitzulöschen würde die Arbeit anderer vernichten, einschließlich der
Übung, die Lernende gerade bearbeiten. Nur war dieses Verhalten **nirgends
geprüft** — eine spätere Änderung, die statt zu anonymisieren löscht, wäre grün
durchgelaufen.

Fünf Tests dazu: Stempel geleert und Cues erhalten, Migrationsabnahme
ebenfalls geleert, fremde Autorenspur unangetastet, Kurs- und Systemkontext
werden ignoriert statt bearbeitet, und das Leeren einer Aktivität greift nicht
in die nächste.

### Die Lebenszyklus-Frage, die die Privacy-API nicht stellt

Ein Kurs-Aufräumen löscht Aktivitäten **direkt** — an allen Provider-Methoden
vorbei. Wenn `elang_delete_instance()` Versuche und Antworten stehen ließe,
behielte eine Site Lernendenantworten zu einer Übung, die es nicht mehr gibt,
und nichts würde sie je wieder zutage fördern.

`course_delete_module()` räumt korrekt auf. Jetzt ist es zugesichert.

### Vollständigkeit statt Stichprobe

Der letzte Test liest **`db/install.xml`** und verlangt, dass jede Tabelle mit
einer personenbezogenen Spalte in `get_metadata()` beschrieben ist. Eine später
hinzugefügte Tabelle mit `userid` wäre sonst personenbezogene Daten, die die
Privacy-API nie erwähnt — und aufgefallen wäre es erst an einem unvollständigen
Auskunftsexport.

Dasselbe Muster wie beim External-API-Vertragstest in Inkrement 24: über die
Quelle der Wahrheit iterieren, nicht über eine gepflegte Liste.

### Verifikation

```
Privacy:      18 Tests, 74 Assertions   (vorher 11)
PHPUnit:      436 Tests, 1397 Assertions, 1 skipped   (vorher 429/1376)
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
```

---

## Inkrement 30 — Lastszenarien und Akzeptanzschwellen (2.0.0-beta.19, 2026090127)

Der letzte offene P0-Punkt. Die Zahlen kommen von Ralf, die Begründungen
gehören dazu.

### Zwei Szenarien statt einer VU-Zahl

| Szenario | Lernende | Cues | Plateau |
|---|---|---|---|
| `classroom` | 200 | 50 | 120 s |
| `lecturehall` | 2000 | 50 | 180 s |

**50 Cues**, nicht 400: das ist die Länge einer realen Höraufgabe. Ein
400-Cue-Fixture sagt etwas über das Rendern im Browser aus — dafür gibt es den
Playwright-Test aus Inkrement 26 —, aber nichts darüber, wie sich der Server
verhält, wenn viele Menschen gleichzeitig eine normale Übung bearbeiten.

`lecturehall` ist ausdrücklich kein plausibler Dienstagmorgen. Der Zweck ist,
die Klippe zu kennen, bevor jemand anders sie findet.

### Zwei Schwellen, weil es zwei Fragen sind

- **800 ms lässt den Lauf scheitern.** In dieser Übung löst jede Antwort eine
  Anfrage aus; darüber wartet eine lernende Person beim Tippen lange genug, um
  sich zu fragen, ob die Taste angekommen ist — und tippt erneut.
- **300 ms scheitert nicht, wird aber getrennt ausgewiesen.** Eine Verschiebung
  von 280 ms auf 700 ms soll sichtbar sein, **solange sie noch eine
  Verschiebung ist**. Eine Schwelle, die erst bei Schmerz anschlägt, meldet
  nichts, was man noch in Ruhe beheben könnte.

Der Anlauf skaliert mit: über 500 VUs 60 s statt 15 s. Sonst misst man das
Hochfahren statt des Plateaus.

### Real geprüft

k6 lokal installiert und den Plan gegen die Sandbox-Site laufen lassen:

```
checks ..................... 100.00% (1081/1081)
elang_content_errors ....... 0.00%   ✓
elang_content_latency ...... p(95)=908.85ms   ✗
http_req_failed ............ 0.00%   ✓
thresholds on 'elang_content_latency' have been crossed
```

Der **Mechanismus** ist damit belegt: beide Schwellen werden ausgewertet, und
die 800-ms-Grenze lässt den Lauf scheitern.

Die **Zahl** ist ausdrücklich kein Urteil über das Plugin. Gemessen wurde gegen
PHPs eingebauten Entwicklungsserver in einem geteilten Container — 908 ms sagen
etwas über diese Umgebung, nichts über eine Produktionsinstallation. Genau
deshalb liegt neben jedem Ergebnis `k6-run-context.txt`: eine einzelne Zahl ist
kein Urteil, eine Reihe ist eins.

### Verifikation

```
k6-Plan:      läuft, beide Schwellen greifen
seed_large:   nimmt die Cue-Zahl als Argument (50 geprüft)
actionlint:   Exit 0
PHPUnit:      436 Tests, 1397 Assertions, 1 skipped
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
```

Beim Einbau fiel auf, dass der Szenario-Schritt **nach** dem Seed-Schritt stand
und dessen Ausgaben dort noch nicht existierten — `actionlint` hat es gemeldet,
bevor es ein CI-Lauf getan hätte.

---

## Inkrement 31 — Eine Lücke, die keine war (kein Version-Bump)

Ralf hat eine Annahme korrigiert, die ich seit Inkrement 24 durch drei Dokumente
getragen hatte: **es wurde nie eine 2.0-Beta veröffentlicht.**

Damit existiert außerhalb von Entwicklungsrechnern kein Zwischenstand, von dem
aus aktualisiert werden müsste. Alles andere ist eine Neuinstallation — und die
bekommt ihr Schema vollständig aus `db/install.xml`, was jeder der sieben
PHPUnit- und Behat-Läufe je Push mit `moodle-plugin-ci install` durchführt.

Der einzige reale Upgrade-Pfad ist **V1 → 2.0**, und den baut
`tests/upgrade_test.php` mit einer echten V1-Datenbank nach.

Korrigiert in `docs/dev/ci-gates.md`, `docs/dev/roadmap.md` und — als Nachtrag
beim ursprünglichen Eintrag — in Inkrement 24 dieses Protokolls. Ich lasse die
falsche Aussage dort stehen und widerspreche ihr sichtbar, statt sie zu
löschen: wer das Protokoll später liest, soll die Korrektur sehen und nicht
eine geglättete Fassung.

### Was dabei auffiel

Die Savepoints `2026090101` und `2026090102` fügen die vier Spalten hinzu, die
während der Beta-Reihe dazukamen. Für eine Neuinstallation sind sie
bedeutungslos — `install.xml` enthält alle vier. Durchlaufen werden sie nur von
Installationen mit einem früheren 2.0-Zwischenstand, also ausschließlich von
Entwicklungsrechnern.

**Sie bleiben trotzdem.** Sie zu entfernen bräche genau diese Rechner, und der
Gewinn wären zwei `if`-Blöcke weniger. Beim Sprung auf 2.0.0 stable kann die
2.0-interne Kette zusammengefasst werden, sofern dann keine
Entwicklungsinstallation mehr darunter liegt. In `docs/dev/roadmap.md` notiert,
damit es später nicht als ungeklärter Rest wirkt.

**Lehre:** Ich hatte „die Upgrade-Tests decken X nicht ab" als Lücke notiert,
ohne zu prüfen, ob X überhaupt existiert. Eine fehlende Abdeckung ist nur dann
eine Lücke, wenn es etwas zu decken gibt — und diese Frage beantwortet nicht der
Code, sondern der Betrieb.

---

## Inkrement 32 — Ein verlorener Fix und der Test dagegen (2.0.0-beta.20, 2026090128)

Ralfs CI meldete drei PHPUnit-Fehler in zwei Matrix-Läufen:

```
Invalid get_string() identifier: 'provider:youtube' or component 'mod_elang'
* line 86 of /mod/elang/classes/external/get_version_content.php
```

### Der Fix existierte, er kam nur nicht an

Verglichen: im Arbeitsbaum steht `'provider_' . $key`, im Repository
`'provider:' . $key`. Genau **eine** Datei war betroffen.

Ursache ist mein Auslieferungsskript aus Inkrement 25. Es sammelte die
Patch-Dateien danach, ob sie eine umbenannte Zeichenkette **enthalten** — und
`get_version_content.php` baut seine ID zur Laufzeit zusammen. Sie enthält
keinen der neuen Bezeichner, also fiel sie durch das Netz. Die anderen drei
Stellen mit derselben Bauart kamen nur mit, weil sie zufällig noch andere
umbenannte Literale enthielten.

Das ist dieselbe Blindheit, die den ursprünglichen Fehler verursacht hat: eine
Textsuche sieht eine zur Laufzeit gebaute ID nicht. Sie hat hier zweimal
zugeschlagen — einmal beim Umbenennen, einmal beim Ausliefern.

**Ab jetzt wird die vollständige Codebase ausgeliefert.** Ein Patch ist nur so
gut wie die Dateiliste, aus der er gebaut wurde.

### Der Test dagegen

`tests/lang_strings_test.php` sichert drei Eigenschaften:

1. Beide Sprachdateien deklarieren dieselben Bezeichner.
2. Nur Capability-Strings dürfen einen Doppelpunkt enthalten.
3. Jede zur Laufzeit zusammengesetzte ID hat Strings hinter sich.

Der dritte Punkt ist der eigentliche. Mein erster Anlauf hätte den Fehler
**nicht** gefunden: das Muster verlangte ein `_` am Ende des Präfixes, der
Fehler endet aber auf `:`. Aufgefallen ist das nur, weil ich den Defekt zur
Gegenprobe wieder eingebaut habe — der Test lief grün.

Nach der Korrektur:

```
+ 0 => 'get_version_content.php: 'provider:' builds a colon identifier'
FAILURES!
```

**Lehre:** Einen Wächter zu schreiben und ihn für wirksam zu halten, ist zwei
verschiedene Dinge. Ein Test, der eine Fehlerklasse fangen soll, muss einmal
gegen einen echten Vertreter dieser Klasse laufen — sonst weiß man nur, dass er
grün wird.

### Verifikation

```
PHPUnit:      439 Tests, 1411 Assertions, 1 skipped   (vorher 436/1397)
              inklusive Gegenprobe mit wieder eingebautem Defekt
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 33 — Was die grünen Läufe belegen und was nicht (2.0.0-beta.21, 2026090129)

Ralf meldet Playwright und k6 grün. Beides stimmt — und beides belegt weniger,
als es auf den ersten Blick scheint.

### Beide Läufe stehen auf beta.16

```
Playwright  SHA 3aa5263d  →  14 Tests
k6          SHA 3aa5263d  →  vus=25, p95-Limit 1500ms
```

`3aa5263d` ist `2.0.0-beta.16`. Damit fehlen:

- die **vier RTL- und Mobiltests** aus beta.17 (14 statt 18 Tests) — und mit
  ihnen der Nachweis für den Auswahlrand, der dort als nie sichtbar entlarvt
  wurde
- die **vereinbarten Lastszenarien und Schwellen** aus beta.19; gelaufen ist der
  alte Standard mit 25 Nutzern gegen 1500 ms

Grün ist also der Stand beta.16. Nach dem Einspielen von 2.0.120 bis 2.0.123
gehören beide Läufe wiederholt.

### Der wertvollere Befund steckt in den k6-Zahlen

```
25 VUs, 45 req/s
p95 581,8 ms   avg 422 ms   max 1050 ms
Fehlerrate 0 %
```

Bei **25** Nutzern liegt der p95 schon bei 582 ms — nahe an der mit Ralf
vereinbarten 800-ms-Grenze. Das `classroom`-Szenario mit 200 Nutzern würde diese
Grenze auf derselben Infrastruktur zwangsläufig reißen.

Das sagt nichts über das Plugin. Der Modus `selfcontained` bedient die Site mit
**PHPs eingebautem Entwicklungsserver** auf vier geteilten Kernen; der Engpass
ist das Ziel, nicht der Prüfling.

**Das ist ein Entwurfsfehler von mir aus Inkrement 30.** Ich habe die
vereinbarten Szenarien in einen Workflow gelegt, dessen Standardziel sie nicht
tragen kann. Ein rotes Ergebnis, das nichts über den Prüfling aussagt, ist
schlimmer als keines — es gewöhnt daran, die Ampel zu ignorieren.

Korrigiert: ein neues Szenario `smoke` (25 Nutzer) ist der Standard und passt
zum Runner; `classroom` und `lecturehall` gehören in den Modus `external` gegen
eine echte Installation. Der Workflow gibt eine Warnung aus, wenn beides
kombiniert wird, statt es stillschweigend zu tun.

**Lehre:** Eine Akzeptanzschwelle ist ohne die Umgebung, gegen die sie gilt,
unvollständig. „p95 < 800 ms" ist erst dann eine Aussage, wenn dabeisteht,
worauf gemessen wird.

### Verifikation

```
actionlint: Exit 0
```

---

## Inkrement 34 — Eine Schwelle, die keine sein durfte (2.0.0-beta.22, 2026090130)

Ralfs Lauf: p95 507 ms, weit innerhalb der 800-ms-Grenze — und trotzdem
`Error: Process completed with exit code 99`.

### Der Fehler war meiner, nicht der der Zahl

Ich hatte das 300-ms-Ziel als **k6-Schwelle** formuliert und geglaubt,
`abortOnFail: false` mache sie berichtend. Tut es nicht: dieser Schalter
entscheidet nur, ob der Lauf **vorzeitig abbricht**. Für den Exit-Code zählt
allein, ob eine Schwelle überschritten wurde — jede, mit welchem Flag auch
immer.

Damit war jeder Lauf zwischen 300 und 800 ms rot, also genau der Bereich, für
den die zweite Zahl gedacht war.

### Ralfs Frage: „Oder sollten wir auf 500 ms runterschrauben?"

Nein. Eine Zielzahl, die man an die Messung anpasst, misst nichts mehr. 800 ms
ist die Grenze, weil darüber das Tippen zäh wird; 300 ms ist das Ziel, weil sich
die Übung so anfühlen soll. Beides bleibt.

Geändert wurde der **Mechanismus**: die Grenze ist die einzige Latenzschwelle,
das Ziel ist eine Metrik (`elang_content_within_target`, Anteil der Abrufe
darunter) plus eine Klartextzeile in der Zusammenfassung.

### Real geprüft, in beiden Richtungen

```
Ziel 100 ms, p95 324,8 ms, Grenze 800 ms   →  EXIT=0   ✓ der reparierte Fall
Ziel 300 ms, p95 904,1 ms, Grenze 800 ms   →  EXIT=99  ✓ die Grenze greift noch
```

Der erste Lauf ist der entscheidende: Ziel verfehlt, Grenze eingehalten,
Exit 0 — genau die Konstellation, die bei Ralf fälschlich rot war.

**Lehre:** Ein Flag mit einem plausiblen Namen ist keine Zusicherung.
`abortOnFail` klingt nach „scheitert nicht", heißt aber „bricht nicht ab". Ich
hatte die Semantik aus dem Namen erschlossen, statt sie zu prüfen — und die
Prüfung hätte dreißig Sekunden gekostet.

---

## Inkrement 35 — Release-Review beta.22: RR-01 bis RR-03 (2.0.0-beta.23, 2026090131)

Die drei ersten P0-RC-Punkte des neuen Reviews.

### RR-01 — ein echtes Namensleck

Der Personenfilter im Bericht baute seine Auswahlliste aus einer **eigenen**
Abfrage: „alle mit einem Versuch in dieser Aktivität". Die Berichtsabfragen
selbst berücksichtigen dagegen `$currentgroup`.

Im Separate-Groups-Modus sah eine Lehrkraft ohne
`moodle/site:accessallgroups` damit die **Namen** von Lernenden, deren Versuche
der Bericht korrekt verbarg. Ein Name ist personenbezogen; ihn über ein
Auswahlfeld preiszugeben ist dieselbe Offenlegung wie die Zeile selbst.

Behoben, indem die Liste dieselbe Quelle bekommt wie alles andere:
`attempt_report::filter_users()` baut auf `build_list_query()` auf. Damit ist
die Gruppenbeschränkung nicht *auch* implementiert, sondern **dieselbe**.

Der zweite Teil des DoD — Umgehung über einen von Hand gesetzten
`userid`-Parameter — war bereits sicher, weil der Filter durch dieselbe Abfrage
läuft. Ein Test hält das jetzt fest, für Liste, Zähler, Kennzahlen **und**
Export.

### RR-02 — eine Datei, die seit August mitfuhr

`editor.bundle.js.map` stammte vom 19. August. `build.mjs` setzt
`sourcemap: dev` — der Produktionsbuild erzeugt also gar keine Map. Sie war das
Überbleibsel eines Entwicklungsbuilds, wurde eingecheckt und seither in jedem
Release mitgeliefert: 478 KB, die den **vollständigen Quelltext** von
`ImportPanel` und `MediaPanel` enthielten, Monate nachdem beide gelöscht worden
waren.

Entfernt, in `db/removed_files.txt` eingetragen (damit bestehende
Installationen sie verlieren) und in `.gitignore` ausgeschlossen.

`tests/artefacts_test.php` hält den Zustand: keine Map, kein hängender
`sourceMappingURL`, keine gelöschte Komponente in irgendeinem Build-Artefakt,
und jeder Pfad aus `removed_files.txt` ist tatsächlich weg.

**Lehre:** Ein eingechecktes Build-Artefakt ist die einzige Dateiart, die
unbemerkt von ihrer Quelle abweichen und so bleiben kann — nichts baut sie auf
dem Weg ins Release neu. Genau deshalb braucht sie eine Zusicherung, keine
Sorgfalt.

### RR-03 — und eine Einheitenfalle

Cue-Zeiten werden jetzt vor dem Veröffentlichen geprüft: negativer Start, Ende
nicht nach dem Start, Ende hinter der Mediendauer. Die Meldung nennt Cue über
Sortierung und Key.

Beim Schreiben fiel auf, was fast ein Fehlalarm bei **jeder** Übung geworden
wäre: `elang_version.mediaduration` steht in **Sekunden**, Cue-Zeiten in
**Millisekunden**. Ein direkter Vergleich hätte praktisch jede Version
abgelehnt. Umgerechnet — und aufgerundet, denn eine 12,4-Sekunden-Aufnahme wird
als 12 gespeichert, und ein Cue, der bei 12,3 s endet, liegt in ihr. Ein Test
deckt genau diesen Grenzfall ab.

Eine Dauer von 0 (Provider-Embed, oder nie ermittelt) bedeutet **keine**
Obergrenze, nicht „die Aufnahme ist leer". Auch das ist getestet.

### Verifikation

```
PHPUnit:      451 Tests, 1455 Assertions, 1 skipped   (vorher 439/1411)
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
esbuild:      reproduzierbar aus dem exakten Lockfile, ohne Map
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 36 — RR-04 und RR-05: P0-RC vollständig (2.0.0-beta.24, 2026090132)

### RR-04 — die Beschriftung war falsch, nicht der Code

Das Review stellt fest, dass `stop` nicht am Ende eines vollständig
beantworteten Cues anhält, die Beschriftung aber „Immer anhalten" sagt, und
verlangt eine Produktentscheidung.

Die Entscheidung ist schon gefallen: Ralf hat dieses Verhalten in Inkrement 13
ausdrücklich verlangt — „Sind Gaps bereits fertig ausgefüllt, dann das nicht als
Gap gewertet und das Video spielt entsprechend weiter." Also **Variante B**.

Umbenannt zu „An jedem offenen Untertitel anhalten", und dieselbe Aussage steht
jetzt im Hilfetext, im Schemakommentar, im Codekommentar und im Behat-Szenario.
Der Hilfetext nennt zusätzlich die Folge, die man sonst erst beim Benutzen
merkt: **der zweite Durchgang durch eine Übung hält nur noch dort, wo etwas
fehlt.**

### RR-05 — drei falsche Rollenangaben

| Recht | README sagte | tatsächlich |
|---|---|---|
| `useregex` | editing teachers + managers | **nur manager** |
| `exporttranscript` | teachers + managers | **auch students** |
| `deleteattempts` | teachers + managers | **editing** teachers + managers |

Die README ist, was eine Administration liest, **bevor** sie entscheidet, ob sie
etwas ändert. Ein falscher Eintrag dort ist schlimmer als keiner: er wird für
verbindlich gehalten.

Dazu behauptete der Kopfkommentar von `db/services.php`, alle Funktionen seien
am offiziellen Mobile-Service. Nur die lernendenseitigen sind es — und das ist
richtig so: der Autoreneditor ist eine React-Anwendung für einen
Desktop-Browser, und eine Veröffentlichungs-Schnittstelle in einem Kontext
anzubieten, der den Editor nicht zeigen kann, wäre ein Endpunkt ohne
Oberfläche.

### Beides zu einem Vertrag gemacht

Zwei Tests in `tests/artefacts_test.php`: README und `db/access.php` müssen
**genau dieselben** Rechte nennen, und keine Autorenfunktion darf am
Mobile-Service hängen, während jede Lernendenfunktion es muss.

Verglichen wird nicht Prosa, sondern das Prüfbare: ist jedes Recht überhaupt
dokumentiert, und ist keines dokumentiert, das es nicht gibt. Die konkreten
Rollen bleiben Text — aber ein neu hinzugefügtes oder entferntes Recht fällt
jetzt sofort auf, und das war der Weg, auf dem diese drei Abweichungen
entstanden sind.

### Verifikation

```
PHPUnit:      453 Tests, 1477 Assertions, 1 skipped   (vorher 451/1455)
Jest:         70/70
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        43 Szenarien / 440 Steps, alle grün
```

Damit ist **P0-RC des Reviews vollständig** (RR-01 bis RR-05).

---

## Inkrement 37 — RR-06: Import-Härtung und Fokusfalle (2.0.0-beta.25, 2026090133)

### Die Grenzen gehören in den Parser, nicht in den Endpunkt

Das Review nennt `preview_import` als Stelle ohne Maximalmaß. Die Grenzen stehen
jetzt aber im **Parser**: durch `subtitle_parser::parse()` muss jeder Weg —
Modal, Webservice, künftige Aufrufer. Eine Prüfung am Endpunkt hätte die
nächste Eintrittsstelle wieder offen gelassen.

| Grenze | Wert | Begründung |
|---|---|---|
| Inhalt | 2 MB | eine Untertiteldatei zu einer Unterrichtsaufnahme hat einige zehn KB |
| Cues | 4000 | etwa ein voller Tag ununterbrochener Rede |
| Zeilenlänge | 5000 Zeichen | eine Untertitelzeile ist ein Satz |

**Reihenfolge der Prüfungen ist nicht beliebig.** Die Längenprüfung steht vor
dem `preg_split`, denn der Split legt eine Kopie des gesamten Inhalts an —
danach zu verweigern hätte genau die Kosten bezahlt, die die Verweigerung
vermeiden soll.

**Zu viele Cues werden abgelehnt, nicht abgeschnitten.** Die ersten
Viertausend zu behalten gäbe eine Übung ohne Ende zurück, und die schreibende
Person hätte keine Möglichkeit, das zu bemerken.

**Eine zu lange Zeile überspringt nur ihren Block**, mit Warnung. Eine
minifizierte Datei zwischen den Blöcken soll diesen Block kosten, nicht das
Transkript darum herum.

**Ungültiges UTF-8** wird mit dem wahrscheinlichen Grund abgewiesen — eine in
älterer Kodierung gespeicherte Datei — statt die kaputten Bytes in die Datenbank
zu lassen, wo sie später als unerklärliches Transkript auftauchen.

### Die Fokusfalle

Vorher setzte das Modal beim Öffnen den Fokus und fing ihn nicht ein. Tab hinter
dem letzten Bedienelement landete auf der Seite **hinter** dem Hintergrund — die
noch da ist, noch anklickbar, und verdeckt. Der Cursor verschwand schlicht.

Jetzt: Tab und Shift+Tab laufen im Dialog um, und beim Schließen kehrt der Fokus
zu der Schaltfläche zurück, die ihn geöffnet hat. Die Liste der fokussierbaren
Elemente wird bei **jedem** Tastendruck neu ermittelt, nicht einmal beim Öffnen:
der Dialog gewinnt und verliert Bedienelemente im Betrieb — die Anwenden-Knöpfe
werden erst nach der Prüfung aktiv, und die beiden Reiter tauschen ihre Inhalte.

### Drei eigene Testfehler

Meine ersten Tests griffen mit `$cue['transcript']` auf Objekte zu, und meine
„riesige" Datei war mit 1,64 MB kleiner als die 2-MB-Grenze, die sie
überschreiten sollte. Beides von PHPUnit gefunden, bevor es eine Behauptung
wurde.

### Verifikation

```
PHPUnit:      458 Tests, 1488 Assertions, 1 skipped   (vorher 453/1477)
Jest:         71/71                                    (vorher 70/70)
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
esbuild:      neu gebaut, reproduzierbar
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 38 — RR-08: der Schreibpfad, gemessen (2.0.0-beta.26, 2026090134)

Das Review sagt O(N²) voraus und verlangt zuerst eine Messung. Die Messung
widerspricht der Vorhersage in einem entscheidenden Punkt.

### Was herauskam

Vollständiger Antwortdurchlauf über alle Lücken, PostgreSQL 16:

| Lücken | nur Antworten | mit Hinweis je Lücke |
|---|---|---|
| 50 | 2,6 ms/Einreichung, **15 Queries** | 5,2 ms, 30 Queries |
| 200 | 2,9 ms/Einreichung, **15 Queries** | 6,3 ms, 30 Queries |
| 400 | 3,1 ms/Einreichung, **15 Queries** | 6,9 ms, 30 Queries |

**Die Query-Zahl je Einreichung ist konstant.** Das Quadratische existiert, sitzt
aber in den in PHP durchlaufenen Zeilen, nicht in Datenbankrunden: bei
achtfacher Übungslänge steigt die Zeit je Einreichung um rund 20 %.

### Stufe 2 des DoD bewusst nicht gebaut

Bei 400 Lücken — schon eine extreme Übung — kostet eine Einreichung 3,1 ms,
gegen eine Schwelle von 50 ms p95. Mehr als eine Größenordnung Luft.

Das Delta-Update würde eine **korrekte, gut getestete Neuberechnung** durch eine
Fortschreibung ersetzen, die bei jedem Sonderfall auseinanderlaufen kann —
nachträglich geänderte Hinweisabzüge, gelöschte Antworten, Regrading. Dafür
gibt es keinen belegten Anlass.

Abgesichert ist stattdessen die Eigenschaft, deren Verlust wirklich wehtäte: ein
Test prüft, dass die **dreißigste** Antwort nicht mehr Abfragen kostet als die
erste. Eine Zeitzusicherung wäre auf einem geteilten Runner Rauschen, eine
Query-Zahl ist es nicht.

Dabei lernte ich etwas über meine eigene Erwartung: die spätere Antwort kostet
**13** statt 15 Abfragen — die erste legt die Zeile an, spätere aktualisieren
nur. Meine erste Zusicherung war Gleichheit und schlug fehl. Die richtige
Aussage ist „wächst nicht", nicht „ist gleich".

### Ein Fund nebenbei

`delete_attempt()` nahm ein **eigenes** Lock (`attempt:<id>`), während jeder
andere Schreibzugriff `attempt_write_<id>` nimmt. Ein Löschen konnte damit neben
einer gerade bewerteten Antwort laufen — und die Antwort wäre in einen Versuch
zurückgeschrieben worden, den es nicht mehr gibt.

Vereinheitlicht, und ein Test liest die Sperrnamen aus der Klasse, damit die
nächste per-Versuch-Sperre nicht wieder aus der Reihe fällt.

**Lehre:** Der Auftrag war „Performance messen". Gefunden wurde ein
Nebenläufigkeitsfehler — weil Messen heißt, sich den Pfad wirklich anzusehen,
statt nur seine Zahlen abzulesen.

### Verifikation

```
PHPUnit:      460 Tests, 1497 Assertions, 1 skipped   (vorher 458/1488)
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 39 — RR-09 bis RR-13 (2.0.0-beta.27, 2026090135)

### RR-09 — eine Unterstützungszusage ohne Deckung

`supported = [405, 502]` schließt **5.1** ein. Die CI-Matrix sprang von 5.0 auf
5.2. Damit war für eine Version Unterstützung zugesagt, auf der das Plugin nie
installiert worden war.

5.1 ist jetzt in beiden Matrizen (PHP 8.3, PostgreSQL). Der Rest von RR-09 —
finaler Lauf mit exakter SHA dokumentieren, Artefakt daraus erzeugen — ist
Ralfs Teil und in `docs/dev/release-policy.md` als Ablauf festgehalten.

### RR-10 — JMeter entfernt (Variante B)

Der Plan maß **denselben** Endpunkt wie k6, verlangte eine JVM, die sonst
nichts in diesem Repository braucht, und war seit Monaten nicht mit den
Endpunkten mitgezogen worden.

Ein zweiter Lasttest, der dasselbe misst, ist keine zweite Meinung, sondern eine
zweite Pflegeschuld. Entfernt: `.jmx`, die `make jmeter`-Ziele, die
Dokumentation, die Checklistenzeile — und in `db/removed_files.txt` eingetragen,
damit die Datei auch aus bestehenden Installationen verschwindet.

### RR-11 — Audit auf dem eingecheckten Lockfile

Dreimal `npm ci` gefolgt von `npm audit`: Editor, nur Laufzeit, Playwright.
**Null Befunde.** Ausgeliefert werden ohnehin nur React und ReactDOM, und auch
die nicht als Dateien, sondern einkompiliert.

Der React-18-Pin ist dokumentiert als das, was er ist: keine Trägheit, sondern
die Vermeidung von **zwei React-Laufzeiten in einer Seite**, sobald Moodle 5.2
die Mindestversion wird und seine eigene mitbringt. Der Ausstieg hängt an dieser
Bedingung, nicht an einem Datum.

### RR-12 — Barrierefreiheit als Test, nicht als Zusicherung

Vier neue Playwright-Gates: eine lernende Person erreicht und beantwortet eine
Lücke **ohne Maus**, die Abschluss-Schaltfläche ist fokussierbar, und die Übung
funktioniert bei 200 % und 400 % Zoom ohne seitliches Scrollen und ohne
abgeschnittenes Eingabefeld.

Der Tastaturtest tabbt mit einer Obergrenze von 60 Schritten — eine kaputte
Tab-Reihenfolge scheitert damit sichtbar, statt den Lauf hängen zu lassen.

Screenreader-Smoke bleibt manuell; das ist keine Automatisierungsfrage.

### RR-13 — ein Auslieferungsformat

Variante A. Der Grund ist mechanisch: **Moodle installiert ein Plugin, indem es
ein ZIP entpackt.** Was nicht im ZIP ist, existiert auf der Zielsite nicht — auch
nicht `tools/cleanup_stale.sh`, das genau dort gebraucht wird, wo eine
Installation von einem älteren Stand kommt. Ein Aufräumwerkzeug, das nur im
Repository liegt, hilft niemandem, der ein ZIP eingespielt hat.

Die eigentliche Sorge hinter RR-13 war nicht die Paketgröße, sondern ob die
Dokumentation etwas verspricht, das im ausgelieferten Zustand fehlt. Mit
Variante A ist die Antwort: nein, alles Genannte liegt bei.

### Verifikation

```
Playwright:   17/17 gegen eine echte Site   (vorher 13/13)
PHPUnit:      460 Tests, 1498 Assertions, 1 skipped
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
actionlint:   Exit 0
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        43 Szenarien / 440 Steps, alle grün
```

---

## Inkrement 40 — RR-07: Einwilligung vor dem Anbieterrahmen (2.0.0-beta.28, 2026090136)

Ralfs Entscheidung: **Variante B**.

### Umgesetzt

Statt des Rahmens steht eine Hinweisfläche mit Anbieternamen, Erklärung und
Schaltfläche. Das `<iframe>` wird **erst beim Klick erzeugt** — seine `src` wird
vorher nie gesetzt, es geht also tatsächlich nichts hinaus.

Drei Entscheidungen:

- **Site-Einstellung, nicht Aktivitätseinstellung.** Ob ein Anbieter vor der
  Zustimmung kontaktiert werden darf, beantwortet die Einrichtung einmal. Pro
  Übung gefragt, würde daraus eine didaktische Wahl derjenigen, die die
  Aktivität zufällig anlegt.
- **Zustimmung gilt für die Browsersitzung.** Ein Reload fragt nicht erneut; eine
  gespeicherte Präferenz überdauert die Sitzung, in der sie gegeben wurde, und
  hört damit auf, etwas zu sein, dessen Erteilung man bewusst wahrnimmt.
- **Im Zweifel gesperrt.**

### Der letzte Punkt war fast ein Fehler

Meine erste Fassung war `(bool) get_config('mod_elang', 'providerconsent')`. Der
Behat-Lauf zeigte: `get_config()` liefert **`false`**, solange der Admin-Standard
nie geschrieben wurde. Der Schutz wäre im Zweifel **aus** gewesen.

Jetzt `!== '0'`: gesperrt, solange niemand die Sperre ausdrücklich abschaltet.
„Noch nicht entschieden" darf bei einer Datenschutzkontrolle nie „nicht nötig"
heißen. Ein Test hält genau diesen Fall fest.

### Die Frage nach dem Proxy

Ralf fragte, ob sich der Stream über die Moodle-Instanz leiten ließe. Für
YouTube: nein, und die Begründung steht in `docs/dev/provider-embeds.md` — die
Nutzungsbedingungen untersagen es, die signierten IP-gebundenen Segment-URLs
machen jede Umsetzung instabil, und der Moodle-Server würde zum CDN.

Die brauchbare Antwort liegt näher: der Medientyp **direkte URL** nimmt die
Adresse eines institutionellen Medienservers (Opencast, Panopto, Kaltura)
**ohne jede Änderung am Plugin** entgegen. Dann bleiben die IP-Adressen im Haus,
die Einwilligungsfrage entfällt, **und** Untertitelposition sowie Pausemodus
funktionieren vollständig — anders als beim Anbieterrahmen, der seine
Wiedergabezeit nicht meldet. Das steht jetzt im Hilfetext des Feldes, wo die
Entscheidung getroffen wird.

### Zwei eigene Fehler unterwegs

Mein Einfügen kaperte den Docblock der folgenden Behat-Methode — die verlor ihre
`@Given`-Annotation, und Behat blieb an einer Eingabeaufforderung hängen. Und
ich ließ Behat einmal gegen einen **veralteten** `player.min.js` laufen: der
`--sync`-Lauf lag vor der Player-Änderung.

**Lehre zum zweiten:** `check_amd_builds.sh --sync` gehört **nach** die letzte
Änderung an `amd/src/`, nicht irgendwann davor. Der rote Test sah aus wie ein
Fehler in der Logik und war ein Fehler in der Reihenfolge.

### Verifikation

```
PHPUnit:      464 Tests, 1503 Assertions, 1 skipped   (vorher 460/1498)
Jest:         71/71
tsc --noEmit: sauber
PHPCS:        0 Errors / 0 Warnings
moodlecheck:  0 <e>-Tags
Mustache:     0 Fehler
actionlint:   Exit 0
check_amd_builds.sh: alle Artefakte entsprechen ihren Quellen
Behat:        45 Szenarien / 466 Steps, alle grün
```

---

## Inkrement 41 — Ein Docblock und eine blinde Prüfung (2.0.0-beta.29, 2026090137)

```
get_attempt_exercise.php:83  ERROR  Missing docblock for function execute
```

Der Consent-Helfer aus beta.28 landete **zwischen** dem Docblock und der
Funktion, zu der er gehörte. Derselbe Fehler wie bei der Behat-Methode zwei
Inkremente zuvor — beim Einfügen vor einem Anker geht der darüberstehende
Docblock an das neue Element über.

Behoben, und der gesamte Baum auf dasselbe Muster durchsucht: „Docblock mit
`@param`/`@return`, unmittelbar gefolgt von einem weiteren Docblock". **Keine
weiteren Fundstellen.**

### Warum mein lokaler Lauf das nicht meldete

Zuerst vermutete ich einen zu alten `moodle-cs`. Nachgeprüft: der Sniff feuert
lokal einwandfrei — ich habe den Fehler künstlich nachgebaut und phpcs meldete
ihn sofort.

Der Fehler lag in **meinem Prüfbefehl**:

```bash
phpcs --standard=moodle --severity=1 work/elang 2>&1 | tail -3
```

Ein sauberer Lauf endet mit einer Zeitzeile. Ein Lauf mit zwanzig Befunden endet
mit **derselben** Zeitzeile, denn die Befunde stehen darüber. Durch `tail -3`
sahen beide Fälle identisch aus, und ich habe „sauber" gelesen, wo Befunde
standen.

Das ist dieselbe Fehlerklasse wie das fehlende `--max-lint-warnings=0` aus
Inkrement 16: eine Prüfung, die eine Fehlerklasse strukturell nicht sehen kann.
Und wieder war die Ursache nicht das Werkzeug, sondern wie ich es aufrief.

### `tools/verify.sh`

Nichts wird mehr mit dem Auge gelesen. Jede Prüfung meldet über ihren
**Exit-Code**, eine fehlgeschlagene druckt ihre vollständige Ausgabe, und ein
**fehlendes** phpcs ist ein Fehlschlag statt eines stillen Übersprungs — ein
übersprungener Test, der wie ein bestandener aussieht, ist genau die Falle, die
hier zugeschnappt ist.

`moodlecheck` ist die eine Ausnahme: es endet mit 0, auch wenn es Probleme
meldet. Dort ist das Durchsuchen der Ausgabe die Prüfung, und das steht
kommentiert dabei.

### Verifikation, ab jetzt per Exit-Code

```
tools/verify.sh   EXIT=0   (phpcs, moodlecheck, mustache, actionlint)
PHPUnit           EXIT=0   464 Tests, 1503 Assertions, 1 skipped
Jest              EXIT=0   71/71
check_amd_builds  EXIT=0
Behat             EXIT=0   45 Szenarien / 466 Steps
```

---

## Stand der acht UI-Issues

Alle acht sind umgesetzt. Die JS-Unit-Tests zu #3 und #4 kamen mit
`amd/src/playback.js` in Inkrement 14, der Gap-Inspector aus #7 in Inkrement 21.

| Issue | Thema | Stand |
|---|---|---|
| #2 | Navigation und Benennung | **erledigt (beta.2)** |
| #3 | Untertitelposition und Auto-Scroll | **erledigt** |
| #4 | Tastaturfluss und Cue-Pausemodus | **erledigt** |
| #5 | Medienverwaltung als eigener Reiter | **erledigt** |
| #6 | Untertitelimport im Modal | **erledigt** |
| #7 | Editor als synchronisierter Workspace | **erledigt** |
| #8 | Berichte auswertungsorientiert | **erledigt** |
| #9 | Transkriptexport als Exportoberfläche | **erledigt** |

## Entscheidungen dieser Sitzung

| Thema | Entscheidung | Begründung |
|---|---|---|
| Deutsche Bezeichnung | „Video-Diktat" | Ralf; „Sprachübung" zu unspezifisch |
| Sprachpaket-Konflikt | Doku statt Code | Sprachpaket überschreibt Plugin; nur `de_local` gewinnt |
| Lernenden-Export | zwei Aktivitätseinstellungen | Capability-Archetype unangetastet, Bestand bleibt konservativ |
| Reiterlimit | Subklasse hebt 5 auf 6 | Ralf favorisiert Export als Reiter (Antwort 4.4) |
| Provider ohne Zeit-API | saubere Degradation in 2.0 | postMessage-Adapter ist in der Sandbox nicht verifizierbar → eigenes Issue |
| Sonderzeichenleiste | nicht in dieser Sitzung | Ralf; Issue-Entwurf für 2.1 geliefert |
