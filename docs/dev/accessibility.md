# Barrierefreiheit: was geprüft ist und was nicht

Zielstandard: **WCAG 2.1 Stufe AA**. Wo eine Einrichtung mehr verlangt — etwa
BITV 2.0 oder EN 301 549 —, ist AA die Untergrenze, nicht das Ziel.

Dieses Dokument beantwortet zwei Fragen getrennt: was **automatisch** geprüft
wird und was jemand **tatsächlich mit einer Hilfstechnologie** bedient hat. Die
zweite Frage kann kein Test beantworten, und ein Dokument, das das verwischt,
ist schlimmer als keines.

## Automatisch geprüft

Alle Prüfungen laufen in Playwright gegen eine echte Moodle-Installation
(`.github/workflows/playwright.yml`), nicht gegen eine Vorschau.

### axe-core, Schweregrad „serious" und „critical"

| Geprüfte Ansicht | Warum sie eigens geprüft wird |
|---|---|
| `view.php` — der Player | die Ansicht, in der Lernende arbeiten |
| `report.php` — Versuchsübersicht | Tabelle, Filterformular, Aktionsmenüs |
| `edit.php` — Untertitel-Studio | die komplexeste Oberfläche des Plugins |
| `media.php` | Formular mit Dateiauswahl und Vorschau |
| `transcript.php` | Exportauswahl |
| Import-Modal, geöffnet | ein Dialog wird in dem Zustand geprüft, in dem er benutzt wird |
| Import-Modal mit Fehlermeldung | dynamisch erzeugt, und genau der Moment, in dem jemand lesen können muss |

Die letzten vier sind mit `2.0.0-beta.30` dazugekommen. Der Grund für die
Trennung von Dialog und Dialog-mit-Fehler: eine Fehlermeldung entsteht erst zur
Laufzeit; ein Scan des ruhenden Dialogs sagt über sie nichts.

### Tastatur

- **Vollständiger Lernendenfluss ohne Maus**: bis zu einer Lücke tabben, tippen,
  Enter — die Bewertung erscheint. Der Test tabbt mit Obergrenze, damit eine
  kaputte Reihenfolge sichtbar scheitert statt den Lauf hängen zu lassen.
- **Abschluss-Schaltfläche** ist fokussierbar.
- **Import-Modal**: Fokus wandert beim Öffnen hinein, Tab und Shift+Tab laufen
  im Dialog um, Escape schließt, und der Fokus kehrt zu der Schaltfläche
  zurück, die den Dialog geöffnet hat (Jest, `mount.test.tsx`).

### Vergrößerung und Reflow

200 % und 400 % (simuliert über 640 × 512 bzw. 320 × 256): kein seitliches
Scrollen, das Eingabefeld bleibt sichtbar und breiter als 20 px. 400 % ist
AAA-Niveau und der Fall, den Menschen mit Sehbehinderung tatsächlich nutzen.

### Schmale Bildschirme und Textrichtung

390 × 844: kein seitliches Scrollen, Medium und Transkript passen gemeinsam auf
den Schirm, die Einblendung bleibt im Bild. Für RTL wird geprüft, dass die
markierte Kante der ausgewählten Cue-Zeile beim Umschalten der Dokumentrichtung
auf die andere Seite wandert — die Regel ist also logisch und nicht physisch.

### Nicht farbabhängig

Bewertungszustände sind Symbol **und** zugänglicher Name, nicht nur Farbe. Der
Auswahlrand der Cue-Liste ist Kante **und** `aria-current`. Der Fortschritt bei
„beantwortet" ist Zahl **und** Balken.

## Manuell zu prüfen — die Freigabeevidenz

Automatische Prüfungen finden, was sich im DOM erkennen lässt. Ob eine
Statusänderung **verständlich angesagt** wird, ob die Reihenfolge der Ansagen
einen Sinn ergibt, ob ein Vollbild auf iOS noch bedienbar ist — das entscheidet
nur ein Mensch mit der Hilfstechnologie in Betrieb.

Vor einer Stable-Freigabe durchzuführen und hier einzutragen:

| Prüfung | Umgebung | Ablauf | Ergebnis | Datum |
|---|---|---|---|---|
| Lernendenfluss | NVDA + Firefox | Übung öffnen, Cue anhören, Lücke füllen, prüfen, Hinweis anfordern, mit Enter weiter, abschließen | offen | — |
| Lernendenfluss | VoiceOver + Safari | wie oben | offen | — |
| Autorenfluss | NVDA + Chrome | Medium setzen, importieren, Cue wählen, Lücke bearbeiten, veröffentlichen | offen | — |
| Vollbild mit Einblendung | iOS + Safari | Anbieter- und Dateimedium im Vollbild | offen | — |

**Worauf dabei zu achten ist**, über „liest es vor" hinaus:

- Wird die Bewertung einer Lücke angesagt, ohne dass man sie suchen muss? Die
  Statusanzeige ist eine Live-Region; ob sie im richtigen Moment und nicht
  dauernd spricht, zeigt erst der Betrieb.
- Ist nach dem Schließen des Import-Modals klar, wo man ist?
- Ist bei „am Untertitel angehalten" hörbar, dass etwas zu tun ist?

## Bekannte Plattformgrenzen

**iOS-Vollbild.** Safari auf iOS spielt Video in einem Systemplayer ab, der
keinen HTML-Inhalt aufnehmen kann. Untertitel-Einblendungen mit Lücken sind dort
im Vollbild nicht darstellbar; das Medium läuft ohne sie, und beim Verlassen ist
alles wieder da. Auf anderen Plattformen hebt der Player die
Vollbildanforderung auf die Bühne, die Bild und Einblendung gemeinsam enthält.

**Anbietereinbettungen.** Ein YouTube- oder Vimeo-Rahmen ist fremdes HTML in
einem Iframe. Seine Barrierefreiheit liegt nicht in unserer Hand, und axe kann
über eine Iframe-Grenze hinweg nichts prüfen. Wer darauf angewiesen ist, sollte
Datei oder direkte URL verwenden — siehe `docs/dev/provider-embeds.md`.

## Wie ein Befund behandelt wird

Ein „serious"- oder „critical"-Befund von axe lässt den Playwright-Lauf
scheitern und ist zu beheben. Befunde geringeren Schweregrads werden nicht
automatisch durchgesetzt: sie enthalten genug Fälle, die im Moodle-Theme statt
im Plugin liegen, dass eine Durchsetzung hier zu Ausnahmen führen würde statt zu
Korrekturen — und eine Prüfung mit Ausnahmeliste prüft bald nichts mehr.
