# Lastenheft · Pflichtenheft · Technisches Blueprint

**Plugin:** `mod_elang` — „Sprachübung" (Version 2.0)
**Zielplattform:** Moodle **4.5 LTS** bis **5.3 LTS** · PHP **8.1 – 8.4**
**Autor / Lizenz:** Ralf Erlebach · GNU GPL v3 or later
**Dokumentversion:** 1.2 · Stand: 23. Juli 2026 (Session 002)

---

## 0. Zweck und Geltung dieses Dokuments

Dieses Dokument ist die verbindliche fachlich-technische Grundlage der Entwicklung
von `mod_elang` 2.0. Es besteht aus drei Teilen:

- **Lastenheft** (Kap. 3) — *was* verlangt wird.
- **Pflichtenheft** (Kap. 4) — *wie* es umgesetzt wird, inkl. Abnahmekriterien.
- **Technisches Blueprint** (Kap. 5–18) — die konkrete Architektur.

Ergänzende Dokumente im selben Verzeichnis:

| Dokument | Inhalt |
| --- | --- |
| `Blueprint_kompakt.md` | Einseitige Arbeitsfassung |
| `Machbarkeit_Zusatzanforderungen.md` | Technische Prüfung der Zusatz- und Roadmap-Anforderungen |
| `Migration_V1_V2.md` | Migrations- und Restore-Konzept |
| `Lizenz_und_Herkunft.md` | Lizenz-, Herkunfts- und Namensfragen |
| `Ideen_Backlog.md` | Bewertetes Ideen-Backlog |

Drei Festlegungen sind dem gesamten Dokument übergeordnet:

- **Leitentscheidung A (verbindlich):** Version 2.0 ist eine **kompatible
  Neuentwicklung** innerhalb desselben Plugins `mod_elang`, keine Modernisierung
  des vorhandenen Codes. Fachliche Kompatibilität (Formate, Bewertungsregeln,
  Migrationspfad) ist Ziel; Code-Kompatibilität ausdrücklich nicht.
- **Leitentscheidung B (verbindlich):** Es wird ausschließlich gegen dokumentierte
  Moodle-APIs entwickelt. Eigene Endpunkte, eigene HTML-Dokumente, eigene
  Frontend-Frameworks und eigene AJAX-Schichten sind ausgeschlossen.
- **Leitentscheidung C (verbindlich):** Kein Release ohne vollständige
  Privacy-API-Implementierung, ohne CSRF-/Capability-Absicherung aller
  schreibenden Operationen und ohne verlustfreie Inhaltsbearbeitung.

---

## 1. Ausgangslage

### 1.1 Bestand

Version 1 (`mod_elang` 1.x, Université de La Rochelle, CeCILL-B) implementiert
videobasierte Lückentextübungen mit zeitcodierten Untertiteln, Hilfen,
Antwortvergleich (u. a. Jaro-Distanz) und Lernfortschritt. Zielplattform ist
Moodle 3.4 / PHP 7.1. Der fachliche Kern ist tragfähig; die Implementierung ist es
nicht mehr.

### 1.2 Befundlage aus der technischen Gesamtbewertung

Vier P0-Blocker bestimmen den Umfang von Version 2.0:

| ID | Befund | Konsequenz für V2.0 |
| --- | --- | --- |
| **P0.1** | Jedes Speichern der Aktivität löscht alle Cues **und alle Nutzerantworten** (`locallib.php:333-335`), ohne Transaktion | Versionierte Übungsdefinition (Kap. 6) |
| **P0.2** | Schreibende AJAX-Aufrufe (`server.php` `task=check`, `task=help`) ohne `confirm_sesskey()`; Zurücksetzen von Fortschritten per GET | Eigene AJAX-Schicht entfällt vollständig, External Functions + `core/ajax` (Kap. 7) |
| **P0.3** | Keine `classes/privacy/provider.php`, obwohl personenbezogene Daten gespeichert werden; `elang_help` und `elang_check` ohne `userid` und Zeitstempel | Vollständige Privacy-Implementierung, normalisierte Antwortdaten (Kap. 15) |
| **P0.4** | Frontend auf Enyo 2.5.1.1 / Bootstrap 3.3.7 / Bower, eigenes HTML-Dokument in `play.php:57-109` außerhalb des Moodle-Seitenrahmens | Neue Moodle-native Oberfläche (Kap. 8) |

Hinzu kommen Sicherheits-, Performance- und Korrektheitsbefunde (ungeprüfte Links
aus Untertiteltexten, JSONP, unbegrenzte Antwortlängen, Lösungen in Logs,
`unserialize()` auf Dateimetadaten, Gruppenlecks im Bericht, vollständige
Datenladungen, JSON-Blobs statt Relationen, lineare Completion-Prüfung, fehlerhafter
Gap-Zähler in `locallib.php:342/381`, fehlende Transaktionen, leere
Upgrade-Infrastruktur).

### 1.3 Warum Neuentwicklung statt Modernisierung

Eine Portierung würde Datenmodell, Frontend, API-Schicht, Reporting,
Datenschutzintegration und Testarchitektur ohnehin vollständig ersetzen. Übrig
bliebe im Wesentlichen die Parser- und Bewertungssemantik — und die lässt sich als
**Spezifikation** übernehmen, ohne den Code zu übernehmen. Die Portierung wäre
teurer und fehleranfälliger als der Neubau.

### 1.4 Plattformspanne und zeitliche Randbedingung

Version 2.0 muss von **Moodle 4.5 LTS** bis **Moodle 5.3 LTS** laufen. Das ist eine
Entscheidung für Verbreitung: 4.5 ist die derzeit meistgenutzte LTS-Version, und ein
Plugin, das erst ab 5.2 installierbar wäre, erreicht die Bestandsinstallationen von
`mod_elang` nicht.

Daraus folgen drei harte Randbedingungen:

1. **PHP 8.1 ist die Untergrenze.** Moodle 4.5 verlangt mindestens PHP 8.1,
   Moodle 5.2 und 5.3 mindestens PHP 8.3 und unterstützen 8.4. Der Code muss
   also auf **PHP 8.1 bis 8.4** laufen. Sprachmerkmale ab PHP 8.2 (`readonly`
   Klassen, DNF-Typen), ab 8.3 (typisierte Klassenkonstanten) und ab 8.4
   (Property Hooks) sind ausgeschlossen.
2. **Versionsabhängige APIs müssen erkannt, nicht vorausgesetzt werden.** Die
   Unterschiede sind bekannt und überschaubar; sie sind in Kap. 5.1 einzeln
   aufgeführt und werden über Fähigkeitsprüfungen behandelt, nicht über
   Versionsvergleiche im Fachcode.
3. **Moodle 5.3 ist noch nicht veröffentlicht.** Code Freeze ist der
   **24. August 2026**, die Veröffentlichung der LTS-Version der
   **5. Oktober 2026**. Bis dahin wird gegen den Integrationszweig `main`
   mitgetestet, **nicht blockierend**; danach wird gegen `MOODLE_503_STABLE`
   revalidiert.

Ende des Supports von Moodle 4.5: Sicherheitskorrekturen laufen bis
**4. Oktober 2027**. Bis dahin bleibt 4.5 in der Matrix; danach kann die
Untergrenze in einem Hauptversionsschritt angehoben werden.

---

## 2. Zielbestimmung

### 2.1 Musskriterien

Eine produktionsreife, Moodle-native Aktivität für videobasierte
Lückentextübungen, die

1. Inhalte **verlustfrei** bearbeitbar macht,
2. alle schreibenden Operationen **CSRF- und berechtigungsgeprüft** ausführt,
3. Lerndaten **normalisiert, exportierbar und löschbar** speichert,
4. eine **barrierefreie** Oberfläche im Moodle-Seitenrahmen bietet,
5. vorhandene V1-Aktivitäten und Lerndaten **migriert**,
6. mit **Moodle Coding Standards, Tests und CI** wartbar bleibt.

### 2.2 Abgrenzung (Nicht-Ziele in 2.0)

- **Nicht-Ziel:** Rückwärtskompatibilität zu Moodle < 4.5.
- **Nicht-Ziel:** Weiterbetrieb der V1-Oberfläche oder der V1-AJAX-Schnittstelle.
- **Nicht-Ziel:** Ersatz für `mod_h5pactivity` / H5P Interactive Video. `mod_elang`
  bleibt bewusst auf zeitcodierte Transkript-Lückenübungen mit belastbarer
  Auswertung, Gradebook-Anbindung und Reporting spezialisiert; H5P deckt breitere,
  aber flacher auswertbare Interaktionsformen ab.
- **Nicht-Ziel:** Videohosting. Videos kommen aus der Moodle File API oder aus
  von Moodle unterstützten externen Quellen.
- **Zurückgestellt (2.1+):** adaptive Wiederholung, Ausspracheübungen,
  KI-Assistenz, YouTube-Untertitelimport, Moodle-App-Offlinemodus.
- **Zurückgestellt (Bewertung offen):** KI-Videogenerierung — siehe
  `Machbarkeit_Zusatzanforderungen.md`, Kap. 6.

### 2.3 Bedeutung von „kompatibel"

„Kompatibel" heißt in diesem Projekt genau vier Dinge:

1. **Komponentenname bleibt** `mod_elang` (Upgradepfad statt Parallelinstallation).
2. **Importformate bleiben** WebVTT und SubRip.
3. **Fachliche Bewertungsregeln bleiben nachvollziehbar** (Normalisierung,
   Toleranzmaß, Hilfestufen) und werden als Referenzfälle festgeschrieben.
4. **Bestandsdaten bleiben erhalten** — sowohl per Datenmigration als auch beim
   Restore von V1-Sicherungen.

Nicht kompatibel sind: interne Tabellenstruktur, JSON-Formate, URL-Schema,
JavaScript-API, Untertitel-Auszeichnungssyntax als primäres Autorenwerkzeug.

---

## 3. Lastenheft (Auftraggebersicht — „was")

### 3.1 Funktionale Anforderungen

