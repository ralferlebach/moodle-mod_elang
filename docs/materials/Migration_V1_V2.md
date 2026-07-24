# Migration V1 → V2 und Restore von Altsicherungen

**Stand:** 23. Juli 2026 · gehört zu `Lastenheft_Pflichtenheft_Blueprint.md`
(Anforderungen L-F13, L-F14, Pflichtenheft P9).

---

## 1. Ausgangslage

`db/upgrade.php` in Version 1 enthält keine realen Upgrade-Schritte. Gleichzeitig
gilt:

- Sites können mehrere Plugin-Releases übersprungen haben; die gespeicherte
  `version`-Nummer ist deshalb kein verlässlicher Anker.
- Der Sprung von Moodle 3.4 auf 4.5 erfolgt bei realen Installationen ohnehin über
  mehrere Moodle-Zwischenversionen. Das Plugin muss den Zustand vorfinden, nicht
  vorhersagen.
- Die V1-Daten liegen überwiegend als JSON-Text in `elang_cues.json`,
  `elang_users.json` und `elang.options` vor.

---

## 1.1 Offener Punkt: keine echten V1-Testdaten verfügbar

**Stand 23. Juli 2026:** Für die Entwicklung und für automatisierte Migrationstests
stehen derzeit **keine echten V1-Bestandsdaten** zur Verfügung — auch nicht mehr
beim Auftraggeber selbst. Das betrifft sowohl reale `elang_cues.json`/
`elang_users.json`-Inhalte als auch reale `.mbz`-Sicherungen für den
Restore-Pfad (Kap. 5).

**Update 24. Juli 2026:** Der Auftraggeber hat einen kleinen, schnell erzeugten
Datensatz nachgereicht (`mdl_elang*`-Dumps einer Einzelaktivität mit neun Cues
sowie eine dazugehörige `example.srt`) — ausdrücklich **nicht** aus einem
produktiven System, sondern eigens dafür erzeugt. Das ändert die Einschätzung
nur teilweise:

- **Reicht für:** Struktur- und Feldsemantik-Klärung, eine erste reale
  Golden-Master-Fixture, Verifikation der Abbildungsregeln in Kap. 3 gegen
  tatsächliche V1-Ausgabe (mehrere Korrekturen unten direkt daraus abgeleitet).
- **Reicht NICHT für:** Mengengerüste/Lasttests, Mehrsprachigkeit, mehrere
  Lernende mit unterschiedlichen Completion-Zuständen, verwaiste
  Antwortdatensätze in realistischer Häufigkeit, den Restore-Pfad (keine
  `.mbz`-Sicherung enthalten). Der V1-Datensimulator (unten) bleibt für diese
  Breite notwendig.

**Konsequenz für diesen Entwicklungsstand:** Die Migrationslogik (Kap. 2–4)
bleibt größtenteils Spezifikation, bis sie gegen echte oder zumindest
realistische Daten geprüft werden kann. Referenzfälle für den
`answer_evaluator` (siehe `Lastenheft_Pflichtenheft_Blueprint.md`, Kap. 10)
wurden unabhängig davon bereits festgeschrieben, weil sie sich aus der
fachlichen Spezifikation und nicht aus V1-Bestandsdaten ableiten ließen.

---

## 1.2 V1-Quellcode verfügbar und ausgewertet (24. Juli 2026)

Der Auftraggeber hat den tatsächlichen V1-Quellcode (`mod_elang`, Release
2018091012) nachgereicht. Damit lassen sich mehrere zuvor offene oder nur aus
Beispieldaten erschlossene Punkte jetzt direkt am Code verifizieren. Zentrale
Funde, mit Fundstelle:

- **`jaroDistance` ist exakt der 0..1-Schwellwert, den `elang.jarothreshold`
  bereits abbildet** (`server.php:339`: `jaro($parsedtext, $parsedcontent) >=
  $options['jaroDistance']`; Formularvalidierung `mod_form.php:551-554`
  verlangt `0 < jaroDistance <= 1`; Default `1`, wenn nicht gesetzt —
  `locallib.php:60`). Kap. 3 unten entsprechend als verifiziert markiert.
  **Eine Nebenerkenntnis:** V1s eigene `jaro()`-Funktion (`locallib.php:470`)
  verwendet ein Vergleichsfenster von `max(len1,len2) - 1` statt des in der
  Fachliteratur üblichen `floor(max(len1,len2)/2) - 1`, das
  `answer_evaluator::jaro_similarity()` implementiert. Für dieselbe
  Zeichenkette liefern beide Funktionen deshalb nicht notwendigerweise
  denselben Zahlenwert — bei einem Schwellwert von exakt `1` (nur Identität
  akzeptiert) ist das irrelevant, bei einem abgesenkten Schwellwert weicht das
  Verhalten migrierter Aktivitäten von V1 graduell ab. Das ist mit der
  bestehenden Leitentscheidung „kompatibel = nachvollziehbar, nicht
  bitidentisch" (Blueprint Kap. 2.3) vereinbar und wird nicht nachgebaut.
