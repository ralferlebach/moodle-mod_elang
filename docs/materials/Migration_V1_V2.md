# Migration V1 → V2 und Restore von Altsicherungen

**Stand:** 23. Juli 2026 · gehört zu `Lastenheft_Pflichtenheft_Blueprint.md`
(Anforderungen L-F13, L-F14, Pflichtenheft P9).

---

## 1. Ausgangslage

`db/upgrade.php` in Version 1 enthält keine realen Upgrade-Schritte. Gleichzeitig
gilt:

- Sites können mehrere Plugin-Releases übersprungen haben; die gespeicherte
  `version`-Nummer ist deshalb kein verlässlicher Anker.
- Der Sprung von Moodle 3.4 auf 5.2 erfolgt bei realen Installationen ohnehin über
  mehrere Moodle-Zwischenversionen. Das Plugin muss den Zustand vorfinden, nicht
  vorhersagen.
- Die V1-Daten liegen überwiegend als JSON-Text in `elang_cues.json`,
  `elang_users.json` und `elang.options` vor.

**Grundsatz:** Die Migration wird an der **Existenz der Legacy-Tabellen**
festgemacht, nicht an Versionsnummern.

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
| — | `elang_version` (Nr. 1, `published`) | genau eine Version je migrierter Aktivität |
| `elang_cues` (ohne `json`) | `elang_cue` | `number` → `sortorder`, `begin`/`end` → `starttime`/`endtime`, `title` → `transcript` |
| `elang_cues.json` | `elang_gap`, `elang_gapanswer` | Lücken, Musterlösung, Alternativen, Link |
| `elang_users` (je Cue, je Person) | `elang_attempt` + `elang_response` | ein Versuch (`attemptnumber = 1`) je (Aktivität, Person) |
| `elang_help` | Zähler `hintedgaps`, `hintlevel` | danach verworfen — kein Nutzerbezug, kein Zeitstempel |
| `elang_check` | Zähler `tries` | danach verworfen — dito |

### 3.1 Bekannte Altlasten, die die Migration ausgleichen muss

- **Fehlerhafter Gap-Zähler.** In V1 wird in `locallib.php:342` `$i` als
  Cue-Index verwendet und in `locallib.php:381` innerhalb der Gap-Verarbeitung mit
  `$i++` verändert. Die Cue-Nummerierung kann dadurch von der Lückenanzahl
  vorheriger Cues abhängen. Die Migration nummeriert deshalb Cues **neu** über die
  Startzeit und protokolliert Abweichungen zur gespeicherten `number`.
- **Verwaiste Antworten.** Weil jedes Speichern in V1 Cues und Antworten löschte
  und Cues neu anlegte, existieren in Beständen Antwortdatensätze ohne passenden
  Cue. Sie werden in den Bericht aufgenommen und, wenn keine Zuordnung möglich
  ist, verworfen — nie stillschweigend.
- **Freie URLs aus `[Antwort(Link)]`.** Werden beim Import validiert
  (`PARAM_URL`, nur `http`/`https` oder Moodle-interne Ziele). Nicht valide Links
  landen als Befund im Bericht, die Lücke bleibt erhalten.
- **Unbegrenzte Antworttexte.** Werden auf die neue Maximallänge gekürzt; jede
  Kürzung erscheint im Bericht.

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

- Die Restore-Klasse erkennt die alte Struktur an den Elementnamen der
  Backup-XML und überführt sie über **dieselben Abbildungsregeln** wie die
  Datenmigration.
- Der V1-Fehler, bei dem `backup_elang_activity_task.class.php:85` fälschlich
  `/mod/book/index.php` kodiert, wird beim Restore toleriert und korrigiert.
- Ebenso werden die fehlerhaften Legacy-URLs aus
  `classes/event/report_viewed.php:121-139` (`id?id=`) ignoriert.
- Abnahmetest: Restore einer echten V1-Sicherung erzeugt eine funktionsfähige
  V2-Aktivität mit vollständigen Cues, Lücken und — sofern in der Sicherung
  enthalten — Lerndaten.

---

## 6. Was ausdrücklich **nicht** migriert wird

- die V1-Auszeichnungssyntax als Autoreninterface (sie bleibt nur als
  Importquelle lesbar);
- die JSON-Strukturen selbst;
- `elang_help` und `elang_check` als Tabellen;
- V1-URLs und V1-JavaScript-Schnittstellen (kein Kompatibilitäts-Shim).