| ID | Anforderung |
| --- | --- |
| **L-F1** | Lernende bearbeiten Lücken in einem zeitcodierten Transkript, synchron zu einem Video- oder Audiomedium. |
| **L-F2** | Lehrende importieren Untertitel im Format **WebVTT** und **SubRip (SRT)**, mit Vorschau und Validierungsbericht vor der Übernahme. |
| **L-F3** | Lehrende markieren Lücken in einem **visuellen Editor** (Timeline + Segmentliste + Textauswahl), nicht über eine Auszeichnungssyntax in der Untertiteldatei. Die V1-Syntax bleibt beim Import lesbar. |
| **L-F4** | Die Übungsdefinition ist **versioniert**. Änderungen an einer Übung dürfen bereits begonnene oder abgeschlossene Versuche nicht verändern und niemals löschen. |
| **L-F5** | Es gibt ein **Versuchsmodell**: konfigurierbare Versuchsanzahl, Fortsetzen oder Neubeginn, Wertung als bester/letzter/durchschnittlicher Versuch. |
| **L-F6** | Die **Antwortbewertung** ist pro Lücke einer von zwei benannten Algorithmen: „komplett-richtig" (zeichengenau, inkl. Diakritika/Apostrophe) oder „Wort erkannt" (Grundform-Abgleich über eine Translationstabelle). Mehrere akzeptierte Antworten je Lücke sind möglich; optional reguläre Ausdrücke für berechtigte Autor:innen. Die Grundform-Reduktion für nicht-lateinische Schriften (Koreanisch, Chinesisch, Japanisch, Sanskrit, Kyrillisch, …) ist über einen eigenen Subplugin-Typ (`elangscript`) offen erweiterbar, ohne den Kern zu ändern. |
| **L-F7** | **Mehrstufige Hilfen** je Lücke (sprachlicher Hinweis → Anfangsbuchstabe/Wortlänge → Teilauflösung → Lösung), je Stufe mit konfigurierbarem Punktabzug. |
| **L-F8** | Ergebnisse werden an das **Moodle-Gradebook** übergeben. |
| **L-F9** | **Aktivitätsabschluss** ist konfigurierbar über Ansicht, Bearbeitungsgrad, Mindestpunktzahl, Anzahl gelöster Lücken oder Abschluss eines Versuchs. |
| **L-F10** | Lehrende erhalten **Berichte**: Versuche und Lernverläufe, Erfolgsquote je Lücke, häufige Fehlantworten, Hilfenutzung, Bearbeitungsdauer, wahlweise pseudonymisiert. |
| **L-F11** | Berichte sind exportierbar als **CSV, Excel (XLSX), OpenDocument (ODS) und JSON**. |
| **L-F12** | Das Transkript ist als **Arbeitsblatt exportierbar**: PDF sowie bearbeitbar als **Word (DOCX)** und **OpenDocument Text (ODT)**, wahlweise **bebildert** mit Standbildern aus dem Video, mit konfigurierbarer Lückendarstellung (mit/ohne Wortlängenhinweis) und wahlweise als Lösungsblatt. |
| **L-F13** | **Backup und Restore** funktionieren vollständig, einschließlich Restore von **V1-Sicherungen** in eine V2-Installation. |
| **L-F14** | Vorhandene **V1-Aktivitäten und Lerndaten werden migriert**; die Migration ist wiederaufnehmbar, blockweise, datenbankneutral und mit Trockenlauf prüfbar. |
| **L-F15** | Die Oberfläche läuft **im Moodle-Seitenrahmen** mit Theme, Navigation, Breadcrumbs und Abschlussstatus. Kein eigenes Fenster, kein eigenes HTML-Dokument. |
| **L-F16** | Die Aktivität integriert sich in die **Aktivitätenübersicht** von Moodle 5.x (Status, letzte Bearbeitung, Punktzahl, Abschluss). |
| **L-F17** | Die Aktivität besitzt ein **Moodle-konformes einfarbiges SVG-Icon** und ist dem Aktivitätszweck **„Prüfen" (assessment)** zugeordnet. |
| **L-F18** | Die Lernfunktionen sind über **Web Services** erreichbar, damit die Moodle App angebunden werden kann. |
| **L-F19** | **Gruppenmodus** und Gruppenberechtigungen werden in Bearbeitung, Berichten und Exporten konsequent beachtet. |
| **L-F20** | Lernende sehen **Fortschritt, Versuchsstand und Speicherstatus** jederzeit; unterbrochene Bearbeitung ist fortsetzbar. |

### 3.2 Nicht-funktionale Anforderungen

| ID | Anforderung |
| --- | --- |
| **L-Q1** | Lauffähig von **Moodle 4.5 LTS** bis **5.3 LTS**; **PHP 8.1 bis 8.4**; PostgreSQL und MariaDB/MySQL. Keine Abwärtskompatibilität zu Moodle < 4.5. |
| **L-Q2** | Keine Änderungen an Moodle-Kerndateien; ausschließlich dokumentierte APIs. |
| **L-Q3** | **Sicherheit:** jede schreibende Operation über POST mit Sesskey-Prüfung bzw. External Function, mit Capability- und Kontextprüfung. **Lösungen und akzeptierte Antwortvarianten verlassen den Server niemals**, solange sie nicht regelkonform freigegeben sind. Keine Lösungstexte in Ereignisprotokollen. |
| **L-Q4** | **Datenschutz:** vollständige Privacy-API-Implementierung (Metadaten, Export, Löschung pro Kontext und pro Nutzer:in, Userlist), definierte Aufbewahrung, Datenminimierung in Berichten und Exporten. |
| **L-Q5** | **Barrierefreiheit:** WCAG 2.1 AA als Zielniveau; vollständige Tastaturbedienbarkeit, belastbare ARIA-Semantik, Statusinformation nie allein über Farbe, Zoom bis mindestens 200 % ohne Funktionsverlust, kein `user-scalable=no`. |
| **L-Q6** | **Performance:** Der Aufwand für Anzeige und Auswertung skaliert mit dem *sichtbaren* Abschnitt, nicht mit der Gesamtlänge der Übung. Abschlussprüfung nahezu konstant aufwendig. Keine Vollladung aller Cues und Antworten. |
| **L-Q7** | **Wartbarkeit:** Moodle Coding Standards (phpcs `--max-warnings 0`, phpdoc, Mustache-Lint, ESLint), PHPUnit- und Behat-Abdeckung, CI-Matrix, keine Legacy-Zweige. |
| **L-Q8** | **Internationalisierung:** vollständige Sprachdateien, Unicode-Normalisierung (NFC), korrekte Behandlung von Akzenten, Apostrophen, Ligaturen, nichtlateinischen Schriften und RTL; keine Manipulation der globalen Prozess-Locale. |
| **L-Q9** | **Keine harten Fremdabhängigkeiten.** Optionale Integrationen (KI-Subsystem, OAuth-2-Dienste, Dateikonverter, `ffmpeg`) werden zur Laufzeit erkannt und degradieren sauber. |
| **L-Q10** | **Reproduzierbare Releases:** die Quellstruktur *ist* das installierbare Plugin; keine Build-Platzhalter in `version.php`; Abhängigkeitsstände werden nicht beim Build verworfen. |
| **L-Q11** | **Robustheit:** mehrschrittige Schreiboperationen laufen in delegierten Transaktionen; konkurrierende Zugriffe erzeugen keine inkonsistenten Zwischenstände. |
| **L-Q12** | **Versionsspanne:** Funktionen, die erst ab einer bestimmten Moodle-Version verfügbar sind, werden über Fähigkeitsprüfung eingebunden und degradieren auf älteren Versionen sichtbar, aber ohne Fehler. Kein Fachcode fragt Versionsnummern ab. |

---

## 4. Pflichtenheft (Auftragnehmersicht — „wie")

### 4.1 Umsetzungszuordnung

