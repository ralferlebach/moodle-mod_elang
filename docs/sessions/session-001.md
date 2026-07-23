# mod_elang 2.0 — Sitzungsprotokoll (Session 001)

**Diese gesamte Datei dokumentiert EINE Session** — einen Claude-Chat, durchgehend am 23. Juli 2026. Frühere Fassungen dieser Dokumentation hatten fälschlich jeden inhaltlichen Schritt als eigene "Session 00N" geführt; das war falsch (ein Claude-Chat = eine Session) und ist hiermit korrigiert. Die einzelnen Schritte unten bleiben als chronologische Gliederung erhalten, da sie eigenständige, in sich abgeschlossene Arbeitspakete waren — nur die "Session"-Zählung war der Fehler, nicht die Gliederung selbst.

---

## Schritt 1: Projektaufsetzung — Infrastruktur, Anforderungen, Blueprint

### Was wurde erledigt?

- [x] Auswertung der technischen Gesamtbewertung zu `mod_elang` 1.x und Sichtung
      des V1-Quellstands (`src/server`, `db/install.xml`, `pix/`, `version.php`)
- [x] Übernahme der Infrastrukturmuster aus dem Plugin-Stub
      (`local_instantcoursecompletion`): CI-Workflows, Makefile, phpcs, gitattributes,
      Dokumentations- und Prompt-Struktur
- [x] Plugin-Skelett `mod/elang/`: `version.php`, `lib.php`, `mod_form.php`,
      `view.php`, `index.php` (nebst Event `course_module_instance_list_viewed`
      für Moodle 4.5), `db/install.xml`, `db/access.php`, `db/install.php`,
      `db/upgrade.php`, Sprachdateien en/de, Null-Privacy-Provider
- [x] `pix/monologo.svg`, `pix/icon.svg` und `pix/monologo.png`
      (einfarbig `#212529`, 24×24)
- [x] `classes/event/course_module_viewed.php` sowie Aufruf von
      `completion_info::set_module_viewed()` in `view.php`
- [x] Korrektur: `FEATURE_BACKUP_MOODLE2`, `FEATURE_COMPLETION_HAS_RULES` und
      `FEATURE_GRADE_HAS_GRADE` werden im Skelett bewusst mit `false`
      beantwortet. Andernfalls sucht der Kern `backup_elang_activity_task`,
      `\mod_elang\completion\custom_completion` und `elang_grade_item_update()`;
      das Anlegen einer Aktivität und jede Kurssicherung brächen ab.
- [x] `elang_supports(FEATURE_MOD_PURPOSE)` → `MOD_PURPOSE_ASSESSMENT`,
      `elang_is_branded()` → `false`
- [x] CI-Matrix stichprobenartig über die Spanne 4.5 – 5.3 gelegt
      (4.5/8.1, 4.5/8.3, 5.0/8.2, 5.2/8.3, 5.2/8.4, 5.3-dev/8.4); 5.3-Jobs
      nicht blockierend
- [x] PHPUnit-Basistests, Testdatengenerator, Behat-Feature
- [x] `docs/materials/`: Lasten-/Pflichtenheft und Blueprint, Kompaktfassung,
      Machbarkeitsprüfung der Zusatzanforderungen, Migrationskonzept,
      Lizenz-/Herkunftsdokument, Ideen-Backlog
- [x] `docs/prompt-templates/`: sessionstart, sessionende, Planungs-Prompt

---

### Entscheidungen getroffen

| Thema | Entscheidung | Begründung |
|---|---|---|
| Vorgehen | Kompatible Neuentwicklung statt Modernisierung | Datenmodell, Frontend, API, Reporting, Datenschutz und Tests müssten ohnehin vollständig ersetzt werden |
| Komponentenname | bleibt `mod_elang` | Upgradepfad statt Parallelinstallation; Namensfrage im Plugins-Verzeichnis separat zu klären |
| Zielplattform | **Moodle 4.5 LTS bis 5.3 LTS** | Verbreitung: 4.5 ist die meistgenutzte LTS-Version; ein Plugin ab 5.2 erreicht die Bestandsinstallationen von `mod_elang` nicht. Releaseziel bleibt 5.3 (Code Freeze 24.08., Release 05.10.) |
| PHP | **8.1 bis 8.4**, Code auf PHP-8.1-Sprachstand | ergibt sich zwingend aus der Spanne: 4.5 verlangt mindestens 8.1, 5.2/5.3 mindestens 8.3 und unterstützen 8.4 |
| Kursintegration | `index.php` **und** `courseformat\overview` ausliefern | die Aktivitätenübersicht existiert erst ab 5.0; auf 4.5 bleibt die Instanzliste der Weg |
| Versionsunterschiede | Fähigkeitsprüfung statt Versionsvergleich | hält den Fachcode frei von Plattformwissen (L-Q12) |
| 5.3 in der CI | über `main`, nicht blockierend | bewegliches Ziel darf die Pipeline nicht rot färben |
| Aktivitätszweck | `MOD_PURPOSE_ASSESSMENT` | Aktivität bewertet, führt Versuche und schreibt ins Gradebook — wie quiz und assign |
| Privacy im Skelett | `null_provider` | Skelett speichert keine personenbezogenen Daten; Ablösung ist Freigabevoraussetzung für Phase 2 |
| Datenexport | Dataformat API des Kerns | CSV/XLSX/ODS/JSON ohne formatspezifischen Eigencode |
| Dokumentexport | DOCX und ODT nativ über `ZipArchive` | keine Fremdbibliothek, kein Konverter-Zwang, gut testbar |
| Standbilder | browserseitige Erfassung zur Autorenzeit | keine Serverabhängigkeit; `ffmpeg` nur als optionale Ausbaustufe |
| KI-Untertitel | zurückgestellt | Moodle-KI-Subsystem kennt keine Transkriptionsaktion; Aktionen sind Kernbestandteil |
| KI-Videogenerierung | nicht empfohlen | keine Kernaktion, hohe Kosten, geringer didaktischer Ertrag |
| Umfang 2.1 | fünf Vorhaben **verbindlich zugesagt** (Kap. 19) | bestes Verhältnis von didaktischem Nutzen zu Aufwand; 4,2–6,1 PW |
| Stabile Schlüssel | `cuekey` und `gapkey` bereits in 2.0 | ohne sie wäre die für 2.1 zugesagte Neubewertung nachträglich nur per Datenmigration umsetzbar |
| Maintainerschaft | Übergabe angestrebt | Kontakt mit Christophe Demko ist aufgenommen; Rückfallweg bleibt bis zur schriftlichen Bestätigung bestehen |

---

### Entwurfsentscheidungen geändert / zurückgestellt

Gegenüber der technischen Gesamtbewertung wurden drei Punkte präzisiert:

1. **Zielplattform.** Die Bewertung nannte Moodle 5.3 als alleiniges Ziel. Die
   Vorgabe lautet nun: lauffähig **ab Moodle 4.5**, Releaseziel weiterhin 5.3.
   Das kostet Sprachstand (PHP 8.1 statt 8.3) und erzwingt eine zweigleisige
   Kursintegration, erreicht dafür aber die Bestandsinstallationen.
2. **Privacy-Provider.** Statt sofort einen vollständigen Provider zu schreiben,
   der noch nichts zu beschreiben hätte, steht ein `null_provider` mit
   dokumentierter Ablösepflicht.
3. **Umfang 2.1.** Die fünf Backlog-Vorhaben mit dem besten Nutzen-Aufwand-
   Verhältnis wurden aus dem Backlog in den verbindlichen Umfang von 2.1 gehoben
   (Blueprint Kap. 19). Eines davon — die nachträgliche Anerkennung von
   Antwortvarianten — hat eine **Rückwirkung auf das 2.0-Datenmodell** und wurde
   deshalb dort als Vorleistung verankert.

Nicht mehr offen: die Maintainerfrage. Der Kontakt zu Christophe Demko ist
aufgenommen; bis zur schriftlichen Bestätigung bleibt der Rückfallweg dokumentiert,
und das Migrationskonzept ist so gebaut, dass es beide Wege trägt.

---

### Offene Punkte nach diesem Schritt

- [ ] Erster vollständiger CI-Lauf über die Matrix 4.5 – 5.3
- [ ] Referenzübungen und erwartete Bewertungen aus V1 festschreiben
      (Grundlage für `answer_evaluator`)
- [ ] Produktionsnahe V1-Datenmenge für Migrationstests beschaffen
- [ ] Schriftliche Bestätigung der Maintainer-Übergabe abwarten
      (`Lizenz_und_Herkunft.md`, Kap. 3)
- [ ] Phase 2 beginnen: `db/install.xml` um das versionierte Datenmodell erweitern —
      einschließlich `cuekey` und `gapkey` als Vorleistung für 2.1-2
- [ ] `classes/courseformat/overview.php` ergänzen (wirkt ab Moodle 5.0)

---

### Testlauf-Ergebnis

```
PHPUnit: nicht ausgeführt (keine Moodle-Instanz in der Arbeitsumgebung)
PHPCS:   nicht ausgeführt
PHPDoc:  nicht ausgeführt
Behat:   nicht ausgeführt
```

Der erste vollständige CI-Lauf steht noch aus. Bis dahin gilt das Skelett als
**ungeprüft**.

---

## Schritt 2: Erster inhaltlicher Schritt (versioniertes Datenmodell + Bewertungsengine) und CI-Bugfix

