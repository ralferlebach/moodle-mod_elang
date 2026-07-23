# Technische Machbarkeit der Zusatz- und Roadmap-Anforderungen

**Stand:** 23. Juli 2026 · geprüft gegen die Zielspanne **Moodle 4.5 LTS bis 5.3**.
Moodle 5.2 ist aktuell stabil; Moodle 5.3 ist angekündigt (Code Freeze 24. August
2026, LTS-Release 5. Oktober 2026, mindestens PHP 8.3, PHP 8.4 unterstützt).

> **Hinweis zur Plattformspanne:** Alle unten geprüften Funktionen müssen bereits
> auf **Moodle 4.5** verfügbar sein, sonst müssen sie sich auf 4.5 sauber
> ausblenden. Wo das relevant ist, steht es beim jeweiligen Punkt.

Zusammenfassung:

| # | Anforderung | Ergebnis | Aufwand |
| --- | --- | --- | ---: |
| 1 | Export zusätzlich als Excel, ODS und JSON | **ohne Einschränkung machbar**, Kernfunktion | 0,5 PW |
| 2 | Bebilderter Transkriptexport als PDF | **machbar** mit Kernbibliothek | 1–1,5 PW |
| 3 | Bearbeitbarer Export als Word (DOCX) und OpenOffice (ODT) | **machbar**, aber Eigenimplementierung nötig | 1,5–2,5 PW |
| 4 | Moodle-konformes SW-SVG-Icon, Zweck „Prüfen" (pink) | **machbar**, trivial | < 0,5 PW |
| 5 | YouTube-Untertitelimport | **nur für eigene Kanalvideos machbar** | 1–1,5 PW |
| 6 | KI-Untertitelerzeugung aus Video über die Moodle-KI-API | **derzeit nicht abbildbar** — Kernaktion fehlt | siehe Kap. 6 |
| 7 | KI-Videogenerierung aus Transkript | **nicht empfohlen** | siehe Kap. 7 |

---

## 1. Export als CSV, Excel, ODS und JSON

**Ergebnis: ohne Einschränkung machbar; es entsteht kein formatspezifischer Code.**

Moodle besitzt seit 3.1 den Plugintyp `dataformat` und liefert die Formate **CSV,
Excel (XLSX), ODS, JSON, HTML und PDF** mit — auf **Moodle 4.5 ebenso wie auf 5.3**,
also ohne Fallunterscheidung über die gesamte Zielspanne. Die Dataformat API übernimmt Auswahl,
Streaming und Dateiauslieferung vollständig.

Umsetzung:

- Auswahlfeld über den Core-Renderer (`download_dataformat_selector()`)
  beziehungsweise automatisch im Report Builder;
- Ausgabe über `\core\dataformat::download_data()` mit Spaltenschlüsseln,
  Sprachstrings und einem Iterator/Recordset über die Berichtsdaten;
- da wir das Reporting ohnehin auf Report Builder aufsetzen, ist der
  Formatwähler bereits vorhanden.

Zu beachten:

1. **Spaltenschlüssel vs. Beschriftungen.** Menschlich gelesene Formate (Excel,
   ODS) nutzen die Sprachstrings als Kopfzeile, maschinenlesbare (JSON) die
   Schlüsselnamen. Für stabile Weiterverarbeitung werden für JSON bewusst feste,
   nicht lokalisierte Schlüssel definiert.
2. **Formel-Injektion.** Weder CSV- noch Tabellenkalkulationsexport neutralisiert
   automatisch Zellinhalte, die mit `=`, `+`, `-`, `@`, Tab oder CR beginnen.
   Da Lernendenantworten Freitext sind, ist das ein realer Angriffsweg. Wir
   neutralisieren solche Präfixe zentral im Export-Mapper (Pflichtenheft P7).
3. **Speicher.** Export ausschließlich über Recordsets, nie über
   `get_records()` auf den Gesamtbestand.
4. **Datenminimierung.** E-Mail-Adressen sind keine Standardspalte; der Export
   personenbezogener Spalten verlangt `mod/elang:exportreports`.