| ID | Umsetzung |
| --- | --- |
| **P1** (→L-F1/L-F15/L-Q5) | Aktivitätsseite `view.php` mit `$OUTPUT->header()/footer()`, Renderables, Renderer und Mustache-Templates; Player, synchronisiertes Transkript und Bearbeitungsbereich als native ES-Module unter `amd/src/`. Kein jQuery, kein Enyo, kein Bower. |
| **P2** (→L-F2/L-F3) | Import über `classes/local/import/{vtt_parser,srt_parser,legacy_markup_parser}.php` mit Vorschau- und Validierungsschritt; visueller Editor als eigene Seite `edit.php` mit Timeline-, Segment- und Lückenkomponenten. |
| **P3** (→L-F4/L-Q11) | Versionierte Übungsdefinition (`elang_version`, `elang_cue`, `elang_gap`, `elang_gapanswer`, `elang_gaphint`); Änderungen erzeugen eine neue Version; Versuche verweisen auf genau die bearbeitete Version. Sämtliche Schreibpfade in `$DB->start_delegated_transaction()`. |
| **P4** (→L-F5/L-F8/L-F9/L-Q6) [implementiert] | `attempt_manager` führt aggregierte Zähler (`totalgaps`, `answeredgaps`, `exactgaps`, `correctgaps`, `hintedgaps`, `score`, `state`) auf `elang_attempt` mit; Completion und Gradebook lesen künftig ausschließlich diese Aggregate (Anbindung selbst noch offen, Kap. 10.5/10.6). Completion-Einstellungen sind reguläre Spalten der Haupttabelle. |
| **P5** (→L-F6/L-Q8) [implementiert] | `answer_evaluator` mit genau zwei benannten Algorithmen (`exact`, `wordrecognized`), die Reduktion je Schriftsystem über austauschbare `script_handler`-Implementierungen (`latin_script_handler` im Kern, `elangscript`-Subplugins für nicht-lateinische Schriften); Unicode-Normalisierung über `Normalizer` statt `setlocale()`; harte serverseitige Längenobergrenze je Lücke plus Verteidigung in der Tiefe bei Regex-Antwortvarianten. Details in Kap. 10. |
| **P6** (→L-F7) | `elang_gaphint` mit Stufe, Typ, Text und Abzug; Hilfeanforderung ausschließlich über External Function, Abzug serverseitig verbucht. |
| **P7** (→L-F10/L-F11/L-F19/L-Q4) | Berichte über **Report Builder** (System Reports + Entities); Export über die **Dataformat API** (`\core\dataformat`) — CSV, XLSX, ODS, JSON ohne Eigencode; Gruppenfilter über `groups_get_activity_groupmode()` / `groups_get_activity_group()`; E-Mail-Adresse keine Standardspalte; Schutz vor Formel-Injektion in Tabellenformaten. |
| **P8** (→L-F12) | `classes/local/export/{worksheet_builder,pdf_exporter,docx_exporter,odt_exporter}.php`; PDF über die Moodle-`pdf`-Klasse (TCPDF); DOCX und ODT als ZIP-Pakete aus Vorlagen über `ZipArchive` ohne Fremdbibliothek; Standbilder über `classes/local/media/frame_provider.php` (browserseitige Erfassung zur Autorenzeit, optional serverseitig, sonst ohne Bilder). Erzeugung schwerer Exporte als Ad-hoc-Task, Cache über Content-Hash. |
| **P9** (→L-F13/L-F14) | Backup-/Restore-Klassen für das neue Modell **plus** Restore-Pfad für V1-Strukturen; Migration als wiederaufnehmbarer Ad-hoc-Task mit Fortschrittsmarker, Blockgröße und Trockenlaufbericht; Steuerung über Adminseite und CLI. |
| **P10** (→L-F16/L-F17/L-Q12) | Zweigleisige Kursintegration: `index.php` für Moodle 4.5, `classes/courseformat/overview.php` für die Aktivitätenübersicht ab Moodle 5.0 (auf 4.5 wirkungslos, aber unschädlich); `pix/monologo.svg` (einfarbig, `#212529`) plus PNG-Fallback; `elang_supports(FEATURE_MOD_PURPOSE)` liefert `MOD_PURPOSE_ASSESSMENT`; `elang_is_branded()` liefert `false`. |
| **P11** (→L-F18/L-Q3) | Alle Interaktionen über External Functions unter `classes/external/`, deklariert in `db/services.php`, aufgerufen über `core/ajax`; `ajax => true`, `services => [MOODLE_OFFICIAL_MOBILE_SERVICE]` für die App-fähigen Funktionen. Keine `server.php`, kein JSONP, kein `callback`-Parameter. |
| **P12** (→L-Q3) | Antwortprüfung ausschließlich serverseitig. Die Player-Nutzlast enthält Cue-Texte, Lückenpositionen, Längenhinweise und Zustände, **nie** Lösungen, Antwortvarianten oder Hilfetexte höherer Stufen. Links aus Übungsinhalten sind eigene Datenfelder, validiert über `PARAM_URL`/`moodle_url`, ausgegeben mit `rel="noopener noreferrer"`. |
| **P13** (→L-Q4) | `classes/privacy/provider.php` implementiert `metadata\provider`, `request\plugin\provider` und `request\core_userlist_provider`; Antworttexte ausschließlich in `elang_response`; Ereignisse enthalten nur IDs und Zustandsänderungen (`attemptid`, `responseid`, `resultstate`, `usedhint`). `elang_help` und `elang_check` entfallen ersatzlos. |
| **P14** (→L-Q6) | Statische Übungsdefinition und individueller Zustand werden getrennt geladen; Cue-Paginierung serverseitig; unveränderliche Versionsdaten über die **Cache API** (MUC, Schlüssel = Content-Hash); Originaluntertitel unverändert über die File API ausgeliefert, Lückendarstellung im DOM. |
| **P15** (→L-Q7/L-Q1) | Makefile spiegelt die CI-Prüfkette; GitHub-Actions-Matrix stichprobenartig über Moodle 4.5 / 5.0 / 5.2 / 5.3-dev unter Beachtung der jeweiligen PHP-Grenzen × MariaDB/PostgreSQL; PHPUnit, Behat, phpcs, phpdoc, Mustache-Lint, Grunt/ESLint, `validate`. PHP-Lint läuft gegen beide Enden der Spanne (8.1 und 8.4). |
| **P16** (→L-Q9/L-Q10) | `version.php` ohne Platzhalter und ohne `$plugin->cron`; keine `dependencies`; optionale Integrationen über Fähigkeitsprüfungen zur Laufzeit; `thirdpartylibs.xml` nur, wenn tatsächlich Fremdcode ausgeliefert wird. |
| **P17** (→L-F20) | Zwischenspeicherung von Eingaben im Browser, gebündelte Übertragung über eine Autosave-Funktion, sichtbarer Speicher- und Verbindungsstatus, Fortsetzen an der letzten Position. |

### 4.2 Abnahmekriterien

**Funktional**

- Eine rein redaktionelle Änderung (Name, Beschreibung, Poster) an einer Aktivität
  mit vorhandenen Versuchen verändert **keinen** Antwortdatensatz und erzeugt
  **keine** neue Übungsversion.
- Eine inhaltliche Änderung erzeugt genau eine neue Version; laufende Versuche
  bleiben mit der alten Version verbunden und bleiben auswertbar.
- Ein Import identischer VTT- und SRT-Dateien erzeugt identische Cue- und
  Gap-Strukturen (Referenztest).
- Die definierten Referenzfälle liefern exakt die erwarteten Klassifizierungen
  für beide Algorithmen (Groß-/Kleinschreibung, Akzente, Apostrophvarianten,
  ß/œ/ø-artige Sonderfälle, unabhängige Antwortvarianten und reguläre
  Ausdrücke) — **erfüllt seit 2.0.0-alpha.2**, siehe
  `tests/local/grading/{latin_script_handler_test,answer_evaluator_test}.php`.
- Eine Sprache ohne installiertes `elangscript`-Subplugin bewertet nachweislich
  weiterhin korrekt (Fallback auf `latin_script_handler`); ein installiertes
  Subplugin wird nachweislich für seine deklarierten Sprachcodes bevorzugt vor
  dem Kern-Standard herangezogen — **erfüllt seit 2.0.0-alpha.2**, siehe
  `tests/local/grading/script_handler_manager_test.php`.
- Migration einer produktionsnahen V1-Datenmenge läuft vollständig durch,
  wiederaufnehmbar, ohne Datenverlust; der Trockenlaufbericht stimmt mit dem
  Ergebnis überein.
- Restore einer V1-Sicherung erzeugt eine funktionsfähige V2-Aktivität.
- Bericht und Export liefern in allen vier Formaten (CSV, XLSX, ODS, JSON)
  denselben Datenbestand.
- Der bebilderte Arbeitsblattexport erzeugt in PDF, DOCX und ODT ein Dokument mit
  identischer Segmentabfolge; DOCX und ODT öffnen fehlerfrei in Word **und**
  LibreOffice.

**Sicherheit und Datenschutz**

- Kein schreibender Endpunkt ist ohne Sesskey- bzw. External-Function-Prüfung
  erreichbar (Negativtests).
- Die Player-Nutzlast enthält in keinem Zustand Lösungstexte (automatisierter
  Test gegen die Antwortstruktur der External Function).
- Privacy-Export enthält alle Versuche, Antworten und Hilfenutzungen der
  betroffenen Person; Löschung pro Kontext und pro Person entfernt sie vollständig.
- In Kursen mit getrennten Gruppen sehen Lehrende ohne
  `moodle/site:accessallgroups` nachweislich keine fremden Gruppen.

**Qualität**

- phpcs (Moodle-Standard, `--max-warnings 0`), phpdoc, Mustache-Lint, Grunt/ESLint,
  `moodle-plugin-ci validate`, PHPUnit und Behat grün auf Moodle 4.5 und 5.2
  sowie auf dem 5.3-Stand nach dessen Veröffentlichung.
- Accessibility-Audit (Axe/WAVE, Tastatur-only, Screenreader, Zoom 200 %,
  Kontrast) ohne Befund der Schwere „kritisch" oder „schwer".
- Lasttest: Übung mit ≥ 1500 Cues und ≥ 300 Lücken bleibt im Player und in der
  Abschlussprüfung innerhalb der definierten Zeitbudgets.

---

## 5. Blueprint — Komponenten- und Dateistruktur

```
mod/elang/
├── version.php                       # 4.5 minimum, supported [405,503], keine deps
├── lib.php                           # supports/purpose, Instanz-Lebenszyklus, Datei-Serving
├── locallib.php                      # nur noch dünne Hilfsfunktionen (Ziel: entfällt)
├── mod_form.php                      # Aktivitätseinstellungen
├── view.php                          # Lernendenseite (Moodle-Seitenrahmen)
├── index.php                         # Instanzliste — nur auf Moodle 4.5 relevant
├── edit.php                          # Autorenwerkzeug (Timeline/Segmente/Lücken)
├── report.php                        # Einstieg in die Report-Builder-Berichte
├── export.php                        # Arbeitsblatt-/Transkriptexport (bestätigter POST)
├── settings.php                      # Admin-Einstellungen (Defaults, Limits, Optionales)
├── script/                           # elangscript-Subplugins (siehe Kap. 10.2), leer im Kern
├── amd/src/
│   ├── player.js                     # Mediensteuerung, Segmentwiederholung, Tempo
│   ├── transcript.js                 # Synchronisiertes Transkript, Fokusführung
│   ├── gapinput.js                   # Eingabe, Validierungsanzeige, Tastaturlogik
│   ├── hints.js                      # Hilfestufen
│   ├── autosave.js                   # Bündelung, Wiederholung, Statusanzeige
│   ├── repository.js                 # core/ajax-Aufrufe (einziger Netzwerkpfad)
│   └── editor/{timeline,gapeditor,preview}.js
├── templates/                        # view, player, transcript, cue, gap, editor, …
├── classes/
│   ├── external/                     # External Functions (siehe Kap. 7)
│   ├── local/
│   │   ├── domain/{attempt_manager,version_manager}.php
│   │   ├── grading/{script_handler,latin_script_handler,script_handler_manager,
│   │   │            answer_evaluator,grading_result}.php   # siehe Kap. 10
│   │   ├── repository/{cue_repository,gap_repository,attempt_repository,…}.php
│   │   ├── import/{vtt_parser,srt_parser,legacy_markup_parser,import_report}.php
│   │   ├── export/{worksheet_builder,pdf_exporter,docx_exporter,odt_exporter}.php
│   │   └── media/frame_provider.php
│   ├── output/                       # Renderables + renderer
│   ├── reportbuilder/local/{entities,systemreports}/
│   ├── courseformat/overview.php     # Aktivitätenübersicht (ab Moodle 5.0)
│   ├── plugininfo/elangscript.php    # Subplugin-Typ-Deklaration (siehe Kap. 10.2)
│   ├── event/                        # nur IDs und Zustände
│   ├── task/{migrate_v1_task,build_worksheet_task,…}.php
│   └── privacy/provider.php
├── backup/moodle2/                   # backup_/restore_ inkl. V1-Restore-Pfad
├── db/                               # install.xml, access.php, services.php, subplugins.json,
│                                     # caches.php, tasks.php, upgrade.php, install.php
├── lang/{en,de}/elang.php
├── pix/monologo.{svg,png}
├── tests/                            # PHPUnit, generator, fixtures, behat
├── tools/                            # Entwicklerhilfen (nicht ausgeliefert)
└── docs/                             # dieses Dokument und Begleitmaterial
```

