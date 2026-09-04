# Lasttests: Szenarien und Schwellen

Der Code-Review verlangt unter P0, „die getestete Datenmenge und die
Akzeptanzschwellen zu dokumentieren". Dieses Dokument ist die Antwort. Die
Zahlen sind mit Ralf abgestimmt.

## Die beiden Szenarien

| Szenario | Gleichzeitige Lernende | Cues | Plateau | Was es abbildet |
|---|---|---|---|---|
| `smoke` | 25 | 50 | 90 s | Funktioniert der Plan überhaupt noch |
| `classroom` | 200 | 50 | 120 s | Ein voller Kurs bearbeitet die Übung gleichzeitig |
| `lecturehall` | 2000 | 50 | 180 s | Der größte Hörsaal Deutschlands, überfüllt |

**50 Cues**, nicht mehr: das ist die Länge einer realen Höraufgabe. Ein
Stress-Fixture mit 400 Cues sagt etwas über das Rendern im Browser aus (dafür
gibt es den Playwright-Test), aber nichts darüber, wie sich der Server verhält,
wenn viele Menschen gleichzeitig eine normale Übung bearbeiten.

`lecturehall` ist ausdrücklich **kein** plausibler Dienstagmorgen. Der Zweck
ist, die Klippe zu kennen, bevor jemand anders sie findet.

Auszulösen über *Actions → Load test (k6) → Run workflow*; `custom` gibt VUs
und Dauer frei.

## Wogegen gemessen wird — und warum das die wichtigere Entscheidung ist

Der Modus `selfcontained` baut sich eine Moodle-Installation auf dem
GitHub-Runner und bedient sie mit **PHPs eingebautem Entwicklungsserver** auf
vier geteilten Kernen. Das reicht für `smoke` und für nichts darüber.

Belegt: ein Lauf mit **25** Nutzern ergab dort einen p95 von **582 ms** bei
45 Anfragen pro Sekunde — schon nahe an der 800-ms-Grenze. Mit 200 Nutzern
reißt der Lauf diese Grenze, und zwar wegen des Ziels, nicht wegen des Plugins.
Ein rotes Ergebnis, das nichts über den Prüfling aussagt, ist schlimmer als
keines: es gewöhnt daran, die Ampel zu ignorieren.

**`classroom` und `lecturehall` gehören deshalb in den Modus `external`**, gegen
eine echte Moodle-Installation mit richtigem Webserver, PHP-FPM und einer
Datenbank auf eigener Hardware. Der Workflow warnt, wenn beides kombiniert
wird.

## Die beiden Schwellen

| Größe | Wert | Wirkung |
|---|---|---|
| p95-Grenze (`thresholds`) | **800 ms** | lässt den Lauf **scheitern** |
| Fehlerrate (`thresholds`) | < 1 % | lässt den Lauf scheitern |
| p95-Ziel (Metrik) | **300 ms** | wird berichtet, **keine** Bedingung |

Das Ziel ist ausdrücklich **keine** k6-Schwelle. k6 kennt keine berichtende
Schwelle: jede überschrittene setzt Exit 99, und `abortOnFail: false` entscheidet
nur, ob der Lauf vorzeitig abbricht. Als Schwelle formuliert machte das Ziel
jeden völlig akzeptablen Lauf rot — genau das ist am 3. September passiert, bei
p95 = 507 ms.

Berichtet wird es stattdessen als Metrik `elang_content_within_target` (Anteil
der Abrufe unter dem Ziel) und als Klartextzeile in der Zusammenfassung:

```
=== mod_elang Lastergebnis ===
p95:            324.8 ms
Grenze:         800 ms  (eingehalten)
Ziel:           100 ms  (nicht erreicht)
unter dem Ziel: 15.4 % der Abrufe

Der Lauf ist bestanden. Das Ziel ist eine Beobachtungsgroesse,
keine Bedingung — siehe docs/dev/load-testing.md.
```

Zwei Zahlen, weil „akzeptabel" und „funktioniert noch" verschiedene Fragen
sind.

**800 ms ist die Grenze**, weil in dieser Übung jede Antwort eine Anfrage
auslöst. Darüber wartet eine lernende Person beim Tippen lange genug, um sich
zu fragen, ob die Taste angekommen ist — und tippt sie erneut.

**300 ms ist das Ziel.** Es scheitert nicht, wird aber getrennt ausgewiesen, weil
eine Verschiebung von 280 ms auf 700 ms sichtbar sein soll, **solange sie noch
eine Verschiebung ist** und kein Ausfall. Eine Schwelle, die erst bei Schmerz
anschlägt, meldet nichts, was man noch in Ruhe beheben könnte.

Das Ziel wird **nicht** gesenkt, weil ein Lauf es verfehlt hat. Eine Zielzahl,
die man an die Messung anpasst, misst nichts mehr.