- **`gradingalgorithm` ist eine AKTIVITÄTSWEITE, nicht pro Lücke
  konfigurierbare Eigenschaft in V1** (`server.php:315-340`): Groß-/
  Kleinschreibung, Transliteration und Jaro-Toleranz werden für JEDE Lücke der
  Aktivität gleich angewendet, mit ODER verknüpft. Damit ist Frage 1 (Zuordnung
  `gradingalgorithm`) beantwortbar: Hat die Aktivität irgendeine Form von
  Toleranz aktiv (`usecasesensitive = false` ODER `usetransliteration = true`
  ODER `jaroDistance < 1`), erhalten ALLE ihre Lücken `wordrecognized` (das in
  V2 ohnehin unbedingt Groß-/Kleinschreibung und Diakritika normalisiert) mit
  `jarothreshold = jaroDistance`; hat sie keinerlei Toleranz aktiv
  (`usecasesensitive = true`, `usetransliteration = false`, `jaroDistance = 1`),
  erhalten alle ihre Lücken `exact`. Das ist jetzt Option A aus der vorherigen
  Rückfrage, am Code bestätigt statt vermutet.
- **Keine Hilfestufen, keine Bestrafung — V1 hat gar kein Gradebook.**
  `lib.php` enthält weder `elang_grade_item_update()` noch
  `elang_update_grades()`; die einzige Auswertung ist
  `elang_get_completion_state()` (Abschluss, siehe unten). „Hilfe" in V1
  (`server.php:413-499`) deckt die komplette Musterlösung auf (kein
  abgestufter Hinweis) und ist je Lücke genau einmal nutzbar (danach mit
  HTTP 400 gesperrt). Frage 3 ist damit beantwortet: Option B — es gab keine
  Bestrafung, weil es keine Punktzahl gab, die hätte bestraft werden können.
- **`tries` existiert in V1 nirgends, auch nicht in `elang_users`.**
  Zur Präzisierung von Frage 4: `elang_users` (UNIQUE-Index auf
  `id_cue, id_user`, `db/install.xml:62`) hält genau eine Zeile je (Cue,
  Person) und wird bei jedem Check/jeder Hilfe-Anfrage überschrieben
  (`server.php:388-403`, `:490-501`) — das ist der **aktuelle Stand**
  (zuletzt eingegebener Text, ob Hilfe benutzt wurde), keine Versuchshistorie
  und kein Zähler. `elang_check` wird bei JEDEM Check-Aufruf neu eingefügt
  (richtig oder falsch, `info` = Musterlösung, `user` = tatsächliche Eingabe —
  bei richtiger Antwort sind beide identisch, weil `server.php:335` den
  gespeicherten Text bei Erfolg durch den kanonischen Lösungstext ersetzt),
  aber ohne `id_user` bleibt es unmöglich, diese Zeilen einer Person
  zuzuordnen. **Ergebnis:** Es gibt keine V1-Quelle für `elang_response.tries`
  — weder in `elang_users` noch in `elang_check`. Migrierte Antworten erhalten
  `tries = 1` (Option A/B aus der vorherigen Rückfrage waren im Ergebnis
  identisch; `elang_check` fließt höchstens als aggregierte Bericht-Kennzahl
  ein, wie in Kap. 3 bereits vermerkt).
- **Der Gap-Zähler-Bug betrifft NICHT `elang_cues.number`, wie zuvor
  ungenau beschrieben — Korrektur.** `locallib.php:342` (`foreach ($cues as
  $i => $elt)`) weist `$cue->number = $i + 1` VOR der inneren Schleife zu
  (`:356`), mit dem frischen, von `foreach` bei jeder Iteration neu aus dem
  Array-Schlüssel gesetzten `$i` — `number` ist dadurch nachweisbar **immer**
  korrekt und niemals vom Bug betroffen (löst Frage 5 endgültig, ohne
  weitere Beispieldaten). Betroffen ist ausschließlich das per-Lücke
  `order`-Feld: `locallib.php:381` verändert dasselbe `$i` innerhalb der
  Lücken-Verarbeitung (`'order' => $i++`) — diese Veränderung überlebt aber
  nicht bis zur nächsten Cue-Iteration, weil `foreach` `$i` dort ohnehin neu
  aus dem Array-Schlüssel setzt. Bei einem Cue mit mehreren Lücken „läuft"
  `$i` deshalb innerhalb dieses einen Cues voraus, wird aber beim nächsten Cue
  auf dessen wahren Index zurückgesetzt — genau das erzeugt die am realen
  Beispiel beobachteten Duplikate/Lücken in `order` (Kap. 3.1), ohne `number`
  je zu berühren. `Lastenheft_Pflichtenheft_Blueprint.md` und die technische
  Review hatten diesen Mechanismus unpräzise beschrieben; hiermit korrigiert.
