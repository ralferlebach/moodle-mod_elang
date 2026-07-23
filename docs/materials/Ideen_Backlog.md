# Ideen-Backlog

**Stand:** 23. Juli 2026 · Ergänzung zu `Lastenheft_Pflichtenheft_Blueprint.md`

Ideen jenseits des festgelegten 2.0-Umfangs, bewertet nach didaktischem Nutzen und
Aufwand. Ideen, die bereits im Pflichtenheft stehen, tauchen hier nicht auf.

**Zielversion** ist ein Vorschlag — mit einer Ausnahme: Die fünf mit **✅ 2.1**
markierten Vorhaben sind seit dem 23.07.2026 **verbindlich zugesagter Bestandteil
von Version 2.1** und in `Lastenheft_Pflichtenheft_Blueprint.md`, Kap. 19,
spezifiziert.

Legende Aufwand: **S** ≤ 0,5 PW · **M** 0,5–2 PW · **L** 2–5 PW · **XL** > 5 PW

---

## A. Aufgaben- und Übungsformen

| # | Idee | Nutzen | Aufwand | Ziel |
| --- | --- | --- | --- | --- |
| A1 | **Mehrere Lückentypen**: Freitext, Auswahlliste je Lücke, Wortspeicher mit Distraktoren, „richtiges Wort anklicken" | hoch | M | 2.1 |
| A2 | **Diktatmodus** — das ganze Segment wird getippt, nicht nur einzelne Lücken; Auswertung wortweise mit Ausrichtung auf die Musterlösung | hoch | L | 2.1 |
| A3 | **Zweite Untertitelspur als Hilfestufe** — Übersetzung in die Erstsprache oder vereinfachte Fassung, auf Anforderung einblendbar. Fügt sich nahtlos in das Hilfestufenmodell ein | hoch | M | **✅ 2.1** |
| A4 | **Reihenfolge-Aufgaben** — Segmente in die richtige Reihenfolge bringen (Hörverstehen global statt lückenweise) | mittel | M | 2.2 |
| A5 | **Segmentweiser Blindmodus** — Untertitel erst nach dem Antwortversuch sichtbar; erzwingt echtes Hören statt Mitlesen | hoch | S | **✅ 2.1** |
| A6 | **Audio-only-Modus** — Video ausgeblendet, nur Ton; für reines Hörverstehen und für schmale Bandbreite | hoch | S | **✅ 2.1** (ggf. schon 2.0) |

## B. Autorenwerkzeuge

| # | Idee | Nutzen | Aufwand | Ziel |
| --- | --- | --- | --- | --- |
| B1 | **Regelbasierte Lückenerzeugung** — jedes n-te Wort, nur Wörter ab Länge x, nur bestimmte Wortarten, nur Wörter außerhalb einer Häufigkeitsliste. Deterministisch, ohne KI, sofort nachvollziehbar | **sehr hoch** | M | **✅ 2.1** |
| B2 | **Wortlisten- und Niveaubezug (GER/CEFR)** — Lücken nach Niveaustufe markieren, Übung „auf A2 begrenzen" | hoch | M | 2.2 |
| B3 | **Antwortvariante nachträglich anerkennen** — Lehrende sehen häufige Fehlantworten, erklären eine davon als korrekt, und alle betroffenen Versuche werden neu bewertet. Der klassische Praxisfall bei Freitextlücken | **sehr hoch** | M | **✅ 2.1** |
| B4 | **Übungsvorlagen** — Voreinstellungen für Bewertungsprofil, Hilfestufen und Abschluss als benannte Vorlage, kurstweit oder siteweit | mittel | M | 2.2 |
| B5 | **Wiederverwendung über Kurse** — Übungsdefinition in die Content Bank auslagern und in mehreren Kursen instanziieren | mittel | L | offen |
| B6 | **Import weiterer Quellen** — Whisper-JSON, Amara, CSV/JSON für Lückendefinitionen | mittel | M | 2.1 |
| B7 | **Zeitversatz-Korrektur** — globaler Offset und Dehnung der Zeitcodes, wenn Untertitel und Video nicht synchron sind | hoch | S | 2.1 |

## C. Lernbegleitung

| # | Idee | Nutzen | Aufwand | Ziel |
| --- | --- | --- | --- | --- |
| C1 | **Vokabelmitnahme** — markierte Lücken als Wortliste exportieren (CSV, Anki-tauglich) oder in ein Kursglossar übernehmen | hoch | M | 2.2 |
| C2 | **Glossar- und Wörterbuchanbindung** — der V1-Link je Lücke wird zu einer sauberen Verknüpfung mit einem Moodle-Glossareintrag oder einer konfigurierten Wörterbuch-URL-Vorlage | hoch | M | 2.1 |
| C3 | **Aussprachevorbild ohne KI** — Vorlesen eines Segments über die Sprachsynthese des Browsers (`SpeechSynthesis`); kostenlos, ohne Serverdienst, ohne Datenübertragung | hoch | S | 2.1 |
| C4 | **Persönliche Fehlerliste** — Lernende sehen ihre eigenen wiederkehrenden Fehlertypen über mehrere Übungen hinweg | mittel | L | 2.2 |
| C5 | **Feedback je Antwort durch Lehrende** — gezielter Kommentar zu einer einzelnen Antwort statt nur zur Gesamtleistung | mittel | M | 2.2 |
| C6 | **Übungsmodus ohne Wertung** — freies Üben, das nicht ins Gradebook geht, klar als solches gekennzeichnet | hoch | S | 2.1 |