### 5.1 Versionsabhängige APIs in der Spanne 4.5 – 5.3

Alle Punkte werden über Fähigkeitsprüfung behandelt (Klassen- und
Funktionsexistenz, `get_config`), nie über Versionsvergleiche im Fachcode.

| Thema | Moodle 4.5 | ab Moodle 5.0 | Umgang |
| --- | --- | --- | --- |
| Aktivitätsübersicht (`courseformat\overview`) | nicht vorhanden | ersetzt die Modul-`index.php` | beides ausliefern; `index.php` bleibt korrekt verlinkt, die Overview-Klasse wird auf 4.5 schlicht nicht geladen |
| KI-Subsystem `core_ai` | vorhanden, Aktionen `generate_text` und `generate_image` | zusätzlich `summarise_text` und `explain_text`; ab 5.1 Steuerung je Kurs und Aktivität | KI-Assistenz nur anbieten, wenn Subsystem **und** benötigte Aktion vorhanden sind |
| Report Builder | vorhanden | erweitert | gemeinsamen Funktionsumfang von 4.5 nutzen |
| Dataformat API | vorhanden (seit 3.1) | unverändert | keine Fallunterscheidung nötig |
| `monologo.svg`, `FEATURE_MOD_PURPOSE` | vorhanden (seit 4.0) | unverändert | keine Fallunterscheidung nötig |
| `*_is_branded()` | vorhanden (seit 4.4) | unverändert | keine Fallunterscheidung nötig |
| PHPUnit-Metadaten | Doc-Comment-Annotationen (`@covers`) | Attribute bevorzugt | einheitlich `@covers` verwenden, solange 4.5 in der Matrix ist; Umstellung erst beim Anheben der Untergrenze |
| PHP-Sprachstand | 8.1 | bis 8.4 | Code strikt auf PHP 8.1 beschränken |

---

## 6. Blueprint — Datenmodell

### 6.1 Tabellen

```text
elang                      Aktivitätsinstanz  [Stand 2.0.0-alpha.2: implementiert]
 ├─ id, course, name, intro, introformat
 ├─ language               BCP-47-ähnlicher Sprach-/Schriftcode, steuert die
 │                         Handler-Wahl in Kap. 10 (z. B. de, fr, ko, zh-Hans)
 ├─ currentversionid       veröffentlichte Version — bewusst OHNE deklarierten
 │                         Fremdschlüssel, um einen zirkulären DDL-Verweis mit
 │                         elang_version.elangid zu vermeiden (Anwendungslogik
 │                         statt DB-Constraint)
 ├─ mediasource            Datei | externe URL                    [offen]
 ├─ maxattempts, grademethod, grade                                [offen]
 ├─ hintpolicy, hintpenalty                                        [offen]
 ├─ completionminscore, completionmingaps, completionallgaps       [offen]
 ├─ answermaxlength        [offen — vgl. elang_gap.maxlength]
 └─ timecreated, timemodified

elang_version              unveränderliche Übungsversion  [implementiert]
 ├─ id, elangid, versionnumber
 ├─ status                 draft | published | archived
 ├─ contenthash            Cache- und Exportschlüssel
 └─ usermodified, timecreated

elang_cue                  Transkriptsegment  [implementiert]
 ├─ id, versionid, sortorder
 ├─ cuekey                 stabile Identität über Versionen hinweg
 ├─ starttime, endtime     Millisekunden
 └─ transcript, transcriptformat

elang_gap                  Lücke innerhalb eines Cues  [implementiert]
 ├─ id, cueid, sortorder
 ├─ gapkey                 stabile Identität über Versionen hinweg
 ├─ charstart, charlength  Position im Cue-Text
 ├─ solution               Musterlösung — verlässt den Server nie (Kap. 7/10)
 ├─ gradingalgorithm       exact | wordrecognized (Kap. 10.1)
 ├─ maxlength, linkurl     Zusatzbegrenzung/validierte Zusatzressource (optional)

elang_gapanswer            akzeptierte Antwortvariante  [implementiert]
 ├─ id, gapid, sortorder
 ├─ answer
 └─ isregex                0/1 — regulärer Ausdruck, siehe Kap. 10.3

elang_gaphint              Hilfestufe  [implementiert]
 ├─ id, gapid, level
 ├─ hinttype               text | firstletter | wordlength | partial | solution
 │                         | translation (translation reserviert für 2.1-4)
 ├─ hinttext, penalty
 └─ timecreated

elang_attempt              Versuch einer Person  [Schema implementiert,
                            Domänenlogik (Start/Fortsetzen/Abschluss) offen]
 ├─ id, elangid, versionid, userid, attemptnumber
 ├─ state                  inprogress | finished | abandoned
 ├─ totalgaps, answeredgaps, hintedgaps
 ├─ exactgaps              exakt gelöst, unabhängig vom konfigurierten Algorithmus
 ├─ correctgaps            als richtig akzeptiert (exactgaps ⊆ correctgaps)
 ├─ score
 └─ timestart, timefinish, timemodified

elang_response              Antwort auf eine Lücke  [Schema implementiert,
                            Schreibpfad über External Function offen]
 ├─ id, attemptid, gapid
 ├─ responsetext           vollständig gespeichert (Voraussetzung für 2.1-2)
 ├─ resultstate             exact | wordrecognized | incorrect | empty
 ├─ accepted                0/1 — resultstate erfüllt gradingalgorithm des Gaps
 ├─ tries, hintlevel, score
 └─ timecreated, timemodified
```

`[implementiert]` markiert Tabellen, die bereits über `db/install.xml` und
`db/upgrade.php` existieren (Stand 2.0.0-alpha.2). `[offen]` markiert Felder, die
erst mit der Autoren- bzw. Versuchs-API (Phase 3/4) hinzukommen. Die
Kernel-Grading-Logik (`classes/local/grading/`, siehe Kap. 10) ist bereits
lauffähig und getestet, auch wenn noch keine Oberfläche sie aufruft.

**Stabile fachliche Identität (`cuekey`, `gapkey`).** Eine Version ist
unveränderlich — Segment und Lücke sind fachlich aber dieselben, auch wenn eine
neue Version entsteht. `cuekey` und `gapkey` bleiben deshalb über Versionsgrenzen
hinweg gleich. Das ist die Voraussetzung dafür, Antworten aus älteren Versuchen
einer geänderten Version zuzuordnen — und damit die technische Vorleistung für die
nachträgliche Anerkennung von Antwortvarianten samt Neubewertung (Kap. 19,
Vorhaben 2.1-2). Ohne diese Schlüssel ließe sich eine Neubewertung nach
Inhaltsänderung nicht sauber durchführen. Sie werden deshalb bereits in 2.0
eingeführt, obwohl 2.0 sie noch nicht auswertet.

**`resultstate` vs. `accepted`.** Diese Trennung ist bewusst: `resultstate` hält
die feinste Klassifizierung fest, die der Evaluator finden konnte — unabhängig
vom konfigurierten Algorithmus der Lücke. `accepted` ist die davon getrennte,
regelbasierte Entscheidung, ob das für *diese* Lücke als richtig zählt. Ein exakt
getippter Treffer auf einer lediglich „Wort erkannt"-konfigurierten Lücke wird
also weiterhin als `exact` protokolliert — nicht als generisches „richtig". Damit
können Berichte zeigen, wie präzise geantwortet wurde, unabhängig davon, wie
streng die Lücke eingestellt ist. Details und Beispiele in Kap. 10.

**Schlüssel und Indizes (Auszug):** eindeutig `elang_attempt(elangid, userid,
attemptnumber)`; eindeutig `elang_response(attemptid, gapid)`; eindeutig
`elang_cue(versionid, cuekey)` und `elang_gap(cueid, gapkey)`; Fremdschlüssel auf
`user`, `course` und die jeweils übergeordnete Tabelle; Index
`elang_cue(versionid, sortorder)` und `elang_cue(versionid, starttime)` für
Paginierung und Zeitsuche.

### 6.2 Warum keine JSON-Blobs

V1 speichert Cue-Definition, Antworten und Aktivitätsoptionen als große
JSON-Texte. Folge: vollständiges Decode bei jeder Auswertung, keine SQL-seitige
Aggregation, verlorene Updates bei parallelen Requests, unbrauchbare Indizes und
ein Read-Modify-Write-Zyklus über den gesamten Cue-Zustand für eine einzelne
Antwort. V2 speichert **eine Zeile je fachlichem Objekt**; Fortschritt und
Abschluss ergeben sich aus indizierten Zählern.

### 6.3 Entfallene Tabellen

`elang_help` und `elang_check` entfallen ersatzlos. Ihre analytische Funktion
übernehmen `elang_response` (mit Nutzerbezug, Versuchsbezug, Zeitstempel und
Kontext) sowie Standard-Moodle-Events ohne Lösungstexte.

---

## 7. Blueprint — API-Schicht

`src/server/server.php` entfällt vollständig. Alle Interaktionen laufen über
External Functions in `classes/external/`, deklariert in `db/services.php` und
aufgerufen über `core/ajax`. Damit gelten zentrale Sicherheitsprüfung, strikte
Parameter- und Rückgabetypen sowie Kontext- und Capability-Validierung innerhalb
jeder Funktion.