Optional ohne Zusatzaufwand ebenfalls verfügbar: HTML und PDF als *Tabellen*format
(nicht zu verwechseln mit dem Arbeitsblatt-PDF aus Kapitel 2).

---

## 2. Bebilderter Transkriptexport als PDF

**Ergebnis: machbar.** Grundlage ist Issue #60 des V1-Repositorys:

> Standbilder aus dem Video (Vorschlag: alle 30 Sekunden, einstellbar) links, die
> Cues dieses Intervalls rechts, jeder Cue in einer eigenen Zeile. Lücken sollen
> sich der Wortlänge anpassen: 2 Unterstriche je Buchstabe plus 2 zusätzliche —
> einstellbar, weil ein Längenhinweis nicht immer erwünscht ist.

### 2.1 PDF-Erzeugung

Moodle liefert TCPDF mit und kapselt es in `lib/pdflib.php` (Klasse `pdf`).
Zweispaltige Layouts mit eingebetteten Rasterbildern, Unicode-Schriften und
Seitenumbrüchen sind damit Standardfunktionalität. Kein Fremdcode nötig.

### 2.2 Woher kommen die Standbilder?

Das ist der eigentlich kritische Punkt: Moodle bringt **kein** Werkzeug zur
Videobildextraktion mit, und `ffmpeg` gehört nicht zu den von Moodle vorausgesetzten
externen Programmen. Drei Strategien, absteigend nach Empfehlung:

**A. Erfassung im Browser zur Autorenzeit — empfohlener Standardweg**

Ein `<video>`-Element wird auf die gewünschten Zeitpunkte gesetzt und über
`<canvas>.drawImage()` / `toBlob()` ausgelesen. Die Bilder werden einmalig beim
Veröffentlichen einer Version erzeugt und im Dateibereich der Aktivität abgelegt,
adressiert über den `contenthash` der Version.

- Vorteile: keine Serverabhängigkeit, keine Rechenlast im Request, Bilder werden
  genau einmal je Version erzeugt, funktioniert auf jedem Hosting.
- Voraussetzung: das Video muss **gleichursprünglich** ausgeliefert werden — also
  eine Moodle-Datei über `pluginfile.php` oder eine URL mit passenden
  CORS-Kopfzeilen. Andernfalls wird das Canvas „tainted" und `toBlob()` schlägt
  fehl.
- **Nicht möglich für eingebettete Fremdvideos** (YouTube, Vimeo): dort läuft die
  Wiedergabe in einem fremden `iframe`, auf dessen Bildinhalt kein Zugriff besteht.
  Das ist eine harte Grenze der Browser-Sicherheitsarchitektur, keine
  Implementierungsfrage.

**B. Serverseitig über `ffmpeg` — optionale Ausbaustufe**

Nur aktiv, wenn in den Plugin-Einstellungen ein Pfad zu `ffmpeg` hinterlegt ist.
Ausführung ausschließlich in einem Ad-hoc-Task, nie im Request. Sinnvoll für
Installationen mit vielen Bestandsvideos, die nachträglich bebildert werden sollen.

**C. Manuelle Bilder — immer verfügbarer Rückfallweg**

Lehrende laden je Intervall ein eigenes Bild hoch, etwa um bewusst eine bestimmte
Szene zu zeigen. Fehlt ein Bild, bleibt die Zelle leer; das Arbeitsblatt bleibt
gültig.

### 2.3 Lückendarstellung