- **Abschlussformel vollständig rekonstruiert** (`lib.php:246-330`), als
  Vorlage für die noch zu bauenden V2-Regeln `completionansweredpercent`/
  `completioncorrectpercent` (Blueprint Kap. 10.5, bisher „was noch fehlt"):
  über alle `input`-Elemente aller Cues gezählt, `completion_gapfilled` prüft
  `(erfolgreich + Hilfe genutzt + falsch) / gesamt * 100 >= Schwellwert`
  („irgendwie bearbeitet"), `completion_gapcompleted` prüft `erfolgreich /
  gesamt * 100 >= Schwellwert` (Hilfe-aufgedeckte Lücken zählen hier
  ausdrücklich NICHT als erfolgreich). Wichtiger Unterschied zum aktuellen
  V2-Verhalten: `attempt_manager::submit_response()`/`request_hint()` zählen
  eine Lücke nur dann zu `answeredgaps`, wenn tatsächlich Text abgegeben
  wurde (`resultstate !== EMPTY`) — eine angeforderte, aber nie beantwortete
  Hilfe zählt in V2 aktuell NICHT zu `answeredgaps`, in V1 zählte ein reiner
  Hilfe-Abruf zu `completion_gapfilled` aber sehr wohl mit. Diese Abweichung
  ist beim Bau der neuen Completion-Regeln explizit zu entscheiden, nicht
  stillschweigend zu übernehmen oder zu verwerfen.
- **Frage 9 (unbekannte Optionsfelder) vollständig geklärt:**
  `repeatedunderscore` — Länge der Unterstrich-Lücken im Arbeitsblattexport
  (`lib.php:618-632`); `titlelength` — maximale Länge des maskierten
  `title`-Felds, danach mit „…" gekürzt (`locallib.php:348-352`); `limit` —
  Seitengröße für die Cue-Liste im Player (`server.php:254-266`, „Listing
  limit"), fachlich identisch zum `$limit`-Parameter von V2s eigenem
  `get_cues`; `left`/`top`/`size` — Randabstände und Schriftgröße des
  PDF-Arbeitsblattexports (TCPDF, `lib.php:646-651`), keine Player-Layout-
  Einstellungen wie zunächst vermutet.
- **Zitate aus der technischen Review bestätigt, nicht revidiert:** der
  `/mod/book/index.php`-Fehler in
  `backup/moodle2/backup_elang_activity_task.class.php` und der
  `id?id=`-Fehler in `classes/event/report_viewed.php` sind exakt wie
  ursprünglich zitiert im Quellcode vorhanden.


**Update 24. Juli 2026 — erste, bewusst eng geschnittene Ausnahme:** Der
Baustein „Cue-JSON in Transkript und Lücken zerlegen" (Zeichenposition,
Musterlösung, Link, `[]`/`{}` → Hilfe erlaubt/nicht erlaubt) hängt an keiner
der noch offenen Fragen (Bewertungsalgorithmus-Zuordnung, `jarothreshold`-
Semantik, Hilfestufen/Penalty, `tries`-Rekonstruktion) und ist deshalb bereits
implementiert und mit echten Golden-Master-Tests gegen alle neun Cues der
nachgereichten Beispielaktivität abgesichert:

```text
classes/local/migration/v1_cue_parser.php                     Parser
tests/fixtures/v1_legacy_schema.php                            V1-Schema + echte Beispieldaten
tests/local/migration/v1_cue_parser_test.php                   Golden-Master je Cue-Form (literales JSON)
tests/local/migration/v1_sample_activity_golden_master_test.php  dieselben Fälle end-to-end über echte DB-Zeilen
```

Alles darüber hinaus — Aktivitäts-Optionen-Mapping, Attempt-/Response-
Rekonstruktion, Hilfestufen, Legacy-Tabellen-Erkennung, Ad-hoc-Task,
Adminseite/CLI — bleibt Spezifikation wie zuvor.

**Update 24.07.2026 — V1-Datensimulator implementiert.**
`tests/fixtures/v1_data_simulator.php` erzeugt synthetische, aber strukturell
realistische V1-Bestände, parametrisiert (Anzahl Aktivitäten, Cues je Aktivität
als Bereich, Lernende je Aktivität, Zufalls-Seed für reproduzierbare Läufe):

- Schema exakt nach dem jetzt vorliegenden `moodle-mod_elang` 1.x
  `db/install.xml` (`v1_legacy_schema.php`, Kap. 1.2);
- der fehlerhafte Gap-Zähler wird nicht nachträglich injiziert, sondern durch
  Nachbau des exakt gleichen fehleranfälligen Mechanismus (`locallib.php:342/
  381`, korrigiert verstanden seit Kap. 1.2) reproduziert — entsteht deshalb
  genau dann und nur dann, wenn er auch in echten V1-Daten entstünde;
- gezielt zuschaltbare Grenzfälle (`injectedgecases`-Option): sehr langer
  Antworttext, verwaister `elang_users`-Datensatz (Cue-ID ohne passende
  `elang_cues`-Zeile — der reale Mechanismus dahinter, `locallib.php:334-335`,
  ist jetzt bekannt: jedes Speichern löscht und legt Cues neu an), ungültige
  `link`-URL (`javascript:`-Schema), unberührter Datensatz mit leerem Inhalt;
- Ausgabe direkt über `$DB` in eine laufende PHPUnit-Testumgebung — die in
  Kap. 1.1 ursprünglich vorgesehene zweite Ausgabeform (eigenständige
  Mini-Moodle-Datenbank für einen isolierten Restore-Test außerhalb von
  PHPUnit) ist **nicht** umgesetzt, siehe Kap. 5 zum aktuellen Stand des
  Restore-Pfads.
- Getestet in `tests/local/migration/v1_data_simulator_test.php`: reproduzierbare
  Läufe bei gleichem Seed, eingehaltene Eindeutigkeit von `(id_cue, id_user)`
  (echter V1-UNIQUE-Index), nachweisbare Zähler-Kollisionen bei mehrgap-reichen
  Aktivitäten, `number` bleibt auch im großen Lauf immer sauber, alle vier
  Grenzfälle nachweisbar vorhanden bzw. bei Bedarf abschaltbar.

**Update 24.07.2026 — Schritt 3 (Migration) implementiert.**
`classes/local/migration/v1_migrator.php` migriert **eine** Aktivität
transaktional (das ist die Verarbeitungseinheit, die ein wiederaufnehmbarer
Ad-hoc-Task später in einer Schleife aufrufen würde — der Task selbst, mit
Blockbildung über mehrere Aktivitäten hinweg, Fortschrittsmarkern und
Terminierung, ist noch nicht gebaut):

```text
classes/local/migration/v1_options_mapper.php   Bewertungs-Mapping, geteilt mit v1_detector
classes/local/migration/v1_migrator.php          migrate_activity(int $elangid): object
tests/local/migration/v1_migrator_test.php       Golden-Master gegen die echte Beispielaktivität
```

Abgedeckt: `elang_cues.json` → `elang_cue`/`elang_gap` (über `v1_cue_parser`,
`cuekey`/`gapkey` aus den stabilen V1-IDs, nicht aus dem fehlerhaften
`order`-Zähler); `[eckige Klammer]` → genau eine `elang_gaphint`-Stufe
(Level 1, Typ `solution`, `penalty = 0`), unabhängig davon, ob eine Person
sie je genutzt hat — das ist eine Autoren-Eigenschaft der Lücke, keine
Antwort-Tatsache; `elang.options` → `gradingalgorithm`/`jarothreshold`
aktivitätsweit; `elang_users` → ein `elang_attempt` je Person
(`attemptnumber = 1`, `state = finished`) plus ein `elang_response` je
tatsächlich vorhandenem Eintrag, neu bewertet über den echten
`answer_evaluator` statt über eine eigene Nachbildung der Bewertungslogik;
`tries` immer `1`; ungültige Link-URLs werden verworfen und gemeldet, nicht
stillschweigend übernommen; verwaiste `elang_users`-Zeilen (Cue oder
Position ohne Gegenstück) werden gemeldet und übersprungen, brechen die
Migration der übrigen Aktivität nicht ab.

**Update 24.07.2026 — Ad-hoc-Task ergänzt.**
`classes/task/migrate_v1_activities_task.php` ruft `v1_migrator` blockweise
über mehrere Aktivitäten hinweg auf:

- Fortschritt wird **nicht** über eine eigene Tabelle verfolgt — die
  Erkennung selbst (`v1_detector::pending_activity_ids()`,
  `elang.currentversionid`) ist bereits der Fortschrittsmarker. Ein
  erfolgreich migrierter Aktivität wird nie wieder zurückgegeben, egal ob
  derselbe Task-Lauf, ein späterer, oder nach einem Cron-Abbruch neu
  gestartet.
- Ein `execute()`-Aufruf verarbeitet höchstens `blocksize` Aktivitäten
  (Default 20); bleiben danach weitere offen, reiht sich der Task selbst
  erneut ein.
- Der Fehlschlag einer einzelnen Aktivität (Ausnahme aus
  `migrate_activity()`) wird geloggt und übersprungen, bricht den Rest des
  Blocks nicht ab — dieselbe Grundhaltung, die `v1_migrator` schon
  innerhalb einer Aktivität für einzelne Cues/Lücken/Antworten verfolgt.
- Vier Tests, u. a. ein Fall, in dem `pending_activity_ids()` eine
  Aktivität als „ausstehend" einstuft, `migrate_activity()` sie aber
  ablehnt (fehlender `options`-Blob trotz vorhandener `elang_cues`) — genau
  die Inkonsistenz, die `pending_activity_ids()` selbst nicht prüft.

Bewusst weiterhin nicht enthalten: Adminseite/CLI, um den Task überhaupt
einzureihen, und die Verifikation (Soll-/Ist-Abgleich nach der Migration,
Schritt 4).

**Grundsatz:** Die Migration wird an der **Existenz der Legacy-Tabellen**
festgemacht, nicht an Versionsnummern.

---

## 1.3 Entscheidung: Wie `elang.options` das Zeitfenster zwischen
Schema-Upgrade und Datenmigration übersteht (24.07.2026)

**Der offene Punkt:** V2s `db/install.xml` hat kein `options`-Feld mehr
(Kap. 6.2 des Blueprints, „Warum keine JSON-Blobs"). Da V1s `elang`-Zeile in
der Realität dieselbe ist wie V2s (Moodle verlangt genau eine
Instanz-Zeile je Kursmodul, sie wird per `ALTER TABLE` erweitert, nicht
ersetzt), musste geklärt werden, wie der rohe V1-Optionen-Blob den Zeitraum
zwischen dem Schema-Upgrade (das die neuen V2-Spalten hinzufügt) und der
eigentlichen Datenmigration (die diesen Blob ausliest und verwirft) übersteht.

**Entscheidung (Option A):** `db/upgrade.php` bekommt einen eigenen Schritt
(`2026072407`), der `elang.options` als nullable Textfeld real hinzufügt —
ohne Default, ohne dass V2-Code außerhalb der Migration es je liest oder
schreibt. Umgesetzt:

- `db/install.xml`: `elang.options`, `TYPE="text"`, `NOTNULL="false"`, direkt
  nach `jarothreshold` einsortiert, mit Kommentar zur Zweckbindung.
- `db/upgrade.php`, Schritt `2026072407`: fügt das Feld nachträglich hinzu,
  idempotent (`field_exists()`-Prüfung wie bei allen anderen Schritten).
- Für JEDE bestehende Aktivität (V1 migriert oder nicht, oder genuin als V2
  angelegt) ist `options` nach diesem Schritt einfach `NULL`, bis eine
  V1-Migration sie befüllt — kein Verhaltensunterschied für rein
  V2-native Aktivitäten.
- `classes/local/migration/v1_detector.php` liest `name`/`options`/
  `language` jetzt direkt aus der echten `elang`-Zeile, genau wie es die
  eigentliche Migration später auch tun wird — keine Fiktion mehr nötig.
- `tests/fixtures/v1_legacy_schema.php` und `v1_data_simulator.php` schreiben
  seitdem ebenfalls direkt in die echte `elang`-Tabelle statt in ein
  gesondertes `elang_v1`-Konstrukt; letzteres war ohnehin nur eine
  Übergangslösung, bis dieser Punkt geklärt war (siehe Versionsgeschichte in
  `tests/fixtures/v1_legacy_schema.php`s Docblock für die Begründung, warum
  diese Übergangslösung nötig war und warum sie jetzt entfällt).

**Damit noch offen, aber nicht mehr blockierend:** Wann genau `options`
wieder aus dem Schema entfernt wird (Kap. 2, Schritt 5 „Abbau" — erst nach
verifizierter Migration, mindestens ein Release Abstand, analog zu den
Legacy-Tabellen selbst).

---

## 2. Ablauf

```text
1. Erkennung        Legacy-Tabellen vorhanden?  -> Migrationsstatus "pending"
2. Trockenlauf      Mengengerüst + Befundbericht, keine Schreiboperation
3. Migration        wiederaufnehmbarer Ad-hoc-Task, blockweise, transaktional
4. Verifikation     Soll-/Ist-Abgleich, Bericht, Freigabe durch Administration
5. Abbau            Legacy-Tabellen erst in einem SPÄTEREN Plugin-Release entfernt
```

Schritt 5 bewusst getrennt: Solange die Alttabellen bestehen, ist ein Rückweg
möglich. Der Abbau erfolgt erst, wenn eine Migration nachweislich verifiziert
wurde und mindestens ein Release dazwischen liegt.

Steuerung über eine Adminseite (Status, Trockenlauf, Start, Bericht) **und** ein
CLI-Skript für große Installationen und Wartungsfenster.

---

## 3. Abbildungsregeln

| V1 | V2 | Anmerkung |
| --- | --- | --- |
| `elang` (Grunddaten) | `elang` | unverändert übernommen |
| `elang.options` (JSON) | reguläre Spalten auf `elang` | unbekannte Schlüssel werden protokolliert, nicht verworfen |
| `elang.options.jaroDistance` | `elang.jarothreshold` | **verifiziert 24.07.2026** (Kap. 1.2): identischer 0..1-Schwellwert, `>=`-Vergleich, Default 1 — direkte Übernahme |
| — | `elang_version` (Nr. 1, `published`) | genau eine Version je migrierter Aktivität |
| `elang_cues` (ohne `json`) | `elang_cue` | `number` → `sortorder` (nur als Ausgangspunkt, siehe Altlasten unten), `begin`/`end` → `starttime`/`endtime` |
| `elang_cues.json` (Konkatenation aller `text`- und `input`-Segmente) | `elang_cue.transcript` | **korrigiert 24.07.2026:** NICHT `elang_cues.title` — das ist bereits eine mit „…" maskierte Vorschau (dieselbe Funktion wie `transcript_masker` in V2), keine Rohtranskript-Quelle. Das echte Transkript ergibt sich erst aus der geordneten Konkatenation aller Segmente in `json`. |
| `elang_cues.json` (`type:"input"`-Segmente) | `elang_gap`, `elang_gapanswer` | eckige Klammer `[...]` im ursprünglichen Untertitel → `"help":true` (Hilfe erlaubt), geschweifte Klammer `{...}` → `"help":false` (keine Hilfe) — an einer realen Beispielinstanz verifiziert, siehe Altlasten unten |
| `elang_users` (je Cue, je Person) | `elang_attempt` + `elang_response` | ein Versuch (`attemptnumber = 1`) je (Aktivität, Person); `elang_users.json` ist der EINZIGE der drei Lerndaten-Container mit `id_user` — nur er lässt sich einer Person zuordnen |
| `elang_help` | **korrigiert 24.07.2026:** aggregierter Bericht-Hinweis, NICHT `hintedgaps`/`hintlevel` | enthält weder `id_user` noch Zeitstempel (bestätigt an realer Beispielinstanz UND am Quellcode, Kap. 1.2) — kann nicht personenbezogen migriert werden. `hintedgaps` je Versuch wird stattdessen aus dem `help`-Bool in `elang_users.json` abgeleitet: `help:true` → genau eine `elang_gaphint`-Stufe (Musterlösung, verifiziert keine Bestrafung, Kap. 1.2) |
| `elang_check` | **korrigiert 24.07.2026:** aggregierter Bericht-Hinweis, NICHT `tries` | dieselbe Einschränkung wie `elang_help` — kein `id_user`. **Verifiziert (Kap. 1.2): `tries` existiert in V1 nirgends**, auch nicht in `elang_users` (hält nur den jeweils letzten Stand, keine Historie) — migrierte `elang_response.tries` wird auf `1` gesetzt, endgültig, keine weitere Quelle zu erwarten |

### 3.1 Bekannte Altlasten, die die Migration ausgleichen muss

- **Fehlerhafter Gap-Zähler — Mechanismus jetzt am Quellcode verifiziert
  (Kap. 1.2), vorherige Beschreibung hier war ungenau.** `locallib.php:342`
  (`foreach ($cues as $i => $elt)`) weist `$cue->number = $i + 1` **vor** der
  inneren Lücken-Schleife zu — `number` ist dadurch nachweisbar **immer**
  korrekt, NICHT wie zuvor vermutet abhängig von der Lückenanzahl
  vorheriger Cues. Betroffen ist ausschließlich das globale `order`-Feld je
  Lücke: `locallib.php:381` (`'order' => $i++`) verändert dasselbe `$i`
  innerhalb der Lücken-Verarbeitung, doch `foreach` setzt `$i` bei der
  nächsten Cue-Iteration ohnehin frisch aus dem Array-Schlüssel — die
  Veränderung "überlebt" nur innerhalb desselben Cues. Am nachgereichten
  Beispiel (Kap. 1.1) sichtbar: `order` läuft `0,1,2,3,4,5,5,6,(fehlt),8` —
  Cue 5 und Cue 6 kollidieren auf `order:5`, `order:7` fehlt vollständig;
  `number` blieb dabei, wie jetzt erwiesen immer, sauber `1..9`. Die
  Migration nummeriert Lücken deshalb **neu** über die Zeichenposition
  (`elang_cues.number` selbst darf dagegen direkt übernommen werden) und
  protokolliert Abweichungen; `order` wird an keiner Stelle als Identität
  übernommen.
- **Drei verschiedene Lücken-Referenzierungsschemata — neu entdeckt
  24.07.2026.** Dieselbe Lücke wird je nach Tabelle unterschiedlich adressiert:
  `elang_cues.json`/`elang_check.guess`/`elang_help.guess` referenzieren über
  den oben beschriebenen globalen (fehlerbehafteten) `order`-Zähler;
  `elang_users.json` referenziert dieselbe Lücke dagegen **pro Cue
  1-indiziert** (Schlüssel `"1"` für die jeweils erste Lücke des Cues,
  unabhängig von `order`). Cues wiederum werden von `elang_check.cue`/
  `elang_help.cue` über die fachliche `number`-Spalte referenziert, von
  `elang_users.id_cue` dagegen über den echten Primärschlüssel `id`. Die
  Migration muss diese drei Schemata sauber auseinanderhalten und darf sie
  nicht vermischen.
- **Verwaiste Antworten.** Weil jedes Speichern in V1 Cues und Antworten löschte
  und Cues neu anlegte, existieren in Beständen Antwortdatensätze ohne passenden
  Cue. Sie werden in den Bericht aufgenommen und, wenn keine Zuordnung möglich
  ist, verworfen — nie stillschweigend.
- **Freie URLs aus `[Antwort(Link)]`.** Werden beim Import validiert
  (`PARAM_URL`, nur `http`/`https` oder Moodle-interne Ziele). Nicht valide Links
  landen als Befund im Bericht, die Lücke bleibt erhalten.
- **Unbegrenzte Antworttexte.** Werden auf die neue Maximallänge gekürzt; jede
  Kürzung erscheint im Bericht.
- **Hilfestufen ohne Abstufung — verifiziert 24.07.2026 (Kap. 1.2).** V1
  kennt pro Lücke nur ein einzelnes Bool „Hilfe benutzt" (`elang_users.json`,
  Feld `help`) und deckt bei Nutzung die VOLLSTÄNDIGE Musterlösung auf
  (`server.php:413-499`), nicht einen abgestuften Hinweis; Hilfe ist je Lücke
  genau einmal nutzbar, danach mit HTTP 400 gesperrt. Eine Bestrafung gab es
  nicht — V1 besitzt gar kein Gradebook (kein `elang_grade_item_update()`/
  `elang_update_grades()` in `lib.php`), nur die prozentuale
  Abschlussberechnung (Kap. 1.2). Migrierte Lücken mit `help:true` erhalten
  deshalb genau eine `elang_gaphint`-Stufe (Typ `solution`, `penalty = 0`).

---

## 4. Nichtfunktionale Anforderungen an die Migration

- **wiederaufnehmbar** — Fortschrittsmarker je Aktivität und je Block;
- **blockweise** — Blockgröße konfigurierbar, Standard konservativ;
- **datenbankneutral** — kein herstellerspezifisches SQL, keine manuell
  zusammengesetzten `IN`-Klauseln;
- **speicherschonend** — Recordsets statt Vollladung;
- **transaktional je Block** — `start_delegated_transaction()`;
- **testbar** — PHPUnit-Fixtures mit realitätsnahen V1-JSON-Strukturen,
  einschließlich der oben genannten Altlasten;
- **diagnostizierbar** — jeder Abbruch nennt Aktivität, Block und Ursache.

---

## 5. Restore von V1-Sicherungen

Unabhängig von der Datenmigration muss der Restore einer `.mbz`-Datei aus einer
V1-Installation in eine V2-Installation funktionieren (L-F13).

**Update 24.07.2026 — echte `.mbz`-Sicherung erhalten und ausgewertet.** Der
Auftraggeber hat eine reale V1-Sicherung derselben Beispielaktivität (Kap. 1.1)
nachgereicht. Struktur jetzt bekannt statt vermutet:

- **`elang_help` und `elang_check` werden von V1 strukturell NIE gesichert.**
  `backup_elang_stepslib.php::define_structure()` verdrahtet ausschließlich
  `elang → cues → cue → users → user`; für Hilfe-/Check-Log gibt es keinen
  `backup_nested_element` und keine `set_source_table()`-Zuordnung. Das ist
  keine Lücke in dieser einen Sicherung, sondern strukturell in jeder
  V1-`.mbz`-Datei so — der Restore-Pfad muss `elang_help`/`elang_check`
  deshalb nie behandeln, nur der Live-Migrationspfad (direkter DB-Zugriff)
  kennt diese Tabellen überhaupt.
- **Erkennungsmerkmal für „das ist eine V1-Aktivität"**: `<activity
  modulename="elang">` mit einem `<elang>`-Wurzelelement, das `<options>`
  (JSON-Text) und `<language>` als direkte Felder trägt, verschachtelte
  `<cues><cue>…<users><user>…` enthält, aber **kein** `<currentversionid>`,
  `<jarothreshold>` oder `<completionfinishattempt>` — diese Felder existieren
  nur im V2-Schema. Reicht als eindeutiges, robustes Unterscheidungsmerkmal,
  ohne auf eine Versionsnummer angewiesen zu sein (V2 hat ohnehin noch keinen
  eigenen Backup/Restore, `FEATURE_BACKUP_MOODLE2` ist aktuell bewusst
  deaktiviert).
- **ID-Remapping ist Standard-Moodle-Restore-Muster, nichts Elang-Spezifisches**
  (`restore_elang_stepslib.php`): `elang_cue`-Alt-ID über `set_mapping()`
  gemerkt, `id_user` über `get_mappingid('user', …)` auf die Ziel-Site
  umgeschrieben. Die V2-Restore-Klasse kann exakt demselben Muster folgen,
  muss aber zusätzlich pro `<cue>`/`<user>`-Element durch dieselbe
  `v1_cue_parser`/Abbildungslogik wie die Live-Migration laufen, statt Felder
  1:1 in die (andere) V2-Zieltabellenstruktur zu kopieren.
- `grades.xml`/`completion.xml` der Beispielsicherung sind leer bzw. tragen
  keine Bewertungsdaten — konsistent mit dem in Kap. 1.2 bestätigten Befund,
  dass V1 kein Gradebook kennt.
- Die Restore-Klasse erkennt die alte Struktur an den oben genannten
  Elementnamen der Backup-XML und überführt sie über **dieselben
  Abbildungsregeln** wie die Datenmigration (Kap. 3).
- Der V1-Fehler, bei dem `backup_elang_activity_task.class.php:85` fälschlich
  `/mod/book/index.php` kodiert, wird beim Restore toleriert und korrigiert
  — **jetzt am Quellcode bestätigt** (Kap. 1.2), nicht mehr nur zitiert.
- Ebenso werden die fehlerhaften Legacy-URLs aus
  `classes/event/report_viewed.php:121-139` (`id?id=`) ignoriert — **ebenfalls
  bestätigt**.
- Abnahmetest: Restore einer echten V1-Sicherung erzeugt eine funktionsfähige
  V2-Aktivität mit vollständigen Cues, Lücken und — sofern in der Sicherung
  enthalten — Lerndaten. Die vorliegende Beispielsicherung eignet sich als
  erster, realer Testfall dafür, auch wenn sie inhaltlich dieselbe kleine
  Aktivität wie Kap. 1.1 ist, nicht neue Diversität liefert.

**Hinweis zur Ablage:** Die vollständige `.mbz`-Datei (61 MB, überwiegend
Inhalt anderer Aktivitäten desselben Kurses) wurde bewusst **nicht** ins
Repository übernommen — genau das Gewicht-Problem, das P0-10 der technischen
Review schon für `.git` bemängelt hatte, gilt für eine binäre Testsicherung
erst recht. Übernommen wurde nur der elang-relevante Ausschnitt
(`activities/elang_2/elang.xml`, ca. 3 KB) als
`tests/fixtures/v1_sample_backup/elang.xml` — nützlich als Referenz für die
oben dokumentierte Backup-XML-Struktur, aber **kein** Ersatz für einen
echten Restore-Integrationstest. Ein solcher Test bräuchte die vollständige
`.mbz` weiterhin außerhalb des Repositories (z. B. erneut vom Auftraggeber
bereitgestellt, wenn der Restore-Pfad tatsächlich gebaut wird).

---

## 6. Was ausdrücklich **nicht** migriert wird

- die V1-Auszeichnungssyntax als Autoreninterface (sie bleibt nur als
  Importquelle lesbar);
- die JSON-Strukturen selbst;
- `elang_help` und `elang_check` als Tabellen;
- V1-URLs und V1-JavaScript-Schnittstellen (kein Kompatibilitäts-Shim).