Beide sind für einen bewussten Stresslauf überschreibbar (`p95`, `p95target`),
aber die Vorgabewerte sind die vereinbarten Zahlen und kein Platzhalter.

## Was der Lauf misst

`get_version_content` — der Endpunkt, den jede lernende Person beim Öffnen der
Übung abruft und der die gesamte Nutzlast trägt. Die Schreibpfade
(`submit_response`, `request_hint`) sind **nicht** Teil dieses Plans; sie sind
durch Sperren serialisiert und ihr Verhalten unter Nebenläufigkeit ist mit
PHPUnit geprüft (siehe `tests/local/domain/attempt_manager_test.php`), nicht mit
Last.

Der Anlauf skaliert mit der Last: über 500 VUs sind es 60 s statt 15 s.
Andernfalls misst man das Hochfahren statt des Plateaus, und die ersten
Sekunden eines kalten Verbindungspools beherrschen den p95.

## Wie ein Ergebnis zu lesen ist

Jeder Lauf lädt `k6-summary.json` **und** `k6-run-context.txt` hoch. Letzteres
enthält Ref, SHA, Szenario, VUs, Cues, Dauer, beide Schwellen und die
Ausstattung des Runners.

Ohne diesen Kontext ist eine Latenzzahl nicht mit der nächsten vergleichbar:
GitHubs Runner sind geteilte Maschinen, und derselbe Code liefert dort je nach
Nachbarschaft unterschiedliche Zahlen. **Eine einzelne Zahl ist kein Urteil,
eine Reihe ist eins.**

## Der Schreibpfad: gemessen, nicht optimiert

Der k6-Plan misst den Lesepfad. Der Schreibpfad — jede Antwort, jeder Hinweis —
wurde separat gemessen, weil `recalculate_attempt_aggregates()` nach **jeder**
Einreichung alle Antworten des Versuchs neu lädt und die bearbeiteten Zeilen
damit quadratisch mit der Übungslänge wachsen.

Vollständiger Antwortdurchlauf, alle Lücken nacheinander, PostgreSQL 16:

| Lücken | nur Antworten | mit Hinweis je Lücke |
|---|---|---|
| 50 | 2,6 ms/Einreichung, 15 Queries | 5,2 ms, 30 Queries |
| 200 | 2,9 ms/Einreichung, 15 Queries | 6,3 ms, 30 Queries |
| 400 | 3,1 ms/Einreichung, 15 Queries | 6,9 ms, 30 Queries |

**Die Query-Zahl je Einreichung ist konstant.** Das Quadratische steckt in den
in PHP durchlaufenen Zeilen, nicht in Datenbankrunden — bei achtfacher
Übungslänge steigt die Zeit je Einreichung um rund 20 %.

**Schwelle: 50 ms p95 je Einreichung.** Der gemessene Wert liegt bei 400 Lücken
— schon eine extreme Übung — bei 3,1 ms, also mehr als eine Größenordnung
darunter. Es gibt keinen belegten Grund, das Delta-Update aus Stufe 2 des DoD zu
bauen: es würde eine korrekte, gut getestete Neuberechnung durch eine
Fortschreibung ersetzen, die bei jedem Sonderfall auseinanderlaufen kann.

Abgesichert ist stattdessen die Eigenschaft, deren Verlust wirklich wehtäte:
`attempt_manager_test::test_answering_does_not_cost_more_queries_as_the_attempt_fills_up()`
prüft, dass eine Einreichung nicht mehr Datenbankabfragen kostet, wenn der
Versuch bereits voll ist. Eine Zeitzusicherung wäre auf einem geteilten Runner
Rauschen; eine Query-Zahl ist es nicht.

**Gleichzeitige Lernende** teilen sich hier nichts: das Schreiblock heißt
`attempt_write_{attemptid}`, ist also je Versuch und damit je Person. Zwei
Lernende blockieren einander nicht; zwei Anfragen derselben Person werden
serialisiert, was genau der Zweck ist.

## Was nicht getestet wird und warum

- **Kein Gate im Pull Request.** Ein Schwellwert auf einem geteilten Runner
  erzeugt Fehlalarme statt Erkenntnis. Der Lasttest ist ein Trendinstrument und
  läuft manuell (siehe `docs/dev/ci-gates.md`).
- **Kein JMeter.** Es misst dasselbe und bräuchte zusätzlich eine JVM;
  `tests/load/*.jmx` bleibt liegen, wird aber nicht gepflegt.
- **Keine Messung mit echten Mediendateien.** Ausgeliefert werden sie von
  Moodles Dateiapi bzw. direkt vom Anbieter — das ist nicht das Verhalten dieses
  Plugins.
