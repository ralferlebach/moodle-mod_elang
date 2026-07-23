# Blueprint kompakt — `mod_elang` 2.0

Arbeitsfassung. Verbindlich ist `Lastenheft_Pflichtenheft_Blueprint.md`.

---

## Rahmen

| | |
| --- | --- |
| Komponente | `mod_elang`, Verzeichnis `mod/elang/` |
| Zielplattform | Moodle **4.5 LTS – 5.3 LTS** · PHP **8.1 – 8.4** · Releaseziel 5.3 (5. Okt. 2026) |
| Lizenz | GNU GPL v3 or later · Autor Ralf Erlebach, 2026 |
| Vorgehen | kompatible **Neuentwicklung**, keine Modernisierung von V1 |
| Abhängigkeiten | keine |

## Drei unverrückbare Festlegungen

1. **Kompatible Neuentwicklung.** Fachlich kompatibel (Formate, Bewertung,
   Migration), technisch neu.
2. **Nur dokumentierte Moodle-APIs.** Keine eigenen Endpunkte, kein eigenes
   HTML-Dokument, kein Fremd-Frontend-Framework.
3. **Kein Release ohne** vollständige Privacy-API, CSRF-/Capability-Absicherung
   und verlustfreie Inhaltsbearbeitung.

## Die vier P0-Blocker und ihre Auflösung

| Blocker | Auflösung |
| --- | --- |
| Speichern löscht Lernfortschritt | versionierte Übungsdefinition, Versuche hängen an einer Version |
| AJAX ohne Sesskey, Reset per GET | External Functions + `core/ajax`, destruktives nur per bestätigtem POST |
| keine Privacy-API | vollständiger Provider, normalisierte Antwortdaten, keine Lösungen in Logs |
| Enyo-Frontend außerhalb Moodles | Templates, Renderer, native ES-Module im Seitenrahmen |

## Datenmodell

```
elang → elang_version → elang_cue → elang_gap → elang_gapanswer
                                             └→ elang_gaphint
elang → elang_attempt (verweist auf elang_version) → elang_response
```

Eine Zeile je fachlichem Objekt. Keine JSON-Blobs. Aggregierte Zähler auf
`elang_attempt` tragen Fortschritt, Bewertung und Abschluss.
`elang_help` und `elang_check` entfallen.

## Schichten

| Schicht | Umsetzung |
| --- | --- |
| Domain | `answer_evaluator`, `attempt_manager`, `version_manager` |
| Persistenz | kleine Repository-Klassen mit gezielten Queries |
| API | External Functions in `classes/external/`, `db/services.php`, `core/ajax` |
| Ausgabe | Renderables, Renderer, Mustache |
| JavaScript | native ES-Module unter `amd/src/` |
| Reporting | Report Builder (Entities + System Reports) |
| Export | Dataformat API (CSV/XLSX/ODS/JSON), eigene Dokument-Exporter (PDF/DOCX/ODT) |
| Hintergrund | Ad-hoc-Tasks für Migration und schwere Exporte |

## Plattformspanne 4.5 – 5.3 — was daraus folgt

- Code strikt auf **PHP 8.1** beschränken (keine `readonly`-Klassen, keine DNF-Typen,
  keine typisierten Klassenkonstanten, keine Property Hooks).
- Versionsabhängige APIs über **Fähigkeitsprüfung**, nie über Versionsvergleiche.
- **Zwei Kursintegrationen ausliefern:** `index.php` (4.5) und
  `classes/courseformat/overview.php` (ab 5.0).
- KI-Assistenz prüft Subsystem **und** Aktion einzeln — 4.5 kennt nur
  `generate_text` und `generate_image`.
- PHPUnit-Metadaten einheitlich als `@covers`-Annotation, solange 4.5 in der Matrix ist.
- CI stichprobenartig: 4.5/8.1 · 4.5/8.3 · 5.0/8.2 · 5.2/8.3 · 5.2/8.4 · 5.3-dev/8.4
  (letzteres nicht blockierend).

## Verbindlicher 2.1-Umfang

| Nr. | Vorhaben | Vorleistung in 2.0 |
| --- | --- | --- |
| 2.1-1 | Regelbasierte Lückenerzeugung | keine |
| 2.1-2 | Antwortvariante nachträglich anerkennen + Neubewertung | **`cuekey`/`gapkey`, dauerhaft gespeicherter Antworttext, zustandsfreie Bewertung** |
| 2.1-3 | Zeichenleiste für Sonderzeichen | Sprachfeld der Aktivität |
| 2.1-4 | Übersetzungsspur als Hilfestufe | erweiterbares Hilfestufenmodell |
| 2.1-5 | Blindmodus und Audio-only | keine |

Summe 2.1: **4,2–6,1 PW**.

## Zusatzanforderungen — Kurzstand

| Anforderung | Stand |
| --- | --- |
| Excel / ODS / JSON zusätzlich zu CSV | Kernfunktion, kein Eigencode |
| Bebildertes Arbeitsblatt als PDF | machbar (TCPDF); Standbilder browserseitig zur Autorenzeit |
| Bearbeitbar als DOCX / ODT | machbar über `ZipArchive` + Vorlage, keine Fremdbibliothek |
| SW-SVG-Icon, Zweck „Prüfen" (pink) | umgesetzt in 2.0.0-alpha.1 |
| YouTube-Untertitelimport | nur eigene Kanalvideos (OAuth 2) — 2.1 |
| KI-Untertitel aus Video | Kernaktion im KI-Subsystem fehlt — zurückgestellt |
| KI-Videogenerierung | nicht empfohlen |

## Phasen

1. Spezifikation, Skelett, CI, Dokumentation *(erledigt: 2.0.0-alpha.1)*
2. Datenmodell, Domain, Completion, Gradebook, Migration
3. Lernendenoberfläche
4. Autoreneditor, Reporting, Exporte
5. Härtung, Privacy, Backup, Audits, Revalidierung gegen Moodle 5.3

Aufwand gesamt 2.0: **21–36 Personenwochen**, MVP **12–18**. Version 2.1: **4,2–6,1**.

## Fallstricke aus dem Bestand

- Gap-Zähler-Fehler in V1 (`$i++` innerhalb der Gap-Schleife) → Cues bei der
  Migration über die Startzeit neu nummerieren.
- Verwaiste Antwortdatensätze aus dem V1-Löschverhalten → Bericht statt stiller
  Verlust.
- `[Antwort(Link)]`-URLs → eigenes validiertes Feld, nie roh ins `href`.
- Reguläre Ausdrücke in Antwortvarianten → eigene Capability, Zeit- und
  Längenbegrenzung.
- Standbilder aus eingebetteten Fremdvideos sind technisch unerreichbar.