| Funktion | Zweck | Schreibend | App |
| --- | --- | --- | --- |
| `mod_elang_get_exercise` [implementiert] | statische Übungsdefinition der aktuellen Version (ohne Lösungen) | – | ja |
| `mod_elang_get_cues` [implementiert] | paginierte Cues (offset/limit, siehe Abweichung unten) | – | ja |
| `mod_elang_start_attempt` [implementiert] | Versuch beginnen oder fortsetzen | ja | ja |
| `mod_elang_get_attempt_state` [implementiert] | individueller Zustand als kompaktes Zustandsobjekt | – | ja |
| `mod_elang_submit_response` [implementiert] | Antwortprüfung für **eine** Lücke (serverseitig) | ja | ja |
| `mod_elang_request_hint` [implementiert] | nächste Hilfestufe anfordern, Abzug verbuchen | ja | ja |
| `mod_elang_finish_attempt` [implementiert] | Versuch abschließen, Bewertung verbuchen | ja | ja |
| `mod_elang_save_draft_version` | Autorenstand speichern | ja | – |
| `mod_elang_publish_version` | Entwurf veröffentlichen | ja | – |
| `mod_elang_preview_import` | Importvorschau und Validierungsbericht | – | – |
| `mod_elang_queue_worksheet` | Arbeitsblatt-Export beauftragen | ja | – |

Abweichung von der ursprünglichen Planung: Statt eines gebündelten
`mod_elang_submit_responses` (Mehrzahl, mehrere Lücken je Aufruf) wurde
`mod_elang_submit_response` (Einzahl, eine Lücke je Aufruf) umgesetzt — passend
zur Signatur von `attempt_manager::submit_response()`. Ein Bündeln mehrerer
Antworten in einem Aufruf ist eine spätere Optimierung (weniger Requests bei
Segmentwechsel), keine Voraussetzung für Korrektheit, und kann ergänzt werden,
ohne die Einzel-Funktion zu entfernen. Ebenso ist `mod_elang_get_cues` seit
2.0.0-alpha.5 mit einfachem `offset`/`limit` (Obergrenze 200 je Seite) statt
eines Zeitfensters umgesetzt — korrekt und für das Lasttest-Ziel (≥1500 Cues)
ausreichend; ein positionsbezogenes Fenster-Fetching bleibt eine mögliche
spätere Verfeinerung, keine Korrektheitslücke.

**Kritische Ergänzung zu `mod_elang_get_cues` (Kap. 6.1 unten präzisiert):**
`elang_cue.transcript` speichert den VOLLSTÄNDIGEN Originaltext — die
Lösungswörter stehen wörtlich darin, da `elang_gap.charstart`/`charlength`
Positionen INNERHALB dieses Texts referenzieren. Jede Funktion, die einen
Transkript-Text zurückgibt, MUSS ihn zuvor durch
`classes/local/domain/transcript_masker.php` schicken (ersetzt jede
Lücken-Zeichenspanne durch ein `{{gap:<gapkey>}}`-Token). Aus demselben Grund
gibt `get_cues` `charstart`/`charlength` NICHT je Lücke zurück — die Zeichen
LÄNGE der Lösung wäre ein kostenloser, unangeforderter „Wortlänge"-Hinweis,
obwohl Hinweise laut `elang_gaphint` bewusst ein anfragbarer, potenziell mit
Abzug versehener Mechanismus sein sollen. Das maskierte Token im Transkript
reicht dem Player zur Positionierung.

**Regeln für alle implementierten Funktionen (Stand 2.0.0-alpha.5, verifiziert
gegen reale Moodle-Beispiele):** Kontext aus der Modul-ID bzw. — bei
versuchsbezogenen Funktionen — aus dem Versuch selbst auflösen,
`self::validate_context($context)` (deckt die Login-Prüfung ab; ein zusätzlicher
`require_login()`-Aufruf ist bei External Functions weder nötig noch das
verbreitete Muster), `require_capability('mod/elang:attempt', $context)`, **und
zusätzlich eine Eigentümerprüfung** (`attempt_helper::require_attempt_ownership()`):
eine Capability allein verhindert nicht, dass eine berechtigte Person einen
fremden Versuch anhand einer erratenen ID bearbeitet. `submit_response` prüft
zusätzlich, dass die angesprochene Lücke tatsächlich zur Version des Versuchs
gehört, und begrenzt die Antwortlänge hart auf 500 Zeichen als Verteidigung in
der Tiefe (die konfigurierbare Pro-Lücke- bzw. Site-Grenze bleibt offen, siehe
Kap. 21). Keine Lösungstexte in Rückgaben oder Events. Rückgabe als deklarierte
`external_single_structure`.

Destruktive Operationen (Fortschritt zurücksetzen, Version verwerfen) laufen
**nicht** über GET-Links, sondern über ein Moodleform mit Bestätigung bzw. einen
bestätigten POST-Workflow.

---

## 8. Blueprint — Lernendenoberfläche

Drei Bereiche in einem Moodle-Template:

1. **Medienbereich** — Untertitel ein-/ausblendbar, Wiedergabegeschwindigkeit,
   Segment wiederholen, 5/10 Sekunden zurück, vollständige Tastatursteuerung,
   Audio-only-Modus.
2. **Synchronisiertes Transkript** — aktuelles Segment hervorgehoben, Segmente
   anklickbar, Zustände (offen / gelöst / mit Hilfe gelöst / falsch) mit Symbol
   **und** Text, nicht nur über Farbe.
3. **Bearbeitungsbereich** — eindeutige Eingabe, Absenden-Schaltfläche,
   verständliches Feedback, gestufte Hinweise, sichtbarer Speicherstatus,
   Fortschritt und Versuchszähler.

**Technisch:** semantisches HTML, Mustache-Templates, Moodle-Komponenten
(`core/modal`, `core/notification`, Moodle-Icons), native ES-Module, `core/ajax`
als einziger Netzwerkpfad, Statusmeldungen über ARIA-Live-Regionen, kein
`user-scalable=no`, keine `href="#"`-Schaltflächen, keine Glyphicons.

Die Textspur wird **nicht** nutzerspezifisch neu geschrieben. Die Originaldatei
wird unverändert über die File API ausgeliefert; Lücken entstehen im DOM.

---

## 9. Blueprint — Autorenwerkzeug

Die V1-Auszeichnungssyntax (`[Antwort]`, `{Antwort}`, `[Antwort(Link)]`) bleibt
**als Importquelle** lesbar, ist aber nicht mehr das Autoreninterface.

Der Editor bietet: Video-Timeline, Liste der Untertitelsegmente, Textauswahl →
„Als Lücke markieren", akzeptierte Antwortvarianten, Regeln für
Groß-/Kleinschreibung, Akzente und Interpunktion, Hilfestufen, Feedback, Vorschau
aus Lernendensicht sowie Validierung überlappender oder ungültiger Zeitsegmente.

Speichern erzeugt einen **Entwurf**; erst „Veröffentlichen" erzeugt eine neue
Version. Laufende Versuche bleiben an der bisherigen Version.

---

## 10. Blueprint — Bewertung, Abschluss, Gradebook

### 10.1 Zwei Bewertungsalgorithmen [implementiert]

Statt eines frei kombinierbaren Bündels von Einzelschaltern (Groß-/
Kleinschreibung, Leerraum, Interpunktion, Diakritika je einzeln togglebar) gibt es
genau **zwei benannte Algorithmen**, je Lücke über `elang_gap.gradingalgorithm`
gewählt:

| Algorithmus | Bezeichnung | Verhalten |
| --- | --- | --- |
| `exact` | „komplett-richtig" | Zeichengenauer Treffer — Diakritika, Groß-/Kleinschreibung und Apostrophvariante müssen stimmen. Unicode-Normalform wird technisch kanonisiert (NFC), das ist keine Toleranz, sondern Voraussetzung dafür, dass zwei optisch identische Zeichenketten überhaupt vergleichbar sind. |
| `wordrecognized` | „Wort erkannt" | Ein Treffer zählt, sobald Antwort und Lösung auf dieselbe Grundform reduziert werden — kleingeschrieben, mit auf Basisbuchstaben reduzierten oder transliterierten Diakritika, mit vereinheitlichten Apostrophvarianten. Die Reduktion selbst ist schriftsystemabhängig (Kap. 10.2). |

