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