## D. Berichte und Analytik

| # | Idee | Nutzen | Aufwand | Ziel |
| --- | --- | --- | --- | --- |
| D1 | **Schwierigkeitskennwerte je Lücke** — Lösungsquote, Hilfequote, mittlere Versuchszahl; markiert Lücken, die zu schwer oder fehlerhaft gestellt sind | hoch | M | 2.1 |
| D2 | **Abbruchstellen im Video** — an welcher Stelle Lernende aussteigen; ausschließlich aggregiert, opt-in | mittel | M | 2.2 |
| D3 | **Klassenübersicht auf einen Blick** — Heatmap Person × Lücke, direkt aus dem Report Builder | hoch | M | 2.1 |
| D4 | **Anbindung an Moodle Analytics** — Indikatoren für Frühwarnmodelle bereitstellen | gering | L | offen |

## E. Integration

| # | Idee | Nutzen | Aufwand | Ziel |
| --- | --- | --- | --- | --- |
| E1 | **Moodle App inklusive Offlinebearbeitung** — auf Basis der External Functions; Antworten werden lokal gepuffert und synchronisiert | hoch | L | 2.2 |
| E2 | **Kalender- und Terminintegration** — Fälligkeitsdatum mit Kalendereintrag und Aktivitätsübersicht | mittel | S | 2.1 |
| E3 | **Sperr- und Freigabezeiten** — Bearbeitung erst ab / nur bis zu einem Zeitpunkt | mittel | S | 2.1 |
| E4 | **Bedingte Verfügbarkeit** anhand des eigenen Fortschritts als Restriktionsplugin | gering | M | offen |

## F. Sprache und Barrierefreiheit

| # | Idee | Nutzen | Aufwand | Ziel |
| --- | --- | --- | --- | --- |
| F1 | **Sprachspezifische Normalisierungsprofile** — Deutsch (ß/ss, Umlaut-Umschrift), Französisch (Akzente, Elision), Türkisch (punktloses i), Spanisch (ñ), Griechisch (Tonoi) | hoch | M | 2.1 |
| F2 | **Eingabehilfe für Sonderzeichen** — anklickbare Zeichenleiste passend zur Zielsprache, für Tastaturen ohne die benötigten Zeichen | **sehr hoch** | S | **✅ 2.1** |
| F3 | **RTL- und IME-Tauglichkeit** — Arabisch, Hebräisch, ostasiatische Eingabemethoden; Zwischenzustände der Eingabemethode dürfen keine Prüfung auslösen | hoch | M | 2.1 |
| F4 | **Untertitelgestaltung durch Lernende** — Schriftgröße, Kontrast, Zeilenabstand des Transkripts einstellbar und gespeichert | mittel | S | 2.1 |
| F5 | **Wiedergabetempo mit Tonhöhenkorrektur** und segmentweises Endlosschleifen — Kernwerkzeug beim Hörverstehenstraining | hoch | S | 2.0 |

## G. Betrieb und Datenschutz

| # | Idee | Nutzen | Aufwand | Ziel |
| --- | --- | --- | --- | --- |
| G1 | **Aufbewahrungsfristen je Aktivität** — automatische Anonymisierung von Antwortdaten nach x Monaten über einen Scheduled Task | hoch | M | 2.1 |
| G2 | **Pseudonymisierte Berichtsansicht als Voreinstellung** — Klarnamen erst auf Anforderung und mit eigener Berechtigung | hoch | S | 2.0/2.1 |
| G3 | **Übungsdiagnose für Administration** — findet Aktivitäten ohne Medium, mit ungültigen Zeitsegmenten oder mit verwaisten Antworten | mittel | M | 2.2 |
| G4 | **Prüfmodus** — Bearbeitung ohne Hilfen, mit Zeitlimit und einmaligem Versuch, für Leistungsnachweise | mittel | M | 2.2 |

---

## Die fünf Ideen mit dem besten Verhältnis von Nutzen zu Aufwand

**Alle fünf sind für Version 2.1 zugesagt** (Blueprint Kap. 19, Aufwand 4,2–6,1 PW).

1. **B1 — regelbasierte Lückenerzeugung.** Halbiert die Vorbereitungszeit einer
   Übung und braucht weder KI noch externe Dienste.
2. **B3 — Antwortvariante nachträglich anerkennen.** Löst das größte Ärgernis
   automatisch bewerteter Freitextlücken und macht Fehlbewertungen reparabel.
3. **F2 — Zeichenleiste für Sonderzeichen.** Sehr kleiner Aufwand, beseitigt eine
   alltägliche Hürde in fast jeder Fremdsprache.
4. **A3 — Übersetzungsspur als Hilfestufe.** Nutzt eine Infrastruktur, die durch
   das Hilfestufenmodell ohnehin entsteht, und ist didaktisch stark.
5. **A5/A6 — Blindmodus und Audio-only.** Zwei Schalter, die aus derselben Übung
   drei unterschiedliche Schwierigkeitsgrade machen.