### Was wurde erledigt?

- [x] `db/install.xml` um das versionierte Datenmodell erweitert:
      `elang_version`, `elang_cue` (`cuekey`), `elang_gap` (`gapkey`,
      `gradingalgorithm`), `elang_gapanswer` (`isregex`), `elang_gaphint`,
      `elang_attempt` (`exactgaps`/`correctgaps` getrennt), `elang_response`
      (`resultstate`/`accepted` getrennt); `elang.language` und
      `elang.currentversionid` (bewusst ohne deklarierten Fremdschlüssel)
- [x] `db/upgrade.php`: vollständiger XMLDB-Upgrade-Schritt 2026072300 ->
      2026072301; `version.php` entsprechend hochgezählt (Release
      2.0.0-alpha.2)
- [x] `db/subplugins.json` + `classes/plugininfo/elangscript.php`: neuer
      Subplugin-Typ `elangscript` für nicht-lateinische Schriften, mit sowohl
      `plugintypes` als auch `subplugintypes` (MDL-83705, nötig für die
      4.5-5.3-Spanne)
- [x] `classes/local/grading/`: `script_handler` (Interface),
      `latin_script_handler` (Default für lateinische Schriften),
      `script_handler_manager` (Discovery/Routing), `answer_evaluator` (zwei
      Algorithmen `exact`/`wordrecognized`), `grading_result`
      (`resultstate`/`accepted`-Trennung)
- [x] `lib.php`: `elang_delete_instance()` kaskadiert jetzt vollständig durch
      das neue Schema (Subquery-basiert, nicht über PHP-seitige ID-Listen)
- [x] Testdatengenerator um `create_version/cue/gap/gapanswer/gaphint`
      erweitert; PHPUnit-Tests für Handler, Manager, Evaluator, Schema-Rundlauf
      und kaskadierendes Löschen
