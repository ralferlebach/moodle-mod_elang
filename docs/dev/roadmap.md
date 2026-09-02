# Was nach 2.0 offen ist

Dies ist der Ort für Aufgeschobenes. Im Code stehen keine Roadmap-Kommentare:
ein Vermerk „kommt in 2.1" altert dort schlecht und wird zur Unwahrheit, sobald
sich der Plan ändert oder die Funktion früher kommt.

## Umgesetzt, aber ohne Bedienoberfläche

Beides ist serverseitig fertig und getestet, es fehlt nur die Oberfläche.

### Sonderzeichenleiste

`classes/local/player/special_characters.php` liefert je Inhaltssprache ein
kuratiertes Zeichenset, und `get_attempt_exercise` gibt es bereits im Feld
`specialcharacters` an den Client aus. Der Player rendert daraus noch keine
Leiste.

Der ausformulierte Issue-Entwurf dazu liegt in `docs/sessions/session-008.md`
(Antwort auf Rückfrage 4.7).

### Regelbasierte Lücken

`gap_rule_generator` und `mod_elang_generate_rule_gaps` sind vollständig, und
`RuleGapControl.tsx` bedient sie im Editor. Was fehlt, ist die Anwendung einer
Regel über **mehrere Cues auf einmal**; heute wird sie je Cue ausgelöst.

## Bewusst aufgeschoben

### React über Moodle Core beziehen

Das Plugin liefert React als eigenes Bundle unter
`js/vendor/react/editor.bundle.js` aus. Moodle Core bringt React ab 5.2 selbst
mit. Ein Wechsel lohnt erst, wenn die **Mindestversion** dieses Plugins auf 5.2
steht — also nach dem Support-Ende von 5.1, voraussichtlich Oktober 2026. Die
Ladestelle ist in `edit.php` kommentiert.

### Zeit-API für Anbieter-Embeds

YouTube- und Vimeo-Einbettungen melden ihre Wiedergabezeit nicht, deshalb
degradiert `playback_settings` sie auf „Untertitel unter dem Medium" und „nicht
anhalten". Beide Anbieter sprechen ein dokumentiertes postMessage-Protokoll, mit
dem sich ein Medien-Adapter ohne Fremd-SDK bauen ließe.

Nicht gemacht, weil es **cross-origin** ist und sich in keiner der hier
verfügbaren Umgebungen verifizieren lässt. Eine Änderung, die man nicht prüfen
kann, gehört nicht in ein Release.

### JMeter

`tests/load/*.jmx` bleibt liegen, wird aber nicht gepflegt. Es misst dasselbe
wie der k6-Plan und bräuchte zusätzlich eine JVM im Runner.

## Vor der Stable-Freigabe zu erledigen

Diese drei sind keine Wünsche, sondern Lücken:

1. **Upgrade-Pfad Beta → Beta.** `tests/upgrade_test.php` springt von Version 1
   auf den aktuellen Stand. Ein Upgrade von einer früheren 2.0-Beta ist nicht
   abgedeckt. Während der Beta-Reihe vertretbar, danach nicht.
2. **Playwright und k6 vor der Freigabe anstoßen.** Beide sind nicht Teil der
   blockierenden CI (siehe `docs/dev/ci-gates.md`), also belegt ein grüner
   Pipeline-Lauf weder Barrierefreiheit noch Lastverhalten.
3. **Lokale Sprachanpassung neu anlegen.** Mit 2.0.0-beta.14 haben 356
   String-IDs ihre Doppelpunkte verloren. Wer Strings lokal angepasst hatte,
   muss das gegen die neuen IDs wiederholen.

## Gemessen und bewusst nicht geändert

**Berichtsabfragen.** Bei 20 000 Versuchen in einer Aktivität: Standardliste
11 ms, Sortierung nach Name 43 ms, Zähler 1,7 ms, Kennzahlen 4,1 ms. Der
Abfrageplan nutzt den Index auf `elangid`. Ein zusätzlicher Index wäre Aufwand
ohne belegten Nutzen — die Messung steht in `docs/sessions/session-008.md`,
Inkrement 27.