**`resultstate` vs. `accepted` (Kap. 6.1):** Der Evaluator ermittelt immer die
feinstmögliche Klassifizierung (`exact` > `wordrecognized` > `incorrect` >
`empty`), unabhängig vom konfigurierten Algorithmus der Lücke. `accepted` ist die
davon getrennte Regel: ein `exact`-Treffer wird immer akzeptiert; ein
`wordrecognized`-Treffer nur, wenn die Lücke selbst auf `wordrecognized`
konfiguriert ist. Diese Trennung liefert Berichten eine Präzisionsauskunft
(„richtig, aber ungenau getippt"), die eine einzelne Boolean-Spalte nicht leisten
könnte, und sie liefert `elang_attempt.exactgaps` und `.correctgaps` als
unterscheidbare Aggregate.

**Umgesetzt in** `classes/local/grading/{answer_evaluator,grading_result}.php`.
Referenzfälle (u. a. café/cafe, Straße/strasse, œuf/oeuf, kız/kiz,
Apostrophvarianten) sind in
`tests/local/grading/{latin_script_handler_test,answer_evaluator_test}.php`
festgeschrieben — dieselben Fälle, die dieses Kapitel beschreibt, sind lauffähige
Tests, keine bloße Absichtserklärung.

### 10.2 Schriftsystemabhängige Reduktion und das `elangscript`-Subplugin [implementiert]

Die Reduktion für `wordrecognized` ist für lateinische Schriften ein
überschaubares, gut definiertes Problem (Diakritika entfernen, transliterieren).
Für Koreanisch, Chinesisch, Japanisch, Sanskrit, Kyrillisch und weitere
nicht-lateinische Schriften ist „auf Basisbuchstaben reduzieren" keine sinnvolle
Operation — dort brauchte es je Schrift ein eigenes, nicht-triviales
Transliterationsschema (Revised Romanization, Pinyin, Rōmaji, IAST,
wissenschaftliche Transliteration, …). Diese Vielfalt im Kern zu bündeln wäre ein
eigenständiges, fehleranfälliges Großprojekt; stattdessen definiert `mod_elang`
einen neuen **Subplugin-Typ `elangscript`** (`db/subplugins.json`, Verzeichnis
`script/`), über den Drittanbieter oder spätere Kern-Erweiterungen genau diese
Schriften nachrüsten können, ohne den Kern anzufassen.

```text
classes/local/grading/script_handler.php            Schnittstelle
classes/local/grading/latin_script_handler.php       Kern-Default für lateinische Schriften
classes/local/grading/script_handler_manager.php     Discovery + Routing nach elang.language
classes/plugininfo/elangscript.php                   Moodle-Plugintyp-Deklaration
```

**Routing:** `script_handler_manager` fragt jedes installierte
`elangscript_<name>`-Subplugin (Klasse `\elangscript_<name>\handler`), welche
Sprach-/Schriftcodes es abdeckt (`get_supported_codes()`), und indiziert sie.
Für eine Aktivität mit `elang.language = 'zh-Hans'` wird zuerst der exakte Code,
dann der Primär-Subtag (`zh`) gesucht; beansprucht kein installiertes Subplugin
den Code, greift `latin_script_handler` als Standard — das Plugin bleibt für
lateinische Sprachen **ohne jedes Subplugin voll funktionsfähig**.

**Kein Subplugin für den Latin-Fall.** `latin_script_handler` deckt Deutsch,
Französisch, Spanisch, Italienisch, Portugiesisch, Türkisch, die nordischen
Sprachen, Polnisch, Tschechisch, Slowakisch, Rumänisch und ähnliche
Latin-Alphabet-Sprachen direkt ab: Unicode-NFKD-Zerlegung plus Entfernen
kombinierender Zeichen (`\p{Mn}`) faltet die meisten vorkomponierten Buchstaben
automatisch; eine kleine, handgepflegte Tabelle ergänzt die nicht zerlegbaren
Sonderfälle (ß→ss, æ→ae, œ→oe, ø→o, ð/đ→d, þ→th, ł→l, ħ→h, ı→i, ĳ→ij). Ohne die
`intl`-Erweiterung entfällt nur der automatische NFKD-Anteil (dokumentierter,
nicht stiller Funktionsverlust — `intl` steht auf praktisch jeder
Produktivinstallation zur Verfügung).

**Was ein `elangscript`-Subplugin liefern muss:** eine Klasse
`\elangscript_<name>\handler` unter `classes/handler.php` innerhalb des
Subplugin-Verzeichnisses, die `script_handler` implementiert
(`get_supported_codes()`, `normalise_for_exact()`,
`normalise_for_word_recognised()`). Mehr verlangt der Kern nicht — das jeweilige
Transliterationsschema bleibt vollständig beim Subplugin.

### 10.3 Reguläre Ausdrücke als eigener Mechanismus, nicht als dritte Stufe [implementiert]

`elang_gapanswer.isregex` markiert eine Antwortvariante als regulären Ausdruck.
Ein Treffer zählt immer als `exact` und wird immer akzeptiert — reguläre
Ausdrücke sind ein **alternativer Prüfmechanismus** für Autor:innen mit
`mod/elang:useregex`, keine dritte Toleranzstufe zwischen „exact" und
„wordrecognized". Absicherung gegen das in Kap. 21 (Risiko 7) benannte
DoS-Risiko: harte Obergrenze der geprüften Antwortlänge
(`answer_evaluator::MAX_REGEX_INPUT_LENGTH`) als Verteidigung in der Tiefe,
`preg_match` mit Fehlerunterdrückung statt Absturz bei fehlerhaften Mustern. Die
eigentliche Längenbegrenzung und Musterprüfung beim Speichern bleibt Aufgabe der
noch zu bauenden Autoren-API (Phase 4).

### 10.4 Toleranzmaß-Herkunft

Die V1-Semantik (Jaro-Distanz als kontinuierliches Ähnlichkeitsmaß) wird **nicht**
fortgeführt. An ihre Stelle tritt die oben beschriebene binäre
Zwei-Algorithmen-Klassifizierung, weil sie Autor:innen eine klare, erklärbare
Entscheidung abverlangt („wie streng muss diese Lücke sein") statt eines
schwer vorhersagbaren Ähnlichkeitsschwellwerts. Eingabelänge ist serverseitig hart
begrenzt (`elang_gap.maxlength`, defensiv zusätzlich in `answer_evaluator`);
Strings werden nicht wiederholt mit `mb_substr()` durchlaufen; keine globale
`setlocale()`-Umschaltung — Normalisierung läuft ausschließlich über
`Normalizer`/eigene Tabellen innerhalb des jeweiligen `script_handler`.

### 10.5 Abschluss [Aggregatpflege implementiert, Completion-Anbindung offen]

Abschluss liest ausschließlich die Aggregate auf `elang_attempt`
(`totalgaps`, `answeredgaps`, `exactgaps`, `correctgaps`, `hintedgaps`, `score`)
und ist damit nahezu konstant aufwendig. Abschlussregeln sind reguläre Spalten der
Haupttabelle, nicht Teil eines JSON-Optionsfelds. Neubewertung nur, wenn sich ein
relevanter Zustand tatsächlich geändert haben kann. Die Domänenlogik
(`classes/local/domain/attempt_manager.php`), die diese Aggregate aus einzelnen
`elang_response`-Zeilen fortschreibt, ist seit 2.0.0-alpha.3 implementiert und
getestet (`start_attempt()`, `submit_response()`, `finish_attempt()`); seit
2.0.0-alpha.6 zusätzlich `request_hint()` — Hilfestufen werden strikt in
Reihenfolge freigegeben, und `elang_gaphint.penalty` fließt in die Neuberechnung
von `elang_response.score` und darüber in `elang_attempt.score` ein (nicht
additiv über Stufen hinweg: die Strafe einer Stufe berücksichtigt bereits alles,
was bis einschließlich dieser Stufe offengelegt wurde). Eine nachträglich
angeforderte Hilfe zu einer bereits korrekt beantworteten Lücke senkt die Wertung
rückwirkend, da bei jeder Antwortabgabe UND jeder Hilfeanfrage aus dem
tatsächlichen Zustand neu berechnet wird, nie zum Zeitpunkt der Abgabe fixiert.
Was noch fehlt: die eigentliche Moodle-Completion-Anbindung (`elang_supports(
FEATURE_COMPLETION_HAS_RULES)` liefert bislang `false`, siehe Kap. 5.1/CHANGELOG
alpha.1) und eine maximale Versuchsanzahl (`elang` hat das Feld noch nicht).

### 10.6 Gradebook [offen]

Ein Grade-Item, Wertung als bester / letzter / durchschnittlicher Versuch, Punkte
oder Prozent.

---

## 11. Blueprint — Reporting und Datenexport

**Berichte** über Report Builder (Entities + System Reports): Versuche und
Lernverläufe, Erfolgsquote je Lücke, häufige Fehlantworten, Hilfenutzung,
Bearbeitungsdauer, wahlweise pseudonymisiert. Echte Paginierung, SQL-seitige
Summen, Recordsets für Exporte.

**Export** über die Dataformat API. Moodle liefert die Formate mit; das Plugin
schreibt keinen formatspezifischen Code:

| Format | Kernplugin |
| --- | --- |
| CSV | `dataformat_csv` |
| Excel (XLSX) | `dataformat_excel` |
| OpenDocument (ODS) | `dataformat_ods` |
| JSON | `dataformat_json` |
| HTML, PDF | `dataformat_html`, `dataformat_pdf` (ohne Zusatzaufwand verfügbar) |

**Härtung:** Gruppenprüfung konsequent nach Aktivitätsgruppenmodus;
Datenminimierung (E-Mail-Adresse keine Standardspalte, nur mit
`mod/elang:exportreports`); optional pseudonymisierte Ausgabe; Schutz vor
Formel-Injektion in Tabellenformaten (führende `=`, `+`, `-`, `@`, Tab und CR
werden neutralisiert); Streaming statt Vollladung.

---

## 12. Blueprint — Transkript- und Arbeitsblattexport

Umsetzung von L-F12 und der Anforderung aus Issue #60 des V1-Repositorys.

**Dokumentaufbau:** je Zeitintervall (Standard 30 Sekunden, konfigurierbar) eine
Zeile mit Standbild links und den Cues dieses Intervalls rechts, jeder Cue in
einer eigenen Zeile.

**Lückendarstellung** (konfigurierbar):

| Modus | Darstellung |
| --- | --- |
| `none` | feste Unterstrichbreite, kein Längenhinweis |
| `proportional` | 2 Unterstriche je Zeichen + 2 (V1-Wunschverhalten) |
| `boxed` | Kästchen fester Breite |
| `solution` | Lösungsblatt mit eingetragener Musterlösung |

**Formate**

| Format | Umsetzung |
| --- | --- |
| PDF | Moodle-Klasse `pdf` (`lib/pdflib.php`, TCPDF) |
| DOCX | ZIP-Paket aus OOXML-Vorlage über `ZipArchive`, Bilder als `word/media/*` |
| ODT | ZIP-Paket aus ODF-Vorlage über `ZipArchive`, Bilder als `Pictures/*` |

**Standbilder** — dreistufige Strategie, siehe
`Machbarkeit_Zusatzanforderungen.md`, Kap. 2:

1. **Autorenzeit im Browser** (Standard): `<video>` + `<canvas>` erzeugt die
   Einzelbilder einmalig beim Anlegen der Version; Speicherung als Dateien im
   Dateibereich, Schlüssel = Content-Hash der Version.
2. **Serverseitig** (optional): `ffmpeg`, nur wenn in den Plugin-Einstellungen ein
   Pfad hinterlegt ist.
3. **Ohne Bilder** (Fallback): rein textliches Arbeitsblatt.

Erzeugung schwerer Exporte läuft als Ad-hoc-Task; Ergebnisse werden über den
Content-Hash gecacht. Nutzerspezifische Exporte (mit eigenen Antworten) werden nur
auf ausdrückliche Anforderung erzeugt und nicht gecacht.

---

## 13. Blueprint — Icon, Aktivitätszweck, Kursintegration

- `pix/monologo.svg`: einfarbige 24×24-SVG, Strichfarbe `#212529`, plus
  `pix/monologo.png` als Fallback. Motiv: Medienrahmen mit Wiedergabesymbol über
  einer Untertitelzeile mit gestricheltem Lückenabschnitt.
- `elang_supports(FEATURE_MOD_PURPOSE)` liefert `MOD_PURPOSE_ASSESSMENT`. Damit
  erscheint das Icon auf dem Hintergrund des Zwecks „Prüfen"
  (`$activity-icon-assessment-bg`, in der aktuellen Boost-Variablenliste `#f90086`).
- `elang_is_branded()` liefert `false`, damit Moodle das Icon einfärben darf.
- **Kursintegration zweigleisig** (L-Q12): `classes/courseformat/overview.php`
  liefert ab Moodle 5.0 für die Aktivitätenübersicht Status, letzte Bearbeitung,
  Punktzahl und Abschluss. Auf Moodle 4.5 existiert diese Übersicht noch nicht;
  dort greift die klassische `index.php` mit der Instanzliste. Beide werden
  ausgeliefert. Die Overview-Klasse wird auf 4.5 nie geladen und stört dort nicht.
  Die V1-Fehlverlinkung auf `/mod/elang.php` (`index.php:102`, `index.php:108`)
  wird nicht fortgeführt.

---

## 14. Blueprint — Sicherheit

| Befund V1 | Maßnahme V2 |
| --- | --- |
| Schreibende AJAX-Aufrufe ohne Sesskey | Ausschließlich External Functions; keine eigenen Endpunkte |
| Zurücksetzen von Fortschritten per GET | Bestätigter POST-Workflow bzw. Moodleform |
| Ungeprüfte Links aus Untertiteltexten | Eigenes Feld `elang_gap.linkurl`, validiert über `PARAM_URL`/`moodle_url`, nur `http`/`https` oder Moodle-interne Ziele, Ausgabe mit `rel="noopener noreferrer"` |
| JSONP-`callback`-Parameter | Ersatzlos entfallen |
| Unbegrenzte Antwortlängen | Fachliche Maximallänge je Lücke plus harte Serverobergrenze; Request-Throttling; keine Ereignisprotokollierung je Fehlversuch |
| Lösungen in Logs und Analysetabellen | Events enthalten nur IDs und Zustände; Antworttexte nur in `elang_response` |
| `unserialize()` auf Dateimetadaten | Dateityp über Dateiname, MIME-Typ und File API |
| Gruppenlecks im Bericht | `groups_get_activity_groupmode()` / `groups_get_activity_group()` plus Berechtigungsprüfung, durchgängig in Bericht und Export |
| Lösungen im Client | Prüfung ausschließlich serverseitig; Nutzlast ohne Lösungen |

---

## 15. Blueprint — Datenschutz [implementiert seit 2.0.0-alpha.4]

`classes/privacy/provider.php` implementiert:

- `\core_privacy\local\metadata\provider` — Beschreibung aller gespeicherten
  Felder (`elang_attempt`, `elang_response`) und aller Übermittlungen;
- `\core_privacy\local\request\plugin\provider` — Kontexte, Export, Löschung;
- `\core_privacy\local\request\core_userlist_provider` — Nutzerliste je Kontext.

Umgesetzt: Export aller Versuche und Antworten, Löschung pro Person
(`delete_data_for_user`/`delete_data_for_users`), Löschung pro
Aktivitätskontext (`delete_data_for_all_users_in_context`), Privacy-Unit-Tests
(`tests/privacy/provider_test.php`). Löschung nutzt dieselbe
Subquery-basierte Kaskade wie `elang_delete_instance()` in `lib.php`, statt
ID-Listen nach PHP zu laden.

Offen: eine definierte Aufbewahrungslogik (automatisches Löschen nach Ablauf
einer Frist) ist noch nicht Teil des Providers — dafür fehlt noch die
Konfigurationsoberfläche, die eine solche Frist überhaupt festlegen könnte.

Der `null_provider` aus 2.0.0-alpha.1/alpha.2 war korrekt für den
schema-losen bzw. schreibpfadlosen Zwischenstand. Mit den External Functions
aus alpha.4 (`classes/external/`) existiert erstmals ein echter, von außen
erreichbarer Schreibpfad auf `elang_attempt`/`elang_response` — genau der
Punkt, an dem die hier ursprünglich formulierte Freigabevoraussetzung fällig
wurde, und an dem die Ablösung jetzt erfolgt ist.

---

## 16. Blueprint — Performance

| Befund V1 | Maßnahme V2 |
| --- | --- |
| `task=data` lädt alle Cues, alle Antworten, alle JSON-Strukturen | Statische Definition und individueller Zustand getrennt; serverseitige Cue-Paginierung; nur aktuelles und benachbartes Fenster |
| JSON-Blobs verhindern SQL-Aggregation | Normalisierte Tabellen, indizierte Zähler |
| Completion lädt alles und dekodiert alles | Completion liest nur Aggregate auf `elang_attempt` |
| Reporting lädt alle Antworten in den Arbeitsspeicher, manuelle IN-Klauseln | Report Builder, SQL-seitige Summen, echte Paginierung, Recordsets |
| VTT und PDF werden bei jedem Abruf neu erzeugt | Originaldatei unverändert über File API; Arbeitsblätter über Content-Hash gecacht; schwere Exporte als Ad-hoc-Task |
| Zeichenkettenvergleich mit verschachtelten Schleifen und `mb_substr()` | Längenbegrenzung, einmalige Codepoint-Zerlegung, keine globale Locale-Umschaltung |

Unveränderliche Versionsdaten werden in einem MUC-Application-Cache gehalten;
Schlüssel ist der `contenthash` der Version, wodurch Invalidierung trivial wird.

---

## 17. Blueprint — Migration und Backup

Details in `Migration_V1_V2.md`. Kernpunkte:

- Migration wird **an der Existenz der Legacy-Tabellen** festgemacht, nicht an
  Versionsnummern; Sites können mehrere Plugin-Releases übersprungen haben.
- Ablauf: Trockenlauf mit Bericht → Migration als wiederaufnehmbarer Ad-hoc-Task
  in Blöcken → Verifikation → erst danach Abbau der Legacy-Tabellen in einem
  **späteren** Upgrade-Schritt.
- Abbildung: `elang.options` → Spalten; `elang_cues.json` → `elang_cue`,
  `elang_gap`, `elang_gapanswer`; `elang_users.json` → ein `elang_attempt` je
  (Aktivität, Person) plus `elang_response`; `elang_help`/`elang_check` →
  aggregierte Zähler, danach verworfen.
- Backup/Restore für das neue Modell **plus** ein Restore-Pfad, der V1-Sicherungen
  erkennt und in das neue Modell überführt. Der V1-Fehler, bei dem
  `backup_elang_activity_task.class.php:85` `/mod/book/index.php` kodiert, wird
  dabei toleriert.

---

## 18. Blueprint — Tests, Qualität, Betrieb

**PHPUnit:** Generator für Aktivitäten, Versionen, Cues, Lücken und Versuche;
Import-Parser (VTT, SRT, Legacy-Syntax); Antwortnormalisierung und
Bewertungsprofile; Attempt- und Completion-Berechnung; Gradebook-Integration;
External Functions (inklusive Negativtests auf Capability, Kontext, Gruppen und
Lösungsfreiheit der Nutzlast); Privacy Provider; Backup/Restore; Migration
V1 → V2; Export in allen vier Datenformaten; Dokumentexport (Wohlgeformtheit der
OOXML-/ODF-Pakete).

**Behat:** Aktivität anlegen; VTT/SRT importieren; Lücken visuell anlegen; Versuch
starten und fortsetzen; Antwort korrekt/falsch; Hilfe anfordern; Abschluss und
Bewertung; getrennte Gruppen; Berichte und Exporte; Tastaturnavigation.

**Accessibility:** Axe/WAVE, Tastatur-only, Fokusreihenfolge, Screenreader,
Zoom ≥ 200 %, Kontrast, Fehler- und Statusmeldungen über Live-Regionen.

**CI-Matrix:** stichprobenartig über die gesamte Spanne, unter Beachtung der
jeweiligen PHP-Grenzen:

| Moodle | PHP | DB | blockierend |
| --- | --- | --- | --- |
| 4.5 LTS | 8.1 | PostgreSQL | ja |
| 4.5 LTS | 8.3 | MariaDB | ja |
| 5.0 | 8.2 | MariaDB | ja |
| 5.2 | 8.3 | PostgreSQL | ja |
| 5.2 | 8.4 | MariaDB | ja |
| 5.3-dev (`main`) | 8.4 | PostgreSQL | nein |

Behat läuft auf 4.5 und 5.2 (blockierend) sowie auf 5.3-dev (nicht blockierend).
PHP-Lint und phpcs laufen gegen beide Enden der Spanne (8.1 und 8.4). Geprüft
werden phplint, phpcs, phpdoc, `validate`, Mustache-Lint, Grunt/ESLint, PHPUnit
und Behat.

**Betrieb:** Adminseite und CLI für Migration und Verifikation; Ad-hoc-Tasks für
schwere Exporte; keine Scheduled Task ohne belegten Bedarf.

---

## 19. Verbindlicher Umfang von Version 2.1

Die folgenden fünf Vorhaben sind **zugesagter Bestandteil von Version 2.1**, nicht
Backlog. Sie stammen aus `Ideen_Backlog.md` und wurden nach dem Verhältnis von
didaktischem Nutzen zu Aufwand ausgewählt.

| Nr. | Vorhaben | Backlog-ID | Aufwand |
| --- | --- | --- | ---: |
| **2.1-1** | Regelbasierte Lückenerzeugung | B1 | 1–1,5 PW |
| **2.1-2** | Antwortvariante nachträglich anerkennen, mit Neubewertung | B3 | 1,5–2 PW |
| **2.1-3** | Zeichenleiste für Sonderzeichen | F2 | 0,3–0,5 PW |
| **2.1-4** | Übersetzungsspur als Hilfestufe | A3 | 1–1,5 PW |
| **2.1-5** | Blindmodus und Audio-only-Modus | A5/A6 | 0,4–0,6 PW |
| | **Summe** | | **4,2–6,1 PW** |

### 2.1-1 Regelbasierte Lückenerzeugung

Im Autorenwerkzeug erzeugt eine Regel automatisch Lückenvorschläge, die
anschließend einzeln bestätigt oder verworfen werden. Regelarten: jedes n-te Wort;
nur Wörter ab Länge x; nur bestimmte Wortarten; nur Wörter außerhalb einer
hinterlegten Häufigkeits- oder Ausschlussliste; nur innerhalb eines Zeitfensters.
Vollständig deterministisch, ohne externe Dienste.

*Keine Vorleistung in 2.0 nötig* — reines Autorenwerkzeug auf dem Entwurfsstand
einer Version.

### 2.1-2 Antwortvariante nachträglich anerkennen

Lehrende sehen im Bericht die häufigsten Fehlantworten je Lücke, erklären eine
davon als korrekt und lösen damit eine Neubewertung aller betroffenen Versuche aus.

Ablauf: Variante aufnehmen → neue Übungsversion → Neubewertungs-Task ordnet die
vorhandenen Antworten über `gapkey` der neuen Version zu und bewertet nur die
betroffenen Lücken neu → Gradebook-Aktualisierung → Protokolleintrag mit
auslösender Person, Zeitpunkt, Variante und Anzahl geänderter Versuche.

**Vorleistung in 2.0 (zwingend):**

1. `cuekey` und `gapkey` als versionsübergreifend stabile Identität (Kap. 6.1).
2. `elang_response` speichert den Antworttext dauerhaft, nicht nur das Ergebnis —
   sonst ist keine Neubewertung möglich.
3. Bewertung als reine Funktion von (Antworttext, Bewertungsprofil,
   Antwortvarianten), ohne verstreute Zustände.

Ohne diese drei Punkte wäre 2.1-2 nachträglich nur mit einer Datenmigration
umsetzbar. Sie sind deshalb bereits im 2.0-Datenmodell verankert.

### 2.1-3 Zeichenleiste für Sonderzeichen

Anklickbare Leiste über dem Eingabefeld mit den Sonderzeichen der Zielsprache
(z. B. `à â ç é è ê ë î ï ô ù û ü ÿ œ`), konfiguriert über die Sprache der
Aktivität, überschreibbar je Aktivität. Einfügen an der Cursorposition, vollständig
tastaturbedienbar, korrekt für Screenreader beschriftet. Fügt sich in die
Anforderungen L-Q5 und L-Q8 ein.

*Vorleistung in 2.0:* die Aktivität führt ohnehin ein Sprachfeld; die Leiste greift
darauf zu.

### 2.1-4 Übersetzungsspur als Hilfestufe

Zusätzlich zur Untertitelspur der Zielsprache kann eine zweite Spur importiert
werden — Übersetzung in die Erstsprache oder vereinfachte Fassung. Sie wird nicht
dauerhaft angezeigt, sondern als eigene Hilfestufe angeboten (`hinttype`
`translation`) und damit in Punktabzug und Auswertung einbezogen.

Neue Tabelle in 2.1: `elang_cuetranslation (id, cueid, lang, text, textformat)`.
*Keine Änderung am 2.0-Schema nötig*, weil das Hilfestufenmodell erweiterbar
angelegt ist.

### 2.1-5 Blindmodus und Audio-only

Zwei Anzeigemodi, die aus demselben Material unterschiedliche Schwierigkeitsgrade
machen:

- **Blindmodus:** das Transkriptsegment wird erst nach dem Antwortversuch sichtbar;
  erzwingt Hören statt Mitlesen.
- **Audio-only:** das Videobild bleibt ausgeblendet, nur der Ton läuft; hilft
  zusätzlich bei schmaler Bandbreite.

Umsetzung als Aktivitätseinstellung `displaymode`, optional durch Lernende
lockerbar. Der Audio-only-Modus ist so klein, dass er bereits in 2.0 mitlaufen
kann; die Zusage gilt spätestens für 2.1.

---

## 20. Roadmap nach 2.1

Technische Prüfung siehe `Machbarkeit_Zusatzanforderungen.md`.

| Version | Vorhaben | Reifegrad |
| --- | --- | --- |
| **2.1** | Untertitelimport aus YouTube **für eigene Kanalvideos** über OAuth 2 und die YouTube Data API | machbar, eng begrenzt |
| **2.2** | Adaptive Wiederholung fehlerhaft oder mit Hilfe gelöster Lücken | machbar |
| **2.2** | Mehrere Lückentypen (Auswahl, Wortspeicher), Diktatmodus | machbar |
| **2.2** | KI-gestützte Autorenassistenz (Lückenvorschläge, Hinweistexte, Antwortvarianten) über das Moodle-KI-Subsystem | machbar mit vorhandenen Kernaktionen |
| **2.2** | Automatische Untertitelerzeugung aus Video (Spracherkennung) | **derzeit nicht über das Moodle-KI-Subsystem abbildbar** — Kernaktion fehlt |
| **offen** | Videogenerierung aus Transkript per KI | **nicht empfohlen** — keine Kernaktion, hoher Aufwand, geringer didaktischer Ertrag |
| **2.2** | Ausspracheübungen mit Audioaufnahme | machbar, datenschutzintensiv |
| **2.2** | Moodle-App-Unterstützung inkl. Offlinebearbeitung | machbar auf Basis der External Functions |

---

## 21. Phasenplan und Aufwand

| Phase | Inhalt |
| --- | --- |
| **1 — Stabilisierung und Spezifikation** | V1-Daten und reales Verhalten sichern; Referenzübungen und erwartete Bewertungen festschreiben; V2-Schema und Migrationsregeln festlegen; Skelett, CI und Dokumentation aufsetzen *(abgeschlossen mit 2.0.0-alpha.1)* |
| **2 — Daten- und Domain-Schicht** | Versioniertes Datenmodell, Repositories, Bewertungsservice, Attempt-Manager, Completion, Gradebook, Migration und deren Tests |
| **3 — Lernendenoberfläche** | Seite, Templates, Player, synchronisiertes Transkript, Antwort- und Hilfe-API, Fortschritt, Barrierefreiheit |
| **4 — Autorenoberfläche, Reporting, Export** | Visueller Editor, Importvalidierung, Vorschau, Report Builder, Datenexport, Arbeitsblattexport, Gruppen- und Berechtigungskonzept |
| **5 — Härtung und Release** | Privacy API, Backup/Restore, Lasttests, Accessibility-Audit, Security-Review, Migrationstest mit produktionsnaher Datenmenge, Revalidierung gegen Moodle 5.3 nach dem 5. Oktober 2026 |

**Aufwand (Größenordnung, Personenwochen)**

| Arbeitspaket | Aufwand |
| --- | ---: |
| Spezifikation und Architektur | 1–2 |
| Datenmodell und Migration | 3–5 |
| Domain, API, Completion, Gradebook | 3–5 |
| Player und Lernenden-UX | 3–5 |
| Autoreneditor | 3–5 |
| Reporting, Privacy, Backup | 2–4 |
| Datenexport CSV/XLSX/ODS/JSON | 0,5 |
| Arbeitsblattexport PDF/DOCX/ODT inkl. Standbilder | 2–4 |
| Icon und Aktivitätszweck | < 0,5 |
| Tests, Accessibility, Härtung | 3–5 |
| **Summe V2.0** | **21–36** |

Ein engeres MVP ohne visuellen Timeline-Editor, ohne DOCX/ODT-Export und ohne
erweiterte Analytik liegt bei etwa **12–18 Personenwochen**.

Der zugesagte Umfang von Version 2.1 (Kap. 19) kommt mit **4,2–6,1 Personenwochen**
hinzu. Die dafür nötigen Vorleistungen stecken bereits in den 2.0-Paketen
Datenmodell und Domain und erhöhen den 2.0-Aufwand nicht messbar.

---

## 22. Offene Punkte und Risiken

| # | Punkt | Umgang |
| --- | --- | --- |
| 1 | Die Spanne 4.5 – 5.3 zwingt den Code auf **PHP 8.1**, während auf 5.2 und 5.3 bereits 8.4 läuft. Versehentlich genutzte neuere Sprachmerkmale fallen erst im 4.5-Job auf | PHP-Lint und phpcs laufen verbindlich gegen 8.1; der 4.5-Job ist blockierend und steht in der Matrix an erster Stelle |
| 2 | Moodle 5.3 ist noch nicht veröffentlicht; APIs können sich bis zum Code Freeze ändern | 5.3-Jobs laufen nicht blockierend; Revalidierung nach dem 5. Oktober 2026 |
| 3 | Lizenzwechsel CeCILL-B → GPL v3+ bei Übernahme von V1-Logik | Klärung und Regeln in `Lizenz_und_Herkunft.md`; im Zweifel Neuimplementierung aus der Spezifikation |
| 4 | Maintainerschaft für `mod_elang` | Kontakt mit Christophe Demko zur Fortführung ist aufgenommen; bis zur schriftlichen Bestätigung bleibt der Rückfallweg eines eigenen Komponentennamens mit Migrationswerkzeug bestehen (`Lizenz_und_Herkunft.md`, Kap. 3) |
| 5 | Standbilder für Arbeitsblätter sind für eingebettete Fremdvideos (YouTube, Vimeo) technisch nicht erreichbar | Funktion nur für Moodle-Dateien und direkte Medien-URLs anbieten, sonst Fallback ohne Bilder |
| 6 | Spracherkennung und Videogenerierung sind im Moodle-KI-Subsystem nicht als Aktionen vorgesehen | Schnittstelle vorbereiten, Umsetzung erst nach Kernunterstützung; kein Eigenweg am Subsystem vorbei |
| 7 | Reguläre Ausdrücke in Antwortvarianten sind ein DoS-Risiko | Nur mit eigener Capability, mit Längen- und Zeitbegrenzung, mit Vorabvalidierung |
| 8 | Migrationsdauer bei großen Beständen | Blockweise Ad-hoc-Tasks, Trockenlauf mit Mengengerüst, Wartungsfenster-Empfehlung im Betriebshandbuch |
| 9 | PHPUnit-Metadaten: Doc-Comment-Annotationen sind in neueren PHPUnit-Versionen abgekündigt, Attribute stehen auf Moodle 4.5 noch nicht zur Verfügung | Einheitlich `@covers` verwenden; die 5.2- und 5.3-Jobs decken auf, sobald das nicht mehr trägt |
| 10 | Doppelte Kursintegration (`index.php` **und** `courseformat\overview`) kann auseinanderlaufen | Beide Pfade zeigen dieselben Daten aus derselben Quelle; Behat prüft auf 4.5 die Instanzliste, auf 5.2 die Aktivitätenübersicht |
| 11 | Moodle 4.5 kennt nur die KI-Aktionen `generate_text` und `generate_image` | KI-Assistenz (2.2) prüft Subsystem und Aktion einzeln und blendet sich sonst aus |