- [x] **CI-Bugfix:** `--no-init` + manuelles `cd moodle && php admin/tool/
      {phpunit,behat}/cli/init.php` entfernt aus beiden Workflows — das war ein
      veralteter `moodle-plugin-ci`-v3-Workaround (siehe moodlehq/moodle-plugin-ci
      Issue #36 und #203); seit v4 initialisieren `moodle-plugin-ci phpunit`/
      `behat` selbst, wie im aktuellen offiziellen `gha.dist.yml` gezeigt.
      `moodle-plugin-ci savepoints`-Check ergänzt.
- [x] PHP 8.3 + `intl`-Extension und Composer im Arbeitsbereich installiert;
      `moodlehq/moodle-cs` 3.7.0 installiert und den **gesamten Plugin-Code
      real gegen `phpcs --standard=moodle`** geprüft — 0 Fehler, 0 Warnungen
      nach Korrektur von vier real gefundenen Verstößen (siehe unten)
- [x] Blueprint Kap. 5 (Dateistruktur), Kap. 6.1 (Datenmodell) und Kap. 10
      (Bewertung, komplett neu) auf den tatsächlichen Code-Stand gebracht;
      L-F6 und Pflichtenheft P5 präzisiert; Abnahmekriterien ergänzt
- [x] `Migration_V1_V2.md`: neues Kapitel 1.1 zum Fehlen echter V1-Testdaten
      und zum geplanten V1-Datensimulator
- [x] `Blueprint_kompakt.md`, `Ideen_Backlog.md` (F1), `README.md`,
      `CHANGELOG.md`, `docs/prompt-templates/sessionstart.txt` auf den
      tatsächlichen Stand gebracht, inklusive Repository-Link
      (`github.com/ralferlebach/moodle-mod_elang`, Branch `development`)

---

### Entscheidungen getroffen

| Thema | Entscheidung | Begründung |
|---|---|---|
| Bewertungsmodell | genau zwei benannte Algorithmen (`exact`, `wordrecognized`) statt frei kombinierbarer Einzelschalter | Vorgabe des Auftraggebers; klare, erklärbare Autor:innen-Entscheidung statt schwer vorhersagbarem Ähnlichkeitsschwellwert (Jaro-Distanz aus V1 wird nicht fortgeführt) |
| `resultstate` vs. `accepted` | zwei getrennte Felder statt einer einzigen Boolean-Spalte | ein exakter Treffer auf einer lenient konfigurierten Lücke soll als `exact` sichtbar bleiben, nicht als generisches „richtig" verschwinden — Voraussetzung für aussagekräftige Berichte (Backlog D1) |
| Nicht-lateinische Schriften | eigener Subplugin-Typ `elangscript`, kein Bündeln im Kern | Transliterationsschemata (Pinyin, Rōmaji, Revised Romanization, IAST, …) sind je Schrift eigenständige, nicht-triviale Projekte; der Kern bleibt für lateinische Sprachen ohne jedes Subplugin voll funktionsfähig |
| Regex-Antwortvarianten | eigener Mechanismus, zählt bei Treffer immer als `exact` | keine dritte Toleranzstufe zwischen den beiden benannten Algorithmen; Autor:innen mit `mod/elang:useregex` bekommen eine alternative Prüfmethode, keine Zwischenstufe |
| `elang.currentversionid` | absichtlich ohne deklarierten Fremdschlüssel | vermeidet einen zirkulären DDL-Verweis mit `elang_version.elangid`; Beziehung wird anwendungsseitig durchgesetzt |
| CI-Init-Mechanismus | manuelle `cli/init.php`-Aufrufe entfernt | veralteter `moodle-plugin-ci`-v3-Workaround; `moodle-plugin-ci phpunit`/`behat` initialisieren seit v4 selbst — bestätigt gegen das aktuelle offizielle `gha.dist.yml` und zwei echte Produktiv-Repos, nicht geraten |
| Auslieferung | ab sofort ausschließlich als Patch-ZIP | wie im Startprompt unter „Modus zur Delivery" vereinbart |

---

### Entwurfsentscheidungen geändert / zurückgestellt

Keine — alle Grundsätze aus Schritt 1 (A/B/C sowie die neun
Schlüssel-Entwurfsentscheidungen) bleiben unverändert gültig. Ergänzt wurde
Punkt 10 (Bewertungsmodell, siehe oben).

---

### Korrektur in eigener Sache

Der Status-Zwischenstand am Ende der vorangegangenen Antwort in dieser Sitzung
hatte fälschlich gemeldet, dass Blueprint Kap. 5.1/6.1, `Migration_V1_V2.md`
sowie README/CHANGELOG/`sessionstart.txt` mit Repo-Link bereits aktualisiert
seien. Tatsächlich war zu diesem Zeitpunkt nur der Code committet; die
Dokumentation wurde erst in dieser Session real nachgezogen (siehe „Was wurde
erledigt?" oben). Für künftige Sitzungen gilt: Status-Zwischenstände werden vor
dem Melden gegen den tatsächlichen Dateiinhalt geprüft (`grep`), nicht aus der
Erinnerung an die eigene Planung berichtet.

---

### Offene Punkte nach diesem Schritt

- [ ] Ersten echten CI-Lauf gegen 2.0.0-alpha.2 abwarten und auswerten
      (insbesondere: behebt der Init-Fix tatsächlich alle gemeldeten Fehler?)
- [ ] `classes/local/domain/{attempt_manager,version_manager}.php`
- [ ] External Functions für Versuchsstart/-fortsetzung/Antwortabgabe/Hilfe
      (erster echter Schreibpfad auf `elang_attempt`/`elang_response`)
- [ ] Sobald der Schreibpfad existiert: Null-Privacy-Provider durch
      vollständigen Provider ersetzen (Freigabevoraussetzung)
- [ ] `elang.answermaxlength` (aktivitätsweiter Default) ergänzen
- [ ] V1-Datensimulator bauen (`Migration_V1_V2.md`, Kap. 1.1) — Voraussetzung
      für echte Migrationstests, da keine V1-Bestandsdaten mehr verfügbar sind
- [ ] `classes/courseformat/overview.php`

---

### Testlauf-Ergebnis

```
PHPUnit: nicht gegen echte Moodle-Instanz ausgeführt (steht in dieser
         Arbeitsumgebung nicht zur Verfügung); Logik stattdessen über einen
         eigenständigen Smoke-Test gegen die realen Klassendateien geprüft
         (27 Prüfungen, alle bestanden) — siehe Anhang zur vorherigen Antwort
PHPCS:   0 Fehler, 0 Warnungen (moodle-Standard, moodlehq/moodle-cs 3.7.0,
         real ausgeführt gegen den gesamten Plugin-Baum)
PHP-Lint: alle .php-Dateien fehlerfrei (php -l, PHP 8.3.6)
PHPDoc:  nicht separat mit local_moodlecheck geprüft (keine Moodle-Instanz
         verfügbar); phpcs deckt einen Teil der Docblock-Pflichten ab und hat
         3 fehlende Docblocks real gefunden und beheben lassen
Behat:   nicht ausgeführt
```

Erster vollständiger Lauf gegen eine echte Moodle-Instanz steht weiterhin aus.

---

### Nachtrag (später in derselben Sitzung): CI tatsächlich zum Laufen gebracht

Der Nutzer meldete reale CI-Fehler ("Could not open input file:
admin/tool/phpunit/cli/init.php" auf mehreren Jobs). Root-Cause-Analyse in
zwei Schritten:

1. `moodle-plugin-ci` lokal installiert und `MoodleProcess.php` im echten
   Quellcode gelesen statt bei der Fehlermeldung zu raten. Ergebnis: der
   `--no-init` + manuelle `cli/init.php`-Aufruf ist ein veralteter
   v3-Workaround; `moodle-plugin-ci phpunit`/`behat` initialisieren seit v4
   selbst. Entfernt aus beiden Workflows, `savepoints`-Check ergänzt.
2. Zweite Fehlerrunde ("Moodle debugging message was detected" auf dem
   main/5.3-dev-Job): über den echten Quellcode von `MoodleProcess.php`
   verifiziert, dass der Regex `/\+\+ .* \+\+\n\* line/` exakt Moodles
   eigenes `debugging()`-Ausgabeformat sucht — kein Fehlalarm des Tools,
   sondern ein echter `debugging()`-Aufruf beim main-Install unter PHP 8.4.
   Erwartete Instabilität eines unveröffentlichten Branches. Das eigentliche
   Problem war aber, dass `continue-on-error` auf JOB-Ebene den Job-Result
   zwar für `needs:`-Gates maskiert, den GESAMTEN Workflow-Lauf aber trotzdem
   rot erscheinen lässt (bestätigt gegen mehrere unabhängige Quellen). Fix:
   main/5.3-dev-Tests in eigene Jobs (`phpunit-experimental`,
   `behat-experimental`, `ci-experimental`) ausgelagert, mit
   `continue-on-error: true` auf JEDEM Step statt auf Job-Ebene.

Zusätzlich: Delivery-Modus korrigiert (Patch-ZIPs bündeln Code UND Doku
künftig gemeinsam, siehe `sessionstart.txt` Abschnitt F) und das
Stub-Template (`plugin-stub.zip` / `local_instantcoursecompletion`) mit
demselben CI-Fix und derselben Delivery-Korrektur synchronisiert, damit beide
Fehler in künftigen, aus dem Stub gestarteten Projekten nicht wiederkehren.

---

## Schritt 3: Domänenschicht — Versions- und Versuchslebenszyklus

### Was wurde erledigt?

- [x] `classes/local/domain/version_manager.php`: `get_or_create_draft()`,
      `create_draft()`, `get_published()`, `publish()`,
      `compute_content_hash()`
- [x] `classes/local/domain/attempt_manager.php`: `start_attempt()`,
      `submit_response()`, `finish_attempt()`, private
      `recalculate_attempt_aggregates()`
- [x] `tests/local/domain/{version_manager_test,attempt_manager_test}.php`
- [x] Standalone-Smoke-Test mit minimaler In-Memory-`$DB`-Attrappe
      (`fake_db`), lädt die echten Klassendateien ohne Moodle-Bootstrap —
      27 Prüfungen, alle bestanden
- [x] `version.php`: 2026072301 → 2026072302 (2.0.0-alpha.3); kein
      Schema-Wechsel, daher kein neuer `upgrade.php`-Schritt
- [x] `phpcs --standard=moodle` gegen die neuen Dateien: 0 Fehler, 0 Warnungen
- [x] Blueprint Kap. 10.5, `Blueprint_kompakt.md`, `CHANGELOG.md` aktualisiert

---

### Entscheidungen getroffen

| Thema | Entscheidung | Begründung |
|---|---|---|
| Draft-Modell | genau ein Draft je Aktivität, `get_or_create_draft()` liefert ihn idempotent zurück | entspricht dem mentalen Modell „aktueller Bearbeitungsstand"; `create_draft()` bleibt separat für den Fall, dass explizit ein neuer Entwurf gewünscht ist |
| Publish | archiviert die vorherige veröffentlichte Version, löscht sie nie | Voraussetzung dafür, dass laufende Versuche an ihrer Version auswertbar bleiben (P0.1 aus der technischen Gesamtbewertung) |
| Content-Hash | schließt Hilfen und Zeitstempel bewusst aus | nur was die Lernperson zu lösen bekommt, soll den Cache-Schlüssel beeinflussen |
| `submit_response()` ermittelt `elang.language` selbst | Aufrufer übergeben es nicht separat | verhindert, dass ein Aufrufer versehentlich eine andere Sprache als die der Aktivität übergibt |
| Upsert statt Duplikat bei erneuter Antwort | eine Zeile je (Versuch, Lücke) in `elang_response`, `tries` wird hochgezählt | passt zum bereits im Schema festgelegten Unique-Index `attemptid-gapid` |
| Aggregate werden bei jeder Antwort komplett neu berechnet, nicht inkrementell fortgeschrieben | einfacher, beweisbar korrekt, kein Drift-Risiko bei Wiederholungen | Kosten sind vernachlässigbar, da je Versuch nur wenige Dutzend Lücken anfallen |
| State-Guards (`coding_exception` bei falschem Status) | `submit_response()`/`finish_attempt()` verweigern sich bei nicht-`inprogress`-Versuchen | verhindert stille Dateninkonsistenz bei falscher Aufrufreihenfolge |

---

### Entwurfsentscheidungen geändert / zurückgestellt

Keine Änderung an bestehenden Festlegungen. Bewusst zurückgestellt (siehe
CHANGELOG „Known gaps"): Hilfeanfragen (`elang_gaphint` wird noch nicht
konsultiert), maximale Versuchsanzahl (Feld existiert noch nicht auf
`elang`), Completion-/Gradebook-Anbindung (Aggregate existieren, werden aber
noch nirgends gelesen).

---

### Offene Punkte nach diesem Schritt

- [ ] External Functions (`classes/external/`, `db/services.php`) für
      Versuchsstart/-fortsetzung/Antwortabgabe/Hilfe — erster echter
      Schreibpfad von außerhalb der Tests
- [ ] Ablösung des Null-Privacy-Providers — spätestens mit den External
      Functions fällig
- [ ] Hilfeanfragen in `attempt_manager` nachziehen
- [ ] Completion- und Gradebook-Anbindung an die vorhandenen Aggregate
- [ ] `elang.answermaxlength`
- [ ] V1-Datensimulator (weiterhin blockiert auf fehlende Bestandsdaten)
- [ ] `classes/courseformat/overview.php`

---

### Testlauf-Ergebnis

```
PHPUnit: nicht gegen echte Moodle-Instanz ausgeführt (keine Instanz in dieser
         Arbeitsumgebung verfügbar); Logik stattdessen gegen eine minimale
         In-Memory-$DB-Attrappe geprüft (27 Prüfungen, alle bestanden)
PHPCS:   0 Fehler, 0 Warnungen (moodle-Standard, moodlehq/moodle-cs 3.7.0,
         gegen die vier neuen Dateien geprüft)
PHP-Lint: alle vier neuen/geänderten .php-Dateien fehlerfrei (php -l, PHP 8.3.6)
PHPDoc:  nicht separat mit local_moodlecheck geprüft (keine Moodle-Instanz
         verfügbar)
Behat:   nicht betroffen (keine UI-Änderung)
```

Erster vollständiger Lauf gegen eine echte Moodle-Instanz steht weiterhin aus.

---

### Nachtrag (später in derselben Sitzung): erster echter Testlauf, fünf reale Funde

Der Nutzer lieferte den ersten vollständigen `make check`-Lauf gegen eine echte
Moodle-4.5.12-Instanz (PHP 8.2.30, MariaDB 10.11.14). Alle fünf gemeldeten
Probleme waren real und wurden **ohne Versionsbump** behoben (Korrektur der
bereits ausgelieferten 2.0.0-alpha.3, nicht ein neues Release):

1. **`Invalid subtype directory 'mod/elang/script' detected`** — `script/`
   existierte nur in der Dokumentation, nie als physisches Verzeichnis (Git
   trackt keine leeren Verzeichnisse). Fix: `script/README.md` mit dem
   Subplugin-Vertrag angelegt.
2. **XMLDB-`debugging()`-Aufruf** beim Install: `language` und `contenthash`
   hatten `DEFAULT=""` auf `NOTNULL CHAR`-Spalten — von Moodles
   XMLDB-Validator abgelehnt. Fix: `DEFAULT`-Attribut in `db/install.xml`
   **und** die entsprechenden Aufrufe in `db/upgrade.php` entfernt. Dabei
   selbst nachgezogen: `elang_add_instance()` braucht jetzt einen expliziten
   Code-Fallback für `language`, da die Spalte ohne Schema-Default beim
   Insert sonst scheitern könnte, wenn niemand explizit einen Wert setzt.
3. **Vier `assertSame()`-Fehler** (`'237000' is identical to 237000`) —
   Moodles DB-Layer liefert bei `get_record()`/`get_field()` auf MariaDB oft
   Strings für Integer-Spalten, `insert_record()` dagegen echte Ints. Fix:
   `(int)`-Casts an den vier real fehlgeschlagenen Stellen ergänzt — bewusst
   NICHT spekulativ überall, sondern nur dort, wo der reale Lauf sie als
   Fehler gemeldet hat.
4. **`Class fake_script_handler not found`** — Moodles PHPUnit-Discovery
   requiret ausschließlich Dateien nach dem Muster `*_test.php`; die
   ausgelagerte Fixture-Datei wurde nie geladen. Ein erster Fix-Versuch
   (Klasse direkt in die Testdatei einbetten) verletzte dann phpcs' Regel
   "Each class must be in a file by itself" — verworfen. Korrekt: Datei nach
   `tests/fixtures/fake_script_handler.php` verschoben, Namespace passend
   zum Pfad (`mod_elang\fixtures`), explizites `require_once()` in
   `setUpBeforeClass()` — dasselbe Muster, das Moodle-Core selbst für
   geteilte Testfixtures verwendet (verifiziert gegen
   `lib/phpunit/tests/advanced_test.php`).
5. phpcs-Kleinigkeiten (Kommentar-Großschreibung, fehlende Docblocks auf den
   verschobenen Interface-Implementierungen).

Alle Fixes real gegen `php -l` und `phpcs --standard=moodle` (0/0) verifiziert,
plus ein neuer gezielter Smoke-Test für den `elang_add_instance()`-Fallback.
Details siehe `CHANGELOG.md`, Abschnitt „Fixed" unter 2.0.0-alpha.3.

**Wichtigste Lektion für künftige Sitzungen:** Statische Analyse (php -l,
phpcs) und Fake-DB-Smoke-Tests decken eine ANDERE Fehlerklasse ab als ein
echter Moodle-Lauf (Schema-Validierung, PHPUnit-Discovery-Verhalten,
DB-Treiber-Typverhalten). Beides bleibt nötig, keins ersetzt das andere.

---

## Schritt 4: Erster echter Schreibpfad — External Functions + vollständiger Privacy-Provider

### Was wurde erledigt?

- [x] `classes/external/attempt_helper.php` (Trait): `get_attempt_manager()`,
      `require_attempt_ownership()` — geteilt von allen drei Funktionen
- [x] `classes/external/start_attempt.php`: Versuch beginnen/fortsetzen,
      Capability-Prüfung, Prüfung auf veröffentlichte Version
- [x] `classes/external/submit_response.php`: Antwort auf eine Lücke,
      Eigentümerschafts- und Versionsprüfung, 500-Zeichen-Hartgrenze
- [x] `classes/external/finish_attempt.php`: Versuch abschließen
- [x] `db/services.php`: alle drei Funktionen core/ajax- und
      Moodle-App-fähig deklariert
- [x] `classes/privacy/provider.php`: vollständiger Provider ersetzt den
      Null-Provider (metadata\provider, request\plugin\provider,
      request\core_userlist_provider)
- [x] Lang-Strings: 4× `error:*`, vollständiger `privacy:metadata:*`-Satz für
      `elang_attempt`/`elang_response`, alter `privacy:metadata`-String entfernt
- [x] PHPUnit-Tests: 3 Testdateien für die External Functions,
      `tests/privacy/provider_test.php` für den Provider
- [x] `version.php`: 2026072302 → 2026072303 (2.0.0-alpha.4) — notwendig,
      nicht nur Konvention (Moodle liest `db/services.php` nur bei
      Versionswechsel neu ein)
- [x] Blueprint Kap. 7 und Kap. 15 sowie `Blueprint_kompakt.md`, `CHANGELOG.md`
      aktualisiert

---

### Entscheidungen getroffen

| Thema | Entscheidung | Begründung |
|---|---|---|
| Eigentümerschaftsprüfung | zusätzlich zur Capability-Prüfung explizit `attempt->userid === $USER->id` verifizieren | eine Capability sagt nur „darf in diesem Kontext Übungen bearbeiten", nicht „darf DIESEN Versuch bearbeiten" — ohne die Prüfung könnte eine berechtigte Person fremde Versuche anhand erratener IDs lesen/verändern |
| Funktionsname | `mod_elang_submit_response` (Einzahl) statt der ursprünglich geplanten `mod_elang_submit_responses` (Mehrzahl) | passt zur bestehenden, bereits getesteten `attempt_manager::submit_response()`-Signatur; Bündelung mehrerer Antworten ist eine spätere Performance-Optimierung, keine Korrektheitsvoraussetzung |
| Zustandsprüfung doppelt (External Function UND `attempt_manager`) | External Function prüft `state === inprogress` VOR dem Aufruf in die Domänenschicht und wirft eine `moodle_exception` mit verständlicher Meldung; `attempt_manager` wirft bei demselben Zustand weiterhin `coding_exception` | `coding_exception` ist für Programmierfehler gedacht, nicht für plausible Laufzeitfälle wie einen doppelten Klick auf „Abschließen" — die Domänenschicht bleibt UI-unabhängig korrekt, die API-Schicht liefert die bessere Fehlermeldung |
| Kontextauflösung | `\context_module::instance()` (globaler Namensraum), NICHT `\core\context\module::instance()` | Die namespaced Variante taucht nur in neueren (5.1er) Moodle-Dev-Docs auf und existiert möglicherweise auf unserer 4.5-Untergrenze noch gar nicht; die klassische Form ist seit 2011 durchgehend dokumentiert als aktuell und nicht als veraltet markiert |
| Längengrenze | harte 500-Zeichen-Grenze in `submit_response`, nicht konfigurierbar | Verteidigung in der Tiefe bis die eigentliche konfigurierbare Grenze (`elang_gap.maxlength`/Site-Default) über das noch nicht gebaute Autorenwerkzeug gesetzt werden kann |

---

### Entwurfsentscheidungen geändert / zurückgestellt

Keine Änderung an bestehenden Festlegungen. Eine Planungsabweichung
dokumentiert (Funktionsname, siehe oben) — kein Bruch einer der neun
Schlüssel-Entwurfsentscheidungen aus den Schritten 1/2.

---

### Offene Punkte nach diesem Schritt

- [ ] **Erster echter Testlauf gegen eine Moodle-Instanz steht aus** — diese
      Runde wurde nur gegen `php -l` und `phpcs --standard=moodle` (0/0)
      geprüft, nicht gegen eine reale Installation. Nach dem Muster der
      letzten zwei Runden ist mit mindestens kleineren Funden zu rechnen.
- [ ] Lese-External-Functions (`mod_elang_get_exercise`, `mod_elang_get_cues`,
      `mod_elang_get_attempt_state`) — ohne sie kann kein Player etwas
      anzeigen, nur gegen bereits bekannte IDs schreiben
- [ ] `mod_elang_request_hint` + Hilfestufen-Anbindung in `attempt_manager`
- [ ] Completion- und Gradebook-Anbindung an die vorhandenen Aggregate
- [ ] `elang.answermaxlength`
- [ ] V1-Datensimulator (weiterhin blockiert)
- [ ] `classes/courseformat/overview.php`

---

### Testlauf-Ergebnis

```
PHPUnit: nicht gegen echte Moodle-Instanz ausgeführt (keine Instanz in dieser
         Arbeitsumgebung verfügbar) — im Unterschied zu den Schritten 2/3 diesmal
         AUCH kein eigenständiger Fake-$DB-Smoke-Test, da External Functions
         und der Privacy-Provider zu stark auf Moodle-eigene Laufzeit-APIs
         (require_capability, context_module, core_privacy-Klassen) angewiesen
         sind, um sie sinnvoll außerhalb einer echten Moodle-Instanz zu prüfen
PHPCS:   0 Fehler, 0 Warnungen (moodle-Standard, gegen den gesamten
         geänderten/neuen Bestand geprüft, inkl. zweier real gefundener und
         behobener Verstöße: Interface-Reihenfolge, Datei-/Klassenname)
PHP-Lint: alle neuen/geänderten .php-Dateien fehlerfrei (php -l)
Behat:   nicht betroffen (keine UI-Änderung)
```

Erster vollständiger Lauf gegen eine echte Moodle-Instanz steht aus und ist
diesmal ausdrücklich wahrscheinlicher als in den Vorrunden, etwas zu finden,
da External Functions und Privacy-Provider die größte bisher gebaute
Oberfläche zu Moodle-Kern-APIs sind.

---

### Nachtrag (später in derselben Sitzung): erster echter Testlauf, zwei reale Funde

Die Vermutung am Ende des Haupteintrags hat sich bestätigt: der erste echte
Testlauf gegen die Moodle-4.5.12-Instanz fand zwei reale Probleme. CI selbst
war zu diesem Zeitpunkt bereits grün (vorheriger Stand). Beide Funde **ohne
Versionsbump** behoben:

1. **Fatal Error: Class "externallib_advanced_testcase" not found** — dieselbe
   Fehlerklasse wie das `fake_script_handler`-Problem der Vorrunde, diesmal
   bei einer Moodle-Core-Klasse statt einer eigenen Fixture. Recherche ergab:
   `externallib_advanced_testcase` lebt in `webservice/tests/helpers.php` und
   braucht ein explizites `require_once()`. Statt das nachzuziehen, wurde
   der robustere Weg gewählt: alle drei Testklassen erben jetzt direkt von
   `\advanced_testcase`, da keine einzige `externallib_advanced_testcase`-
   spezifische Methode verwendet wurde — nur geerbte Basisfunktionalität.
   Zusatzfund bei der Recherche: `externallib_advanced_testcase` ist ab
   Moodle 4.6 zur Ablösung vorgesehen und empfiehlt isolierte Prozesse wegen
   interner `class_alias()`-Aufrufe — ein weiterer Grund, sie zu vermeiden,
   wo sie nicht gebraucht wird.
2. **phpcpd: 35 duplizierte Zeilen** zwischen `submit_response_test.php` und
   `finish_attempt_test.php` (fast identisches `setUp()`). In
   `tests/fixtures/attempt_test_fixture.php` ausgelagert (Trait,
   `create_single_gap_exercise_with_attempt()`), nach demselben bewährten
   `require_once()`-Muster wie `fake_script_handler.php` — bewusst NICHT auf
   Autoloading verlassen, da der letzte echte Testlauf gezeigt hat, dass das
   für Dateien unter `tests/` nicht zuverlässig funktioniert.

`phpcpd` lokal nachinstalliert und real gegen den ganzen Baum laufen lassen,
um den zweiten Fix zu verifizieren: "No clones found."

---

### Nachtrag 2 (zweiter echter Testlauf): der phpcpd-Fix von eben war selbst kaputt

Ehrlich zu vermerken: der Trait-basierte Fix für die phpcpd-Duplikat-Meldung
aus Nachtrag 1 wurde ausgeliefert, OHNE ihn gegen eine echte PHPUnit-
Aufrufreihenfolge zu prüfen — nur `php -l` und `phpcs` liefen sauber durch,
was den eigentlichen Fehler nicht hätte finden können. Ergebnis: sofortiger
Fatal Error beim ersten echten Lauf, "Trait ... not found".

**Root Cause:** `use TraitName;` INNERHALB einer Klasse wird beim
KOMPILIEREN dieser Klasse aufgelöst — also sobald die Datei geladen wird,
lange bevor irgendeine Methode (auch eine statische `setUpBeforeClass()`)
ausgeführt werden kann. `require_once()` innerhalb von `setUpBeforeClass()`
kommt für einen Trait deshalb strukturell immer zu spät. Das exakt gleiche
Muster funktioniert bei `fake_script_handler` nur deshalb, weil das dort
eine normale KLASSE ist, die ausschließlich per `new` INNERHALB von
Methodenrümpfen referenziert wird (Laufzeit-Referenz, nicht
Kompilierzeit-Referenz).

Ein zweiter Versuch (Datei-Ebenen-`require_once` VOR der Klasse) hätte das
Ladeproblem zwar gelöst, aber gegen eine ANDERE, ebenfalls reale phpcs-Regel
verstoßen (moodle-Standard: ausführender Code auf Dateiebene außerhalb eines
`MOODLE_INTERNAL`-Kontexts = unerwünschte globale Zustandsänderung) — das
wurde diesmal VOR dem Ausliefern selbst durch einen `phpcs`-Lauf gefunden,
nicht erst durch den Nutzer.

**Endgültiger Fix:** `attempt_test_fixture` ist jetzt keine Trait mehr,
sondern eine normale Klasse `attempt_test_fixture_builder` mit einer
öffentlichen statischen `create()`-Methode, ausschließlich aus `setUp()`
heraus aufgerufen — dieselbe Laufzeit-Referenz-Form wie
`fake_script_handler`, damit funktioniert das bereits bewährte
`require_once()`-in-`setUpBeforeClass()`-Muster unverändert.

**Vor dem erneuten Ausliefern diesmal zusätzlich geprüft:** ein eigenes
PHP-Skript, das PHPUnits exakte Aufrufreihenfolge nachstellt (Datei laden ->
Klassen deklarieren -> `setUpBeforeClass()` aufrufen -> erst danach auf die
Fixture zugreifen) und explizit bestätigt, dass die Fixture-Klasse VOR dem
Aufruf nicht und DANACH geladen ist. Das ist genau die Prüfung, die den
kaputten ersten Fix hätte verhindern können.

**Lektion für künftige Sitzungen:** `php -l` und `phpcs` prüfen Syntax und
Stil, aber keine LADEREIHENFOLGE. Bei jedem `require_once`/Autoloading-Fix
lohnt sich ein eigenes Skript, das die tatsächliche Aufrufreihenfolge des
Zielsystems nachstellt, bevor ausgeliefert wird — nicht erst, nachdem der
Nutzer den Fehler ein zweites Mal gemeldet hat.

---

### Nachtrag 3 (dritter echter Testlauf): nur noch ein Fund, plus eine Nutzerfrage

Deutlich besser als die Runden zuvor: nur noch EIN echter Testfehler bei
69 Tests/156 Assertions. Beide Punkte **ohne Versionsbump** behoben:

1. **`tests/privacy/provider_test.php`: `Failed asserting that an array
   contains 110003`** — dieselbe String-vs-Int-Fehlerklasse wie schon mehrfach
   im Produktivcode, diesmal in einer Testassertion: `assertContains()`
   vergleicht seit PHPUnit 9 STRIKT (`===`), vorher lose (bestätigt gegen den
   offiziellen PHPUnit-Bugtracker, sebastianbergmann/phpunit#3426).
   `contextlist::get_contextids()` liefert auf MariaDB/PDO Strings für eine
   Integer-Spalte, `context->id` kam als echter int rein — beide Seiten jetzt
   explizit auf `(int)`/`array_map('intval', ...)` gecastet. Vor dem
   Ausliefern mit einem eigenen Skript verifiziert, das PHPUnits exakten
   `in_array(..., true)`-Vergleich nachstellt: alter Code reproduziert den
   Fehler, neuer Code behebt ihn.
2. **Nutzerfrage:** warum `ERROR: directory not found:
   mod/elang/templates` erscheint, und ob das Verzeichnis nicht schon
   angelegt werden sollte, da Mustache-Templates geplant sind. Antwort: NEIN,
   bewusst nicht — es gibt noch keinen Renderer/Ausgabe-Layer (Phase 3 hat
   nicht begonnen), das Verzeichnis wäre leer und ohne Platzhalter-Datei
   ohnehin nicht versioniert. Stattdessen `tools/mustache_check.php`
   korrigiert: (a) ein liegengebliebenes `@package
   local_instantcoursecompletion` aus dem Stub-Template auf `mod_elang`
   korrigiert, (b) die Meldung bei fehlendem `templates/`-Verzeichnis von
   `ERROR:`/Exit 1 auf `OK:`/Exit 0 umgestellt — blockierte zuvor nichts
   (Makefile ignoriert den Exit-Code, CI filtert ohnehin per
   `grep -v '^OK:'`), war aber irreführend formuliert für einen tatsächlich
   erwarteten, korrekten Zustand.

Damit sind alle bisher bekannten Testfunde aus drei echten Testläufen
behoben. Ausstehend: ein vierter Lauf zur Bestätigung.

---

## Schritt 5: Lese-API — Transkript-Maskierung, get_exercise, get_cues, get_attempt_state

### Was wurde erledigt?

- [x] `classes/local/domain/transcript_masker.php`: ersetzt jede
      Lücken-Zeichenspanne im Transkript durch ein `{{gap:<gapkey>}}`-Token.
      Unicode-Codepoint-basiert (`mb_substr`), überlappende Lücken werfen
      `coding_exception`.
- [x] `classes/external/get_exercise.php`: statische Versionskennzahlen,
      kein Inhalt.
- [x] `classes/external/get_cues.php`: paginierte Cues+Gaps
      (offset/limit, Deckel 200), Transkript immer maskiert, **kein**
      `charstart`/`charlength` je Lücke im Rückgabewert.
- [x] `classes/external/get_attempt_state.php`: Aggregate + Antwortzustand
      je Lücke inkl. eigenem, vorher eingegebenem Antworttext.
- [x] `db/services.php` um alle drei erweitert.
- [x] PHPUnit-Tests für alle vier neuen Klassen, plus eigenständiger
      Smoke-Test für `transcript_masker` (9/9 bestanden).
- [x] `version.php`: 2026072303 → 2026072304 (2.0.0-alpha.5) — notwendig,
      damit `db/services.php` neu eingelesen wird.
- [x] `php -l`/`phpcs` (0/0) und `phpcpd` (No clones found) real geprüft.
- [x] Blueprint Kap. 6.1/7, `Blueprint_kompakt.md`, `CHANGELOG.md`
      aktualisiert.

---

### Entscheidungen getroffen

| Thema | Entscheidung | Begründung |
|---|---|---|
| Transkript-Maskierung | eigene, dedizierte Domänenklasse (`transcript_masker`), **jede** externe Funktion mit Transkript-Rückgabe MUSS sie durchlaufen | `elang_cue.transcript` enthält den vollen Originaltext inklusive Lösungswörter (da `charstart`/`charlength` Positionen im Originaltext referenzieren) — ein direkter Rückgabe-Pfad ohne Maskierung hätte P12 verletzt. Als eigene, pure Funktionsklasse leicht isoliert testbar. |
| `charstart`/`charlength` in `get_cues` | bewusst **nicht** im Rückgabewert je Lücke | die Zeichenlänge der Lösung wäre ein kostenloser, unangeforderter „Wortlänge"-Hinweis — widerspricht dem Design, dass Hinweise ein bewusst anfragbarer, potenziell mit Abzug versehener Mechanismus sein sollen (`elang_gaphint`). Das `{{gap:gapkey}}`-Token im maskierten Transkript reicht dem Player zur Positionierung. |
| Paginierung | einfaches `offset`/`limit` (Deckel 200), kein Zeitfenster-basiertes Fetching | korrekt und für das Lasttest-Ziel (≥1500 Cues) ausreichend; ein positionsbezogenes Fenster-Fetching ist eine mögliche spätere Verfeinerung, keine Korrektheitslücke — Abweichung von der ursprünglichen Blueprint-Formulierung dokumentiert |
| `get_attempt_state` gibt `responsetext` zurück | ja, der eigene vorher eingegebene Text der lernenden Person | keine Lösung (kommt nie aus `elang_gap.solution`/`elang_gapanswer`), sondern nötig, damit ein Player einen laufenden Versuch mit bereits eingegebenem Text wiederherstellen kann |
| Capability | `mod/elang:view` für `get_exercise`/`get_cues`, `mod/elang:attempt` für `get_attempt_state` | Lesen der Übungsform braucht keine Attempt-Berechtigung; Versuchsdaten sind inhärent an einen Versuch gebunden, dieselbe Gatingebene wie die Schreib-Funktionen |

---

### Entwurfsentscheidungen geändert / zurückgestellt

Keine Änderung an bestehenden Festlegungen. Eine Planungsabweichung
dokumentiert (Paginierungsmechanismus, siehe oben) — kein Bruch einer der
neun Schlüssel-Entwurfsentscheidungen.

---

### Offene Punkte nach diesem Schritt

- [ ] **Erster echter Testlauf gegen eine Moodle-Instanz steht aus** — diese
      Runde wurde nur gegen `php -l`, `phpcs` (0/0), `phpcpd` und den
      Standalone-Smoke-Test geprüft.
- [ ] `mod_elang_request_hint` + Hilfestufen-Anbindung in `attempt_manager`
      (inkl. Score-Abzug pro Hint-Level, bisher nicht berücksichtigt)
- [ ] Completion- und Gradebook-Anbindung
- [ ] `elang.answermaxlength`
- [ ] V1-Datensimulator (weiterhin blockiert)
- [ ] `classes/courseformat/overview.php`

---

### Testlauf-Ergebnis

```
PHPUnit: nicht gegen echte Moodle-Instanz ausgeführt (keine Instanz in dieser
         Arbeitsumgebung verfügbar). transcript_masker zusätzlich mit einem
         eigenständigen Smoke-Test gegen die echte Klasse geprüft (9
         Prüfungen, alle bestanden, inkl. Unicode-Codepoint- vs.
         Byte-Offset-Fall gegen echtes mb_strpos() verifiziert)
PHPCS:   0 Fehler, 0 Warnungen (moodle-Standard, gegen den gesamten
         geänderten/neuen Bestand geprüft, inkl. mehrerer real gefundener
         und behobener Verstöße: Zeilenlänge, Kommentar-Großschreibung)
PHPCPD:  No clones found
PHP-Lint: alle neuen/geänderten .php-Dateien fehlerfrei (php -l)
Behat:   nicht betroffen (keine UI-Änderung)
```

Erster vollständiger Lauf gegen eine echte Moodle-Instanz steht aus. Nach dem
Muster der letzten Schritte (3 und 4) mit jeweils real gefundenen
Kleinigkeiten ist auch diesmal nicht auszuschließen, dass etwas auftaucht —
insbesondere die neuen `$DB->get_records()`-Aufrufe mit expliziter
Feldliste/Sortierung/Paginierung in `get_cues.php` wurden bisher nicht gegen
eine echte Datenbank geprüft.

---

### Nachtrag (erster echter Testlauf): zwei Testerwartungen falsch, Produktivcode korrekt

89 Tests, nur 2 Fehler — beide in `test_requires_capability` von
`get_exercise_test`/`get_cues_test`, beide **ohne Versionsbump** behoben.
Die eigentlichen `$DB->get_records()`-Paginierungsaufrufe in `get_cues.php`,
die ich als wahrscheinlichste Fehlerquelle vermutet hatte, liefen
tatsächlich fehlerfrei durch.

**Root Cause (kein Produktivcode-Fehler):** `self::validate_context($context)`
ruft bei `CONTEXT_MODULE` intern `require_login($course, false, $cm, ...)`
auf, das prüft, ob die Aktivität für die aktuelle Person "sichtbar" ist —
über `mod/elang:view`, die Capability, die Moodle per Namenskonvention für
Sichtbarkeit heranzieht. Die Tests prohibited genau diese Capability und
erwarteten `required_capability_exception` vom eigenen, nachfolgenden
`require_capability()`-Aufruf. Tatsächlich schlägt aber bereits
`validate_context()` vorher fehl, mit
`\core\exception\require_login_exception` ("Activity is hidden") — der
eigene Aufruf wird nie erreicht. Beide Ausnahmen verweigern den Zugriff
korrekt; nur die Testerwartung war falsch. Fix: die erwartete
Ausnahmeklasse korrigiert, mit ausführlicher Begründung im Testkommentar,
warum `:attempt`-Tests (wie in `start_attempt_test`) davon NICHT betroffen
sind (keine sichtbarkeitsrelevante Capability).

Lektion in `sessionstart.txt` festgehalten für künftige External-Function-
Tests, die eine `:view`-Capability prohibiten.

---

## Schritt 6: Hilfeanfragen (mod_elang_request_hint) — letzter offener Baustein des Versuchslebenszyklus

### Was wurde erledigt?

- [x] `classes/local/domain/attempt_manager.php::request_hint()`: Hilfestufen
      werden strikt in Reihenfolge freigegeben, `coding_exception` wenn keine
      weitere Stufe existiert. Leere `elang_response`-Zeile bei Hilfeanfrage
      vor jeder Antwortabgabe (`responsetext` explizit `''`, kein
      Schema-Default).
- [x] `recalculate_attempt_aggregates()` überarbeitet: berücksichtigt
      `elang_gaphint.penalty` (nicht additiv über Stufen), aktualisiert auch
      `elang_response.score`, nicht nur das Attempt-Aggregat. Rückwirkende
      Neubewertung bei nachträglicher Hilfeanfrage.
- [x] `classes/external/request_hint.php`, `db/services.php`-Eintrag, neuer
      Sprachstring `error:nomorehints` (EN+DE).
- [x] Tests: `attempt_manager_test.php` um 6 Hilfe-Fälle erweitert,
      `tests/external/request_hint_test.php` neu.
- [x] `php -l`/`phpcs`: 0/0. `phpcpd`: „No clones found".
- [x] Eigenständiger Domain-Smoke-Test um Hilfe-Szenarien erweitert — dabei
      selbst einen Setup-Fehler gefunden und korrigiert (wiederverwendete
      Version hatte schon eine Lücke, `totalgaps` stimmte nicht) — 34/34
      bestanden.
- [x] `version.php`: 2026072304 → 2026072305 (2.0.0-alpha.6).
- [x] Blueprint Kap. 7/10.5, `Blueprint_kompakt.md`, `CHANGELOG.md`
      aktualisiert.

---

### Entscheidungen getroffen

| Thema | Entscheidung | Begründung |
|---|---|---|
| Hilfestufen-Reihenfolge | strikt aufsteigend, kein Sprung auf eine gewählte Stufe | verhindert, dass ein Aufrufer direkt die teuerste/aufschlussreichste Stufe anfordert, ohne die günstigeren vorher gesehen zu haben — entspricht dem pädagogischen Konzept „abgestufte Hilfe" |
| Bestrafung nicht additiv über Stufen | die Strafe EINER Stufe berücksichtigt bereits alles bis einschließlich dieser Stufe | einfacher zu verstehen und zu autorisieren als eine kumulative Summe; die Lehrperson legt pro Stufe direkt fest, wie viel diese Stufe insgesamt kostet |
| `elang_response.score` wird bei jeder Aktion neu berechnet, nicht bei Abgabe fixiert | eine nachträgliche Hilfeanfrage zu einer bereits korrekten Antwort senkt die Wertung rückwirkend | konsistent mit dem bereits etablierten Muster aus `attempt_manager` (Aggregate werden immer aus dem aktuellen Zustand abgeleitet, nie fortgeschrieben) |
| `hinttype='solution'` als möglicher Hilfeninhalt | bewusst erlaubt, kein Sonderfall | eine explizit angeforderte, mit Punktabzug versehene Solution-Stufe ist etwas anderes als eine ungewollte Lösungslecks in `get_exercise`/`get_cues` — Design-Absicht, kein Widerspruch |

---

### Entwurfsentscheidungen geändert / zurückgestellt

Keine Änderung an bestehenden Festlegungen.

---

### Offene Punkte nach diesem Schritt

- [ ] Completion- und Gradebook-Anbindung (muss das jetzt hint-abzugsbewusste
      `elang_attempt.score` lesen, nicht eigenständig neu berechnen)
- [ ] `elang.answermaxlength`
- [ ] V1-Datensimulator (weiterhin blockiert)
- [ ] `classes/courseformat/overview.php`

---

### Testlauf-Ergebnis (vor dem Nachtrag)

```
PHPUnit: nicht gegen echte Moodle-Instanz ausgeführt. Eigenständiger
         Smoke-Test gegen die echten Klassendateien (34 Prüfungen, alle
         bestanden, inkl. rückwirkender Punktabzugslogik)
PHPCS:   0 Fehler, 0 Warnungen
PHPCPD:  No clones found
PHP-Lint: fehlerfrei
```

---

### Nachtrag: kritischer Fund beim ersten echten Admin-UI-Upgrade

Der Nutzer meldete einen Installationsfehler: `coding_exception` — „Key
gapid collides with index gapid specified in table elang_gapanswer" beim
Versuch, das Plugin über die Moodle-Admin-Oberfläche zu aktualisieren.
**Ohne Versionsbump behoben** (weiterhin 2.0.0-alpha.6).

**Root Cause:** `elang_gapanswer` (und, wie eine systematische Prüfung ALLER
Tabellen ergab, auch `elang_attempt`) definierte sowohl einen `KEY`
(Fremdschlüssel) als auch einen separaten `INDEX` auf GENAU demselben
einzelnen Feld (`gapid` bzw. `versionid`). Ein Fremdschlüssel erzeugt
bereits implizit einen Index — der zusätzliche explizite Index ist
redundant und wird von Moodles imperativer `xmldb_table`-API explizit
abgelehnt.

**Warum das erst jetzt auffiel:** Dieser Fehler lag SEIT 2.0.0-alpha.2
unbemerkt im Schema. PHPUnit installiert IMMER frisch über `install.xml`
(`database_manager::install_from_xmldb_file()`), NIEMALS über
`upgrade.php`. Ein echtes Admin-UI-Upgrade nimmt dagegen `upgrade.php`s
imperativen Pfad — und NUR dieser Pfad prüft KEY/INDEX-Kollisionen streng.
Fünf vorherige echte Testrunden (Schritte 2–5) haben ausschließlich den
install.xml-Pfad geprüft; dies ist der erste echte Upgrade-Testlauf
überhaupt.

**Fix:** redundanten Index aus `elang_gapanswer` UND `elang_attempt`
entfernt — in BEIDEN Dateien (`install.xml` und `upgrade.php`), damit
Frischinstallation und Upgrade dasselbe finale Schema erzeugen.

**Wiederaufnehmbarkeit geprüft:** Der fehlgeschlagene `add_index()`-Aufruf
passiert beim Aufbau des In-Memory-Tabellenobjekts, VOR dem eigentlichen
`create_table()` — `elang_gapanswer` wurde also nie angelegt. Alles DAVOR
in demselben Upgrade-Schritt (`elang.language`/`currentversionid`,
`elang_version`, `elang_cue`, `elang_gap`) war zum Zeitpunkt des Fehlers
mit hoher Wahrscheinlichkeit bereits real in der Datenbank angelegt, da DDL
in MySQL/MariaDB pro Anweisung sofort committet und durch eine PHP-Exception
nicht zurückgerollt werden kann. Da JEDE Tabellen-/Feld-Erstellung in
`xmldb_elang_upgrade()` von Anfang an mit `table_exists()`/`field_exists()`-
Prüfungen abgesichert war, sollte ein erneuter Lauf sauber dort
weitermachen, wo der erste abgebrochen ist — keine manuelle Bereinigung
nötig.

**Lektion in `sessionstart.txt` festgehalten:** install.xml und upgrade.php
künftig IMMER gemeinsam auf KEY-Feld == INDEX-Feld-Kollisionen prüfen, nicht
erst wenn ein echtes Upgrade das findet.

---

## Schritt 7: Gradebook-Anbindung — Grundfunktion

### Was wurde erledigt?

- [x] `db/install.xml`/`db/upgrade.php` (Savepoint 2026072306): neues Feld
      `elang.grade` (Standard-Moodle-Konvention: positiv = Punktzahl, 0 =
      ungewertet, negativ = `-scaleid`). Kein Key/Index auf diesem Feld —
      explizit gegen dieselbe Kollisionsart geprüft, die den alpha.6-
      Upgrade-Pfad zerschossen hatte (Schritt 6). Keine gefunden.
- [x] `lib.php`: `FEATURE_GRADE_HAS_GRADE` jetzt `true`.
      `elang_grade_item_update()` (ruft `grade_update()` auf) und
      `elang_update_grades()` (liest `attempt_manager::get_best_score()`,
      schreibt skaliert auf `elang.grade` in die Bewertung) neu.
      `elang_add_instance()`/`elang_update_instance()` rufen jetzt
      `elang_grade_item_update()` auf; `elang_delete_instance()` räumt das
      Grade-Item über `grade_update(..., ['deleted' => true])` ab.
- [x] `classes/local/domain/attempt_manager.php::get_best_score()`: höchste
      Punktzahl unter den ABGESCHLOSSENEN Versuchen einer Person.
- [x] `classes/external/finish_attempt.php`: ruft nach `finish_attempt()`
      sofort `elang_update_grades()` für die betreffende Person auf.
- [x] `mod_form.php`: `standard_grading_coursemodule_elements()` ergänzt.
- [x] Tests: `tests/lib_test.php` um vier Gradebook-Fälle erweitert,
      `attempt_manager_test.php` um `get_best_score()`-Fälle.
- [x] `version.php`: 2026072305 → 2026072306 (2.0.0-alpha.7).
- [x] `php -l`/`phpcs`: 0/0. `phpcpd`: „No clones found". Erneute
      KEY/INDEX-Kollisionsprüfung: keine.
- [x] Blueprint Kap. 10.6, `Blueprint_kompakt.md`, `CHANGELOG.md`
      aktualisiert.

---

### Entscheidungen getroffen

| Thema | Entscheidung | Begründung |
|---|---|---|
| Wertungsmethode über mehrere Versuche | fest „höchster abgeschlossener Versuch", nicht konfigurierbar | einfacher, korrekter erster Schritt; konfigurierbare Methoden (bester/letzter/durchschnittlicher, nach `mod_quiz`-Vorbild) als dokumentierte spätere Verfeinerung, keine Auslassung aus Versehen |
| `get_best_score()` lebt in `attempt_manager`, nicht in `lib.php` | reine Abfrage über `elang_attempt`, dieselbe Zuständigkeit wie der Rest der Klasse | unabhängig von einem Gradebook-Bootstrap testbar, konsistent mit dem Rest der Domänenschicht |
| Bewertung wird sofort nach `finish_attempt()` angestoßen | `classes/external/finish_attempt.php` ruft `elang_update_grades()` direkt auf | Gradebook zeigt das Ergebnis unmittelbar, nicht erst bei einem späteren Regrade-Lauf |
| `lib.php` in `finish_attempt.php` explizit `require_once`t | Gradebook-Callbacks sind einfache globale Funktionen, im External-Function-Kontext nicht garantiert bereits geladen | sichere, explizite Abhängigkeit statt Annahme über Ladereihenfolge |

---

### Entwurfsentscheidungen geändert / zurückgestellt

Keine Änderung an bestehenden Festlegungen.

---

### Wichtiger Fund: dieselbe DB-Rückgabewert-Fallenklasse wie zuvor, diesmal selbst gefunden

`SELECT MAX(score) FROM elang_attempt WHERE ...` liefert bei null Treffern in
SQL **immer genau eine Zeile mit `NULL`**, nie „keine Zeile" — dieselbe Art
von impliziter DB-Rückgabewert-Annahme, die in Schritt 2/3 bereits zu realen
int-vs-string-Funden geführt hatte. Diesmal explizit geprüft statt vermutet:
die eigenständige Fake-DB im Smoke-Test wurde gezielt erweitert, um dieses
Verhalten nachzubilden (vorher hätte sie den generischen `FROM
{elang_attempt}`-Zweig getroffen und ein falsches Ergebnis geliefert), mit
vier neuen, unabhängig bestandenen Prüfungen (keine Versuche, laufender
Versuch zählt nicht, erster abgeschlossener Versuch zählt, höchster von
mehreren gewinnt gegen den letzten).

---

### Offene Punkte nach diesem Schritt

- [ ] Completion (`FEATURE_COMPLETION_HAS_RULES`,
      `\mod_elang\completion\custom_completion`) — letzter Baustein, bevor
      der aktiv entwickelbare Umfang von Phase 2 vollständig ist
- [ ] `elang.answermaxlength`
- [ ] `classes/courseformat/overview.php`
- [ ] Migration V1 → V2 — weiterhin separat blockiert (V1-Datensimulator)

---

### Testlauf-Ergebnis

```
PHPUnit: nicht gegen echte Moodle-Instanz ausgeführt. Eigenständiger
         Smoke-Test um vier get_best_score()-Prüfungen erweitert, alle
         bestanden (34 -> 38 Prüfungen insgesamt im Domain-Smoke-Test)
PHPCS:   0 Fehler, 0 Warnungen
PHPCPD:  No clones found
PHP-Lint: fehlerfrei
KEY/INDEX-Kollisionsprüfung: keine (neues Feld hat weder Key noch Index)
```

Der neue `upgrade.php`-Savepoint ist im Besonderen noch nicht gegen ein
echtes Admin-UI-Upgrade gelaufen — nach der Schritt-6-Lektion ausdrücklich
als offener Punkt vermerkt, nicht stillschweigend vorausgesetzt.

---

## Schritt 8: Completion — letzter Baustein von Phase 2

### Was wurde erledigt?

- [x] `classes/completion/custom_completion.php`: implementiert
      `\core_completion\activity_custom_completion` mit genau einer eigenen
      Regel, `completionfinishattempt`. `completionview` und eine
      Bestehensnote-Bedingung liefert Moodle-Core bereits automatisch, sobald
      `FEATURE_COMPLETION_TRACKS_VIEWS` bzw. `FEATURE_GRADE_HAS_GRADE`
      gesetzt sind — nicht selbst implementiert.
- [x] `lib.php`: `FEATURE_COMPLETION_HAS_RULES` jetzt `true`.
- [x] `mod_form.php`: `add_completion_rules()`/`completion_rule_enabled()` —
      Feldname trägt `$this->get_suffix()` (seit Moodle 4.3/4.4, MDL-78516,
      Pflicht für Mehrfach-Instanzen-Formulare).
- [x] Zwei neue Sprachstrings (EN+DE): `completionfinishattempt`,
      `completiondetail:completionfinishattempt`.
- [x] Tests: `tests/completion/custom_completion_test.php` neu, `lib_test.php`
      korrigiert.
- [x] `version.php`: 2026072306 → 2026072307 (2.0.0-alpha.8).
- [x] `php -l`/`phpcs`: 0/0. `phpcpd`: „No clones found".
- [x] Blueprint Kap. 10.5, `Blueprint_kompakt.md`, `CHANGELOG.md`
      aktualisiert.

---

### Entscheidungen getroffen

| Thema | Entscheidung | Begründung |
|---|---|---|
| Eigene Completion-Regel | genau eine: `completionfinishattempt` | `completionview` und eine Bestehensnote-Bedingung liefert Moodle-Core bereits automatisch für Aktivitäten mit `FEATURE_COMPLETION_TRACKS_VIEWS`/`FEATURE_GRADE_HAS_GRADE` — die einzige echte Lücke, die Core nicht kennt, ist „hat die Person tatsächlich einen Versuch abgeschlossen" |
| Formularfeld mit Suffix | `$this->get_suffix()` immer angehängt, keine Versionsverzweigung | seit Moodle 4.3/4.4 (MDL-78516) Pflicht für Mehrfach-Instanzen-Formulare; unser gesamter Zielbereich (ab 4.5 LTS) liegt bereits jenseits dieser Grenze — anders als ein während der Recherche gefundenes Drittanbieter-Plugin, das noch ältere Moodle-Versionen unterstützen musste und deshalb eine Verzweigung brauchte, brauchen wir keine |
| Kein `elang_get_completion_state()` | bewusst nicht implementiert | Moodle-Core hat diesen Legacy-Callback-Pfad zur Deprecation vorgesehen (MDL-71144) zugunsten des klassenbasierten Ansatzes; als Neuentwicklung ohne Altlast gibt es nichts fortzuführen |

---

### Entwurfsentscheidungen geändert / zurückgestellt

Keine Änderung an bestehenden Festlegungen.

---

### Ein Rechercheergebnis, das einen echten Fehler verhindert hat

Vor dem Schreiben von `mod_form.php::add_completion_rules()` gezielt nach dem
AKTUELLEN, versionsabhängigen Muster gesucht statt aus dem Gedächtnis zu
schreiben — und dabei einen echten Community-Bugreport gefunden: ein anderes
Plugin brach auf Moodle 4.4 genau deshalb, weil sein Completion-Regel-
Formularfeld KEIN `$this->get_suffix()` trug (Pflicht seit MDL-78516, für
Mehrfach-Instanzen-Formulare wie Sammel-Abschluss-Bearbeitung). Ohne diese
gezielte Recherche wäre genau dieser Fehler naheliegend gewesen, da ältere
Tutorials und Beispielcode oft noch die unsuffixierte Variante zeigen.

Zusätzlich am Moodle-Core-Quellcode direkt bestätigt statt vermutet: welchen
exakten Exception-Typ `validate_rule()` bei einer undefinierten
Completion-Regel wirft (`coding_exception`, nicht `moodle_exception` — auch
wenn Letzteres über die Vererbungshierarchie technisch ebenfalls bestanden
hätte).

---

### Offene Punkte nach diesem Schritt

**Damit ist der aktiv entwickelbare Umfang von Phase 2 vollständig.**
Migration V1 → V2 bleibt separat blockiert und zählt nicht dagegen.

- [ ] `elang.answermaxlength`
- [ ] `classes/courseformat/overview.php`
- [ ] Migration V1 → V2 — weiterhin separat blockiert (V1-Datensimulator,
      braucht Eingabe vom Nutzer)
- [ ] Phase 3 (Lernendenoberfläche): Lese- und Schreib-API sind vollständig,
      ein Player kann jetzt gebaut werden

---

### Testlauf-Ergebnis

```
PHPUnit: nicht gegen echte Moodle-Instanz ausgeführt. Completion-
         Zustandsberechnung und die mod_form-Oberfläche brauchen Moodles
         echte Completion-/Formular-Maschinerie und ließen sich nicht
         eigenständig per Smoke-Test prüfen — anders als die Domänenschicht
         in den vorherigen Schritten.
PHPCS:   0 Fehler, 0 Warnungen
PHPCPD:  No clones found
PHP-Lint: fehlerfrei
```

---

### Nachtrag (erster echter Testlauf): drei Testfehler, alle im eigenen neuen Testaufbau

118 Tests, nur 3 Fehler — alle drei in `custom_completion_test`, alle
dieselbe Ursache, **ohne Versionsbump** behoben.

**Root Cause (kein Fehler in `custom_completion.php`):** `validate_rule()`
(Moodle-Core, `core_completion\activity_custom_completion`) prüft zwei
getrennte Dinge — ist die Regel überhaupt vom Plugin definiert (bestand)
UND ist sie für GENAU DIESE Kursmodul-Instanz aktiviert (schlug fehl). Mein
Test-Setup hatte das Modul ohne jede Completion-Konfiguration angelegt —
`get_state()` schlägt dann unabhängig von der eigenen Logik immer fehl.
Fix: Kurs mit `enablecompletion => 1`, Modul mit `completion =>
COMPLETION_TRACKING_AUTOMATIC` und dem unsuffixierten Regelnamen
`completionfinishattempt => 1` direkt an `create_module()` übergeben —
passend zum Muster, das Moodle-Cores eigene Completion-Tests verwenden
(gegen `completion/tests/bulk_update_test.php` bestätigt).

Lektion in `sessionstart.txt` festgehalten für künftige Custom-Completion-
Tests.

---

### Nachtrag 2 (zweiter echter Testlauf): erste Diagnose war unvollständig — echtes fehlendes Produktivteil gefunden

Derselbe Fehler trat nach dem ersten Fix erneut auf, unverändert. Die
Diagnose aus Nachtrag 1 war zwar RICHTIG, aber UNVOLLSTÄNDIG: Es fehlte
nicht nur die Testkonfiguration, sondern ein ECHTES Produktivteil, das
Schritt 8 von Anfang an gefehlt hatte.

**Tatsächliche Root Cause:** Die zweite `validate_rule()`-Prüfung liest
`cm_info->customdata['customcompletionrules']` — das wird AUSSCHLIESSLICH
durch einen `{modname}_get_coursemodule_info($coursemodule)`-Callback in
`lib.php` befüllt (Vorbild: `forum_get_coursemodule_info()`, liest
`forum.completiondiscussions` etc. aus der EIGENEN Tabelle der Aktivität).
Dieser Callback existierte in `mod_elang` gar nicht, und die dafür nötige
Spalte (`elang.completionfinishattempt`) auch nicht. Ohne beides schlägt
`get_state()` IMMER fehl, unabhängig von Testaufbau oder eigener Logik —
der erste Fix (nur `create_module()`-Optionen) hatte also nichts, wohin er
seinen Wert hätte schreiben oder woraus er ihn hätte zurücklesen können.

**Fix (weiterhin ohne Versionsbump, 2.0.0-alpha.8 bleibt):**
- Neue Spalte `elang.completionfinishattempt` in `db/install.xml`.
- Neuer `db/upgrade.php`-Savepoint, der auf den BEREITS AKTUELLEN Wert
  2026072307 zielt (kein weiterer Bump, aber real wirksam beim nächsten
  Admin-UI-Upgrade einer echten Installation, deren gespeicherte Version
  noch dahinter liegt).
- `lib.php::elang_get_coursemodule_info()` neu — befüllt
  `customdata['customcompletionrules']['completionfinishattempt']` NUR,
  wenn `$coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC`
  (Core-eigene Konvention).
- Zwei neue, eigenständige Tests in `tests/lib_test.php` direkt für den
  Callback, unabhängig vom Completion-State-Test, der die Lücke ursprünglich
  aufgedeckt hatte.

Mit Spalte und Callback vorhanden griff die ursprüngliche
`create_module()`-Konfiguration aus Nachtrag 1 tatsächlich korrekt — sie
hatte nur bis jetzt nichts, worauf sie wirken konnte.

Lektion in `sessionstart.txt` korrigiert und vervollständigt (nicht nur
ergänzt), damit sie beim nächsten Mal von Anfang an vollständig ist.

---

### Nachtrag 3 (dritter echter Testlauf): Fix aus Nachtrag 2 bestätigt korrekt — nur zwei eigene Testfehler übrig

`custom_completion_test` lief diesmal vollständig durch — der Schema-/
Callback-Fix aus Nachtrag 2 war tatsächlich richtig. Die einzigen zwei
verbleibenden Fehler steckten in den zwei NEUEN Tests für den Callback
selbst, beide reine Testfehler:

- Ein Test hatte gar kein `$this->resetAfterTest()` — löste Moodles
  „unexpected database modification"-Schutz aus.
- Der andere prüfte mit `assertArrayNotHasKey()` gegen `$info->customdata`,
  das bei nicht-automatischer Completion `null` bleibt (nie ein Array),
  da `elang_get_coursemodule_info()` es nur im automatischen Zweig anfasst
  — `assertArrayNotHasKey()` verlangt zwingend ein Array und wirft sonst;
  auf `assertTrue(empty(...))` umgestellt.

Vor dem Fix zusätzlich projektweit auf dasselbe Muster geprüft (nicht nur
die gemeldete Stelle) — mehrere vermeintliche Treffer waren Fehlalarme des
eigenen, zu groben Prüfskripts (verwechselte `setUpBeforeClass()` mit
`setUp()`) bzw. korrekt `resetAfterTest()`-freie `\basic_testcase`-Klassen
ohne DB-Zugriff.

Kein Versionsbump — reine Testkorrektur.

