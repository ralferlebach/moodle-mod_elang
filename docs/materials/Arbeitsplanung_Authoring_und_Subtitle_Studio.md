# Arbeitsplanung — Authoring, Report/Export & Subtitle Studio

Stand: 2.0.0-alpha.58. Dieses Dokument bündelt die offenen Punkte des laufenden
Arbeitspakets und beschreibt das neue, eigenständige Arbeitspaket „Subtitle
Studio & Authoring-UX".

---

## A. Bewertung des aktuellen Ist-Stands (aus den Screenshots)

Was funktioniert:

- Der Editor ist erreichbar (`edit.php`) und die Shell rendert server-seitig:
  Buttons „Entwurf speichern / Veröffentlichen / Cue hinzufügen", Panels
  „Untertitel importieren" und „Medium".
- Die Einstellungen (Abschnitt „Antwortbewertung" mit Sprache + Schwellwert)
  sind vorhanden.
- Report und Export sind über die Aktionsleiste auf der Aktivitätsseite
  erreichbar.

Was NICHT funktioniert / unfertig ist:

- **P0 — Editor lädt keinen Inhalt.** Statusmeldung „Der Editor konnte nicht
  geladen werden." Das ist die `catch`-Meldung, wenn der Aufruf
  `mod_elang_get_version_content` scheitert. Ab alpha.58 hängt der Editor die
  echte Fehlerursache in Klammern an die Meldung an. Zu klären ist, ob die
  External Functions registriert sind (Upgrade gelaufen?) oder ein Code-Fehler
  vorliegt — Diagnose über die konkrete Meldung bzw. den Netzwerk-Tab
  (`service.php`-Antwort).
- Das Medien-Panel zeigt aktuell alle Felder gleichzeitig, weil das JS-Toggle
  erst nach erfolgreichem Laden greift (Folge von P0).
- Die Bedienung ist noch technisch/roh (ms-Felder, keine geführten Workflows).

---

## B. Laufendes Arbeitspaket — offene Punkte (priorisiert)

### P0 — Editor-Ladefehler beheben
- Ursache anhand der neuen Detailmeldung bestimmen.
- Falls „function not found": Upgrade/Service-Registrierung; falls Code: Fix in
  `get_version_content`/Rückgabestruktur.
- Danach greift auch das Medien-Panel-Toggle wieder.

### P1 — Kleine, konkrete UX-Verbesserungen (aus dem Feedback)
1. **Anbieter als Dropdown** im Medien-Panel statt Freitext. **[Umgesetzt in
   alpha.62]** Kuratierte, pflegbare Liste (`provider_registry`: youtube,
   vimeo); Wert = Provider-Schlüssel. Die Anbieter-Referenz akzeptiert eine
   bloße Video-ID **oder** einen Link in gängiger Form (watch-URL mit
   Tracking-Parametern, `youtu.be`, `/shorts/`, `/embed/`, `/live/`,
   `player.vimeo.com`, Kanal-Pfade, mit/ohne Schema) und wird serverseitig in
   `set_draft_media` auf die kanonische Video-ID normalisiert; unbekannte
   Anbieter und unparsbare Referenzen werden abgewiesen. Die kuratierte Liste
   kommt aus `get_version_content` (`mediaproviders`). Admin-Einschränkung
   siehe P2.
2. **Sprachauswahl in den Einstellungen: Instanz-Sprache vorauswählen.**
   **[Umgesetzt in alpha.62]** Beim Bearbeiten wird der gespeicherte
   `language`-Wert vorausgewählt (via `set_data`); Neu-Anlage defaultet auf die
   Kurs-Sprache (Fallback Site-Sprache), sofern in den Optionen vorhanden —
   Lang-Pack-Varianten wie `de_du` werden auf den Basiscode abgebildet.
3. **Sprache required?** Empfehlung: **nicht hart `required`**. Begründung:
   „Generisch" ist ein legitimer Wert (Latin-Fallback), und Bestandsaktivitäten
   mit leerer Sprache dürfen nicht beim nächsten Speichern blockiert werden.
   Stattdessen: sinnvoller Default + optionaler Hinweis. Falls doch gewünscht,
   als `required` umsetzbar — dann Migrationspfad für Altbestand bedenken.

### P2 — Admin-Einstellung: Sprachvielfalt einschränken **[Umgesetzt in alpha.63]**
- Admin-Setting `mod_elang/allowedlanguages` (`configmultiselect`) in
  `settings.php`; leer = keine Einschränkung (Default). `mod_form` bietet über
  `language_options` nur die erlaubten Sprachen an (plus „Generisch"). Die
  gespeicherte Sprache einer Aktivität bleibt stets im Dropdown erhalten, auch
  wenn sie später aus der erlaubten Liste entfernt wird. Basiscode-Mapping
  (`de_du` → `de`) und Restriktionslogik zentral und getestet.
### P3 — Word/ODF-Transkript-Export **[Umgesetzt in alpha.64]**
- Zusätzlich zu PDF (TCPDF) und Text nun DOCX und ODT, beide **ohne**
  Fremdbibliothek: `docx_writer` erzeugt einen minimalen OOXML-Container
  (Content-Types, Package-/Document-Rels, document.xml, styles.xml) und packt
  über Moodles Core-`zip_packer`; `odt_writer` erzeugt einen minimalen
  OpenDocument-Container über PHPs Core-`ZipArchive`, sodass `mimetype`
  unkomprimiert und als erster Eintrag steht (extern verifiziert: `file`
  erkennt „OpenDocument Text"). Gemeinsame Basis ist
  `transcript_exporter::paragraphs()`. PHPUnit-getestet (Container-Teile,
  Escaping, mimetype-stored-first).
### P4 — Gruppen-Feinschliff & Kleinreste
- Report-Gruppenfilter ist umgesetzt; Sichtprüfung im UI ausstehend.
- Player-Meldung „kein Inhalt veröffentlicht" statt generischem loaderror.

---

## C. NEUES Arbeitspaket „AP-D: Subtitle Studio & Authoring-UX"

### Technologie-Entscheidung (Session 005, freigegeben)

Das Autorentool wird als **gebündelte React/TypeScript-Anwendung** umgesetzt
(Option B): Quellcode unter `js/src`, per esbuild (`build.mjs`) zu
`amd/build/editor_lazy.min.js` gebündelt (React/ReactDOM/Scheduler enthalten,
deklariert in `thirdpartylibs.xml`), geladen über den dünnen AMD-Loader
`amd/src/editor.js`. Toolchain und Muster sind 1:1 aus mod_vimipad übernommen
(dort produktiv erprobt, inkl. tsc-Typecheck und Jest/jsdom-Testsuite).

Hintergrund: Moodle 5.2 hat die Core-React-Infrastruktur eingeführt
(`{{#react}}`-Helper, Import-Maps, `js/esm`-Build); ab 5.3 LTS ist React der
empfohlene Weg für neue reaktive UI. Unser Support-Korridor 4.5–5.3 schließt
Core-React für 2.0 aus (auf 4.5–5.1 existiert die Infrastruktur nicht),
deshalb das eigene Bundle.

Präzisierung von Grundsatz B / Entwurfsentscheidung 5:
- **Lernenden-Oberfläche (Player):** weiterhin framework-frei, native
  ES-Module unter `amd/src`. Unverändert.
- **Autoren-Oberfläche (Editor/Studio):** React/TS als gebündelte Anwendung
  mit injizierbarem Transport (core/ajax) und String-Resolver (core/str) —
  weiterhin ausschließlich dokumentierte Moodle-APIs für alle Server-Zugriffe.
- **Migrationspfad:** Sobald der 4.5-Support endet (mod_elang 3.x), wird der
  Mount auf Core-React (`{{#react}}` + `js/esm`) umgestellt; Komponenten- und
  Service-Schnitt folgen bereits jetzt den Core-Konventionen, sodass der
  Wechsel im Wesentlichen Mount-Mechanismus und Import-Quelle betrifft.

### Import: V1-Lücken-Syntax (Session 005, umgesetzt)

`preview_import` erkennt mit der Option `parsegaps` die alte
elang-1.x-Inline-Syntax in Untertiteln: `[Wort]` → Lücke mit Hilfe erlaubt
(sät eine Solution-Hilfestufe, Penalty 0 — analog zur V1-Migration),
`{Wort}` → Lücke ohne Hilfe. Unbrauchbare Marker (unaufgelöst, leer,
zeilenübergreifend) bleiben literal. Im Editor als Checkbox im Import-Panel.

Begründung: Import (WebVTT/SubRip), Timeline-Strip, Lücken-/Hint-Editing und
Medien existieren, aber als lose, technische Bausteine. Ein durchgängiges,
workflow-orientiertes „Studio" ist noch NICHT umgesetzt und verdient ein eigenes,
sorgfältig designtes Arbeitspaket.

### Zielbild
Ein Lehrender erstellt eine Übung end-to-end, ohne technische Zwischenschritte:
Medium wählen → Transkript erzeugen/importieren → Cues an der Medienzeit
ausrichten → Lücken markieren → Hinweise setzen → Vorschau → veröffentlichen.

### Leitende Workflows (zu designen)
1. **Medium zuerst.** Upload/URL/Provider wählen; Player + Waveform erscheinen.
2. **Transkript.** Import (VTT/SRT) ODER manuelles Anlegen; Cue-Blöcke erscheinen
   auf der Timeline.
3. **Timing feinjustieren.** Cue-Grenzen per Drag auf der Waveform/Timeline;
   „aktuelle Position übernehmen"; Snap an Wortgrenzen.
4. **Lücken markieren.** Wort im Cue markieren → Lücke; Live-Vorschau der
   maskierten Ansicht; Re-Sync der Lückenpositionen bei Transkript-Edits.
5. **Hinweise/Bewertung.** Pro Lücke Algorithmus/Varianten/gestufte Hinweise;
   verständliche Beschriftungen statt technischer Begriffe.
6. **Vorschau & Veröffentlichen.** Lernenden-Vorschau; Validierung mit klaren,
   verlinkten Problemmeldungen; publizieren.

### Umfang / Bausteine (inkrementell)
- Waveform-Darstellung (Web Audio API) unter der Timeline.
- Drag-Resize der Cue-Grenzen; Tastatur-Feinjustierung.
- Live-Re-Sync der Lücken-Offsets bei Transkript-Änderung (behebt die aktuelle
  UTF-16/BMP- und Stale-Offset-Problematik).
- Geführtes Onboarding (leerer Zustand → „Womit starten?").
- Barrierefreiheit (Tastaturbedienung, ARIA, Fokusführung).
- Inline-Vorschau der maskierten Lernenden-Ansicht.
- Autosave + klare Speicher-/Konflikt-Rückmeldung (Revision).

### Design-/Test-Herangehen
- UX zuerst als Klick-Flows/Skizzen entlang der obigen Workflows festlegen.
- Behat-`@javascript`-E2E je Workflow-Schritt (in der CI ausführbar).
- AMD-Module modular (Player, Timeline/Waveform, Cue-/Lücken-Editor getrennt).

---

## D. Vorgeschlagene Reihenfolge
1. **P0** Editor-Ladefehler beheben (blockiert alles Weitere).
2. **P1** Anbieter-Dropdown + Sprach-Vorauswahl (+ Default) — kleine, sichtbare
   Wins.
3. **P2** Admin-Sprachliste.
4. **P3** Word/ODF-Export (minimaler DOCX/ODT-Writer).
5. **AP-D** Subtitle Studio & Authoring-UX als eigenes, größeres Paket, entlang
   der Workflows designt.

---

## E. Weg zur Beta — Checkliste (für die nächste Sitzung)

Stand: nach Session 005 ist der Code funktional komplett und die CI grün
(alpha.70). Es gibt **keinen großen technischen Blocker** mehr. Vor dem Sprung
von Alpha auf Beta stehen aber Abschluss, Härtung und Vollständigkeit an. Diese
Liste ist die Arbeitsgrundlage für die nächste(n) Sitzung(en); Reihenfolge grob
von „schnell erledigt" nach „großes Paket".

### Review-P0/P1 + tote Caps — **[ERLEDIGT alpha.71, Session 006]**
Vor E0/E1 wurde die Codehärtung aus dem technischen Review abgearbeitet (die
Fixes lagen bereits uncommittet über alpha.70 vor, waren aber ungetestet):
- P0 Transcript: Worksheet (maskiert) vs. Solution (Volltext, neue Cap
  `mod/elang:exportsolution`) — mit Regressionstests über alle Formate.
- P0 useregex serverseitig durchgesetzt; P1 Domain-Validierung (penalty∈[0,1],
  isregex∈{0,1}, Regex-Compile, Algorithmus/Hinttyp-Enum) + Score-Clamp — getestet.
- Tote Caps `deleteattempts`/`exportreports` implementiert (Dataformat-Export +
  bestätigter Lösch-Flow). `package-lock.json` committet, Lang 229/229.

### Review-Skalierung/Datenintegrität — **[ERLEDIGT alpha.72, Session 007]**
Die Skalierungs-/Integritätsbefunde des Reviews (Punkte 4–8, 18):
- N+1 beseitigt in `version_validator` (Publish, 3 Queries) und
  `version_manager::copy_version_content` (Draft-Copy, 4 Queries).
- Report paginiert (`count_for_activity` + `list_for_activity` page/perpage,
  `paging_bar`); Export bleibt bewusst ungeteilt.
- V1-Detector DB-seitig limitiert (`pending_activity_ids($limit)` + Single-JOIN)
  + `count_pending_activities()`; Task holt einen begrenzten Block.
- Draft-Invariante reconciliert (akkurater Klassenkommentar; `get_or_create_draft`
  toleriert einen Zweit-Draft). Mit Query-Budget-/Pagination-/Limit-Tests.

### E0 — Werkzeug-/Infrastruktur-Nachzug — **[ERLEDIGT alpha.71]**
- Vollständiges `makefile` von mod_vimipad übernommen und an elang-Pfade
  (`js/vendor/react/editor.bundle.js`, `mod/elang`) sowie elang-benannte
  Playwright/JMeter/k6-Ziele angeglichen. Die Load-/Playwright-Ziele stehen
  bereit für die E5/E6-Pakete (Testdateien folgen dort).

### E1 — Kleinreste — **[ERLEDIGT alpha.71]**
- **Player-Meldung**: `view.php` mountet den Player nur bei publizierter Version
  und zeigt sonst `player:nocontent` (String neu ergänzt).
- **P1.3 — Sprache NICHT `required`.** Bestätigt, bleibt optional.
- **Report-Gruppenfilter**: Sichtprüfung vorhanden (SEPARATEGROUPS +
  `moodle/site:accessallgroups` in `report.php`).

### E2 — AP-D „Subtitle Studio & Authoring-UX" **vor Beta**
- Das große, eigenständige Paket (siehe Abschnitt C). Fundament (React/TS,
  gebündelt) steht; die workflow-orientierte Autoren-Erfahrung (Waveform via Web
  Audio API, Drag/Snap-Timing, Live-Re-Sync der Lücken-Offsets, Autosave,
  geführtes Onboarding) ist noch zu bauen. Start mit einem Scoping-/Design-
  Schritt (Klick-Flows), dann inkrementell.

### E3 — Codehärtung **vor Beta**
- Fehlerpfade, Randfälle und Eingabevalidierung systematisch durchgehen
  (insbesondere die Authoring-Write-Services und die Migration).
- Defensive Prüfungen dort, wo bisher „happy path" angenommen wird.
- Konsistente, nutzerfreundliche Fehlermeldungen statt Exceptions im UI.

### E4 — Behat-Testabdeckung **vor Beta**
- Aktuell nur wenige Feature-Dateien. Für Beta die Kernpfade durchgängig als
  `@javascript`-Szenarien abdecken: Übung anlegen → bearbeiten → veröffentlichen
  → als Lernende bearbeiten → Report ansehen → Transkript exportieren.
- Gruppenmodus-Pfade und die Autoren-UI (Timeline, Lücken-Editor) mit einbeziehen.

### E5 — Sichtfunktions- und Barrierearmutsprüfung **vor Beta**
- **Sichtfunktionsprüfung (visuelle Regression / E2E-Klickprüfung):** entweder
  eine dokumentierte manuelle Prüf-Anleitung bereitstellen **oder** automatisierte
  **Playwright**-Tests bereitstellen (bevorzugt), die die tatsächliche Darstellung
  und Interaktion von Player und Autorentool abdecken.
- **Barrierearmut (Accessibility):** Tastaturbedienbarkeit, ARIA-Rollen/-Labels,
  Kontraste, Screenreader-Fluss für Player und Autorentool prüfen. Anleitung
  bzw. automatisierte a11y-Checks (z. B. axe in Playwright) bereitstellen.

### E6 — Lasttests **vor Beta**
- Last-/Performance-Tests bereitstellen: **jMeter**-Testplan **und** **k6**-Skripte
  für die wesentlichen Endpunkte (Player-Datenabruf, Antwort-/Grading-Roundtrip,
  Autoren-Speichern/Veröffentlichen). Ziel: Verhalten unter realistischer und
  unter Spitzenlast dokumentieren; Engpässe identifizieren.

### E7 — Migrationspfad prüfen (**automatische Testabdeckung!**) **vor Beta**
- Der V1→V2-Migrationspfad muss auf einem **echten Upgrade** verifiziert werden,
  nicht nur auf PHPUnit-Frischinstalls (Schema-Kollisionen in `upgrade.php`
  tauchen nur auf realen Admin-UI-Upgrades auf).
- **Automatisierte Testabdeckung** dafür bereitstellen: ein reproduzierbarer,
  in der CI (oder scriptgestützt) laufender Durchlauf, der von einer echten
  V1-Instanz über die Zwischenversionen bis V2 (alpha.70+) migriert und die
  Datenintegrität (cuekey/gapkey-Stabilität, Versionen, Media, Ergebnisse)
  assertiert.

### Bewusst NICHT Beta-blockierend
- OAuth/login-gated Provider-Support: eigenes, kostenpflichtiges Subplugin,
  ausdrücklich nach Beta.