Vier Modi, pro Export wählbar (siehe Blueprint Kap. 12): `none`, `proportional`
(die aus Issue #60 gewünschte Regel `2 × Zeichenanzahl + 2`), `boxed` und
`solution` (Lösungsblatt). Der Modus ist eine Exportoption, keine Eigenschaft der
Übung — dasselbe Material lässt sich damit als Aufgaben- und als Lösungsblatt
ausgeben.

### 2.4 Betrieb

Arbeitsblätter ohne personenbezogene Anteile werden über den Content-Hash
gecacht. Nutzerspezifische Exporte (mit den eigenen Antworten) werden nur auf
ausdrückliche Anforderung erzeugt, laufen über einen Ad-hoc-Task und werden nicht
gecacht. Die V1-Praxis, PDF und VTT bei **jedem** Abruf neu zu erzeugen, wird
nicht fortgeführt.

---

## 3. Bearbeitbarer Export als Word (DOCX) und OpenOffice (ODT)

**Ergebnis: machbar, aber ohne Kernunterstützung — hier entsteht echter
Implementierungsaufwand.**

Moodle liefert **keinen** Writer für DOCX oder ODT mit. Verfügbar sind TCPDF
(PDF) und PhpSpreadsheet (XLSX/ODS, also Tabellen, keine Textdokumente).

Bewertete Wege:

| Weg | Bewertung |
| --- | --- |
| **ZIP-Paket aus Vorlage über `ZipArchive`** | **empfohlen.** DOCX und ODT sind ZIP-Archive mit XML-Inhalt. Eine geprüfte Minimalvorlage wird mit Mustache gefüllt, Bilder werden als `word/media/*` bzw. `Pictures/*` eingefügt und in `[Content_Types].xml`/`document.xml.rels` bzw. `META-INF/manifest.xml` registriert. `ext-zip` ist für Moodle ohnehin Voraussetzung. Keine neue Abhängigkeit, volle Kontrolle, gut testbar. |
| Fremdbibliothek (z. B. PHPWord) | Zusätzliche ausgelieferte Abhängigkeit mit eigener Pflege- und Sicherheitslast, `thirdpartylibs.xml`-Eintrag, Review-Aufwand. Nur sinnvoll, wenn deutlich komplexere Dokumente entstehen sollen. |
| HTML mit `application/msword` ausliefern | **abgelehnt.** Erzeugt kein gültiges Word-Dokument, öffnet je nach Version mit Warnungen, ist in LibreOffice unzuverlässig. |
| RTF | Von Word und LibreOffice geöffnet, aber Bilder müssen hex-codiert eingebettet werden, Layoutkontrolle ist schlecht, Ergebnis wirkt veraltet. Nur als Notlösung. |
| ODT erzeugen und über die Moodle-Dateikonverter in DOCX wandeln | Setzt einen konfigurierten Konverter (LibreOffice/Drittdienst) voraus, den viele Installationen nicht haben. Als *optionaler Zusatzweg* sinnvoll, nicht als Standard. |

Empfehlung: **ODT und DOCX beide nativ** über `ZipArchive` erzeugen. Beide
Formate teilen sich Inhaltsmodell, Bildbeschaffung und Lückenlogik mit dem
PDF-Export; nur die Serialisierung unterscheidet sich. Deshalb liegt der
Zusatzaufwand für das zweite Textformat deutlich unter dem für das erste.

Prüfpunkte für die Abnahme:

- Öffnen ohne Reparaturhinweis in Microsoft Word **und** LibreOffice Writer;
- Bilder korrekt eingebettet, nicht verlinkt;
- Umlaute, Akzente und nichtlateinische Schriften korrekt (UTF-8, eingebettete
  Schriftverweise);
- Lücken bleiben nach dem Öffnen bearbeitbar (Standardweg: Unterstrichfolgen im
  Fließtext; Formularfelder sind bewusst **nicht** vorgesehen, weil sie in Word und
  LibreOffice unterschiedlich behandelt werden);
- Wohlgeformtheit der Paket-XML wird im Test automatisiert geprüft.

---

## 4. Icon und Aktivitätszweck

**Ergebnis: machbar, trivial.**

Beides ist über die gesamte Zielspanne verfügbar: `monologo.svg` und
`FEATURE_MOD_PURPOSE` seit Moodle 4.0, der Branding-Callback seit 4.4 — also auch
auf der 4.5-Untergrenze.

- **Icon:** Aktivitäts-Icons sind seit Moodle 4.0 einfarbige SVG-Dateien unter
  `mod/PLUGINNAME/pix/monologo.svg`. Konvention für die Strichfarbe ist `#212529`;
  Moodle färbt das Icon per CSS um. Ein PNG-Fallback (`monologo.png`, 24×24) wird
  mitgeliefert. Die alten V1-Dateien `icon.gif`, `icon.ico`, `icon.png` und
  `icon.svg` entfallen.
- **Zweck:** `elang_supports(FEATURE_MOD_PURPOSE)` liefert
  `MOD_PURPOSE_ASSESSMENT`. Die Hintergrundfarbe kommt aus der Theme-Variablen
  `$activity-icon-assessment-bg` (in der aktuellen Boost-Variablenliste `#f90086`,
  also der gewünschte Pink-Ton). Die Farbe wird **nicht** im Plugin gesetzt —
  Themes dürfen sie überschreiben.
- **Einfärbung erlauben:** `elang_is_branded()` liefert `false`, sonst würde
  Moodle das Icon unverändert und ohne Zweckhintergrund darstellen.

Anmerkung zur Zuordnung: Zur Wahl stünde auch `MOD_PURPOSE_INTERACTIVECONTENT`.
Da `mod_elang` 2.0 bewertet, ins Gradebook schreibt und Versuche führt, ist
„Prüfen" die konsistentere Zuordnung — dieselbe wie bei `mod_quiz` und
`mod_assign`. Die Entscheidung ist eine Zeile und jederzeit revidierbar.

---

## 5. Import von YouTube-Untertiteln

**Ergebnis: nur für Videos machbar, die der anmeldenden Person gehören oder die
sie bearbeiten darf. Für beliebige fremde Videos nicht.**

Sachstand:

- Die **YouTube Data API v3** stellt `captions.list` und `captions.download`
  bereit. Beide verlangen OAuth 2.0; `captions.download` verlangt ausdrücklich
  **Bearbeitungsrecht am Video**. Anfragen zu fremden Videos scheitern mit einem
  Berechtigungsfehler. Ein reiner API-Key genügt nicht.
- Kostenrahmen: `captions.list` 50, `captions.download` 200 Kontingenteinheiten
  bei standardmäßig 10.000 Einheiten pro Tag — also rund 50 Downloads täglich.
- Formate: SRT, VTT, SBV, SCC, TTML — VTT und SRT passen unmittelbar zu unserem
  Importer.
- Das inoffizielle `timedtext`-Endpunkt-Scraping ist **kein** gangbarer Weg:
  es widerspricht den YouTube-Nutzungsbedingungen, ist nicht dokumentiert und
  bricht regelmäßig. Es wird ausgeschlossen.

Umsetzbare Variante für 2.1:

1. Administration hinterlegt einen Google-OAuth-2-Dienst über das
   Moodle-OAuth-2-Subsystem (`\core\oauth2`) mit dem Scope
   `youtube.force-ssl`.
2. Lehrende verbinden ihr eigenes YouTube-Konto und wählen ein Video ihres Kanals.
3. Das Plugin holt die Untertitelspur als VTT und übergibt sie an den normalen
   Importweg (Vorschau + Validierung + Cue-/Lückenerzeugung).
4. Fehlt die Berechtigung, wird das klar benannt und auf den Dateiimport verwiesen.

Für alle anderen Fälle bleibt der pragmatische Weg: Untertitel in YouTube Studio
exportieren und die Datei hochladen. Das ist in der Praxis ein Klick mehr und
rechtlich unproblematisch.

---

## 6. Automatische Untertitelerzeugung aus Video über die Moodle-KI-API

**Ergebnis: mit dem Moodle-KI-Subsystem im heutigen Stand nicht abbildbar.**

Sachstand:

- Das KI-Subsystem gibt es seit **Moodle 4.5** — allerdings dort nur mit den
  Aktionen `generate_text` und `generate_image`; `summarise_text` und
  `explain_text` kamen mit 5.0 hinzu, die Steuerung je Kurs und Aktivität mit 5.1.
  Für die Zielspanne heißt das: jede KI-Funktion prüft Subsystem **und** benötigte
  Aktion einzeln und blendet sich sonst aus.
- Das KI-Subsystem trennt **Placements** (Oberfläche), **Actions** (was getan
  wird) und **Provider** (welcher Dienst es tut). Genau diese Trennung ist der
  Grund, das Subsystem zu nutzen: providerneutral, mit zentraler Richtlinie,
  Nutzungsprotokoll und Berechtigungen.
- Die verfügbaren Aktionen sind `generate_text`, `generate_image`,
  `summarise_text` und `explain_text`. Alle arbeiten auf **Text** (bzw. erzeugen
  ein Bild aus Text). Eine Aktion für Spracherkennung oder für Audio-/Videoeingabe
  existiert nicht.
- Aktionen sind Klassen im Namensraum `\core_ai\aiactions` und erben von
  `\core_ai\aiactions\base`. Sie sind damit **Kernbestandteil**, nicht
  Plugin-Erweiterungspunkt. Provider erklären lediglich, welche der vorhandenen
  Aktionen sie unterstützen.

Daraus folgt: Eine Untertitelgenerierung „über die Moodle-KI-API" setzt zwingend
eine neue Kernaktion voraus (etwa `transcribe_media`), samt Provider-Unterstützung.

Empfohlenes Vorgehen:

1. **Schnittstelle vorbereiten, Umsetzung entkoppeln.** `mod_elang` definiert
   intern ein schmales Interface `transcription_provider` mit einer einzigen
   Methode. Der Importweg (Vorschau, Validierung, Cue-Erzeugung) wird bereits in
   2.1 so gebaut, dass er eine maschinell erzeugte Spur genauso behandelt wie eine
   hochgeladene.
2. **Kernunterstützung anstoßen.** Ein Tracker-Issue für eine Transkriptionsaktion
   im KI-Subsystem einreichen und referenzieren.
3. **Kein Eigenweg am Subsystem vorbei im Standardpaket.** Ein direkt
   konfigurierter Fremdendpunkt (etwa eine selbst betriebene Whisper-Instanz)
   ist technisch trivial, umgeht aber Richtlinienzustimmung, Nutzungsprotokoll und
   Provider-Governance des Kerns. Wenn er kommt, dann als **separates, optionales
   Plugin**, ausdrücklich deaktiviert per Voreinstellung und mit eigener
   Datenschutzdokumentation.
4. **Nebenbedingung nicht übersehen:** Für Spracherkennung muss zuerst die
   Audiospur aus dem Video gelöst werden — dieselbe `ffmpeg`-Abhängigkeit wie in
   Kapitel 2.2, Weg B.

**Was dagegen heute schon geht** und deshalb für 2.2 vorgesehen ist: KI-gestützte
**Autorenassistenz auf Textbasis** über `generate_text` — Lückenvorschläge aus
einem vorhandenen Transkript, Formulierung von Hinweistexten, Vorschläge für
weitere akzeptable Antwortvarianten, Schwierigkeitseinschätzung,
Segmentzusammenfassungen. Immer mit expliziter Freigabe und abschließender
Kontrolle durch die Lehrperson.

---

## 7. Videogenerierung aus Transkript per KI

**Ergebnis: nicht empfohlen.**

- Es gibt im KI-Subsystem keine Aktion für Video- oder Sprachsynthese; es gilt
  dieselbe Kernbeschränkung wie in Kapitel 6, nur ausgeprägter.
- Videogenerierung ist rechen- und kostenintensiv, langlaufend und in der
  Ergebnisqualität schwer kontrollierbar — schlecht vereinbar mit einem
  LMS-Request-Modell und mit institutionellen Budgets.
- Didaktisch ist der Ertrag für eine Hörverstehensübung gering: der Lerngegenstand
  ist authentisches gesprochenes Material. Synthetisches Video mit synthetischer
  Stimme trainiert etwas anderes als natürliche Rede.

Falls der Bedarf tatsächlich „aus einem Sprechtext ein nutzbares Übungsmedium
machen" lautet, ist der ungleich günstigere Weg: **Audio statt Video** — eine
Sprachsynthese erzeugt die Tonspur, das Transkript liefert die Zeitcodes ohnehin
mit. Auch das setzt eine Kernaktion für Sprachsynthese voraus und bleibt deshalb
zunächst zurückgestellt; als Vorstufe genügt der bereits vorgesehene
**Audio-only-Modus** für hochgeladene Tondateien.
