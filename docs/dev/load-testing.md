# Lasttests: Szenarien und Schwellen

Der Code-Review verlangt unter P0, „die getestete Datenmenge und die
Akzeptanzschwellen zu dokumentieren". Dieses Dokument ist die Antwort. Die
Zahlen sind mit Ralf abgestimmt.

## Zwei Werkzeuge, eine Strecke

Gemessen wird mit **k6** und mit **JMeter**, beide gegen denselben Endpunkt,
dieselbe Fixture und dieselbe Latenzgrenze.

| | k6 | JMeter |
|---|---|---|
| Rolle | Hauptinstrument: Parallelität, Trends, VU-Szenarien | unabhängige Gegenprobe derselben Lesestrecke |
| Plan | `tests/load/elang-read-endpoints.k6.js` | `tests/load/elang-read-endpoints.jmx` |
| Workflow | `.github/workflows/load-k6.yml` | `.github/workflows/load-jmeter.yml` |
| Start | `make load-k6` | `make jmeter` |
| Braucht | eine statische Binärdatei | eine JVM |
| Ergebnis | `k6-summary.json`, `k6-run-context.txt` | `jmeter-results.jtl`, HTML-Report, `jmeter-run-context.txt` |

**Warum zweimal dasselbe messen?** Weil es eben nicht dasselbe ist. Zwei
unabhängige Werkzeuge, die über einen Endpunkt verschiedener Meinung sind,
liefern eine Information, die keines von beiden allein erzeugen kann: dass das
Ergebnis am Werkzeug hängt und nicht am Plugin. Stimmen sie überein, ist die
Aussage belastbarer, als eine einzelne Zahl es je sein könnte. Weichen sie ab,
ist keines von beiden im Recht — die Abweichung **ist** der Befund.

Der Preis ist eine JVM, die sonst nichts in diesem Repository braucht. Deshalb
läuft JMeter ausschließlich manuell und nie im Push-Gate.

## Was ein roter Lauf bedeutet

Ein absichtlich überdimensionierter Lauf und eine Regressionsmessung dürfen
nicht dasselbe Signal erzeugen. Bis RC1 taten sie es: `lecturehall` überschritt
erwartungsgemäß die Latenzschwelle, der Lauf wurde rot, und Rot hieß damit
manchmal „das Plugin ist zu langsam geworden" und manchmal „wir haben absichtlich
zu viel Last erzeugt".

Jedes Szenario trägt deshalb eine **Rolle**, und die Rolle entscheidet, welche
Schwellen greifen.

| Szenario | Rolle | Ziel | Lernende | Datensatz | Dauer | Latenzschwelle | Blockierend |
|---|---|---|---|---|---|---|---|
| `smoke` | **gate** | selbstenthalten oder extern | 25 | 50 Untertitel | 90 s | p95 < 800 ms | **ja** |
| `classroom` | diagnostic | nur extern sinnvoll | 200 | 50 Untertitel | 120 s | wird berichtet | nein |
| `lecturehall` | diagnostic | nur extern sinnvoll | 2000 | 50 Untertitel | 180 s | wird berichtet | nein |
| frei gewählt | diagnostic | wie gewählt | Eingabe | 50 Untertitel | Eingabe | wird berichtet | nein |

**Fehler und Moodle-Exceptions blockieren in beiden Rollen.** Eine abgerissene
Verbindung oder eine Antwort mit `exception`-Feld ist nie „bei dieser Last zu
erwarten" — sie ist entweder ein Defekt oder eine Infrastrukturgrenze, und das
Etikett „diagnostic" macht sie nicht hinnehmbar. Nur die *Latenz* wird in
diagnostischen Läufen berichtet statt bewertet.

Die Voreinstellung ist `gate`. Ein vergessenes `ROLE` fällt damit auf die
strenge Seite.

### HTTP-Fehler und Exceptions werden getrennt gezählt

In einer gemeinsamen Fehlerquote sehen sie gleich aus und bedeuten Gegenteiliges:

| Metrik | Was sie zählt | Aussage |
|---|---|---|
| `elang_http_errors` | Antwort kam nicht mit HTTP 200 an | Kapazität oder Netz |
| `elang_exception_responses` | HTTP 200 mit `exception`-Feld | das Plugin hat falsch geantwortet |
| `elang_content_errors` | beides zusammen, plus unlesbare Antworten | Gesamtbild |

`elang_exception_responses` hat die Schwelle `rate==0`: eine einzige solche
Antwort ist ein Befund, keine Quote.

### Warum JMeter eine eigene Behandlung braucht

JMeters `DurationAssertion` markiert ein zu langsames Sample als
**fehlgeschlagen**. Damit landete die Latenz direkt in der Fehlerquote, die das
Gate liest — dieselbe Vermengung, nur an anderer Stelle. Diagnostische Läufe
setzen die Grenze deshalb auf eine Stunde, statt die Assertion zu entfernen: der
Plan bleibt eine Datei, und eine Anfrage, die eine Stunde braucht, ist längst am
Socket-Timeout gescheitert.

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

## Szenarien beschreiben Lernende, nicht gleichzeitige Anfragen

Das ist die wichtigste Eigenschaft der Pläne, und sie war anfangs falsch.

Beide Pläne liefen mit N virtuellen Nutzenden **ohne Denkzeit**. „200 Lernende"
bedeutete damit 200 permanent laufende Anfragen — etwas, das kein Kurs je tut.
Ein Hörsaal-Lauf ergab so p95 = 29 Sekunden und sah aus wie ein Befund über das
Plugin; tatsächlich war es ein Befund über das Modell.

Jetzt ist **eine Iteration eine Lernendensitzung**:

| Annahme | Wert | Herkunft |
|---|---|---|
| Ankunftsfenster | 180 s | eine Klasse öffnet die Übung über die ersten Minuten einer Stunde |
| Denkzeit Lücke zu Lücke | 3 s | Praxis |
| Medienabrufe je Lernendem | 1,5 | einmal beim Öffnen, mit 50 % Wahrscheinlichkeit später erneut |

Daraus ergeben sich die Raten:

| Szenario | Lernende | Ankünfte/s |
|---|---|---|
| `smoke` | 25 | 0,14 |
| `classroom` | 200 | 1,11 |
| `lecturehall` | 2000 | 11,1 |

k6 setzt das mit `constant-arrival-rate` um. JMeter kennt das nicht und nutzt
einen Constant Throughput Timer; die Threads liefern dort nur die Parallelität.
**Zehn Threads, nicht mehr:** 50 Threads feuern beim Hochlauf, bevor der Timer
sie bremsen kann, und allein dieser Startburst erzeugte p95 = 4,5 s auf einem
Server mit Median 37 ms.

### Gemessen mit diesem Modell

Gegen dasselbe selbstenthaltene Ziel, das mit dem alten Modell zusammenbrach:

| Werkzeug | Szenario | p95 | Fehler |
|---|---|---|---|
| k6 | classroom | 45,6 ms | 0 % |
| JMeter | classroom | 49 ms | 0 % |

Dass beide Werkzeuge unabhängig auf ~47 ms kommen, ist genau der Zweck der
Doppelmessung: eine Zahl allein hätte man für ein Artefakt des Werkzeugs halten
können.

Der Medienpfad wird mitgemessen — `mod_elang_pluginfile` mit Capability- und
Versionsprüfung, angefordert per `Range` über die ersten 64 KB. Ganze Videos zu
übertragen würde die Netzwerkanbindung des Runners messen, nicht das Plugin.

## Was das selbstenthaltene Ziel aushält — und was nicht

Der `selfcontained`-Modus baut ein Moodle und bedient es mit **PHPs eingebautem
Entwicklungsserver**, acht Worker auf einem Vier-CPU-Runner. Das reicht für
`smoke` (25 Nutzende) und für nichts darüber.

Gemessen, nicht angenommen:

| Szenario gegen `selfcontained` | p95 | Aussage |
|---|---|---|
| `smoke`, 25 VUs | ~410 ms | brauchbar als Trendwert |
| `lecturehall`, 2000 VUs | **29 090 ms**, 1327 abgebrochene Iterationen | misst die Warteschlange des Testservers |

Bei 2000 gleichzeitigen Anfragen auf acht Worker warten rund 250 Anfragen je
Worker. Die 29 Sekunden sind Wartezeit, kein Verarbeiten — über das Plugin sagt
die Zahl nichts.

Diese Zahlen stammen aus dem **alten** Modell mit permanent laufenden Anfragen.
Mit Ankunftsraten trägt dasselbe Ziel `classroom` mühelos (p95 unter 50 ms bei
beiden Werkzeugen), und die zeitweilige Sperre für `classroom`/`lecturehall` im
`selfcontained`-Modus ist entfallen — sie war die richtige Antwort auf ein
falsch gebautes Szenario, nicht auf eine zu schwache Umgebung.

Für `lecturehall` gilt weiter Vorsicht: 11 Ankünfte je Sekunde sind auf vier
vCPU plausibel, aber ungemessen. Ein belastbarer Hörsaal-Nachweis gehört in den
`external`-Modus.

Für `classroom` und `lecturehall` braucht es `mode=external` gegen eine echte
Installation mit einem richtigen Webserver.

## Was eine belastbare Hörsaal-Messung braucht (P2-3)

Der `external`-Modus misst gegen eine Installation, die du stellst. Damit die
Zahl etwas über das Plugin aussagt und nicht über die Umgebung, muss diese
Umgebung die Last überhaupt annehmen können:

| Bestandteil | Anforderung | Warum |
|---|---|---|
| Webserver | nginx oder Apache mit **PHP-FPM** | PHPs eingebauter Server hat feste Worker und keine Warteschlangenstrategie |
| PHP-FPM | `pm.max_children` ≥ erwartete Gleichzeitigkeit | sonst misst man wieder die Warteschlange |
| Datenbank | eigener Host oder eigener Container, `max_connections` passend | die Verbindungsgrenze wird vor der CPU erreicht |
| Moodle-Caches | MUC produktiv konfiguriert, `cachejs` an | eine Site im Entwicklungsmodus misst ihr eigenes Neukompilieren |
| Lastgenerator | **nicht** auf derselben Maschine | 2000 virtuelle Nutzende brauchen selbst CPU |

Ohne diese fünf Punkte ist auch ein `external`-Lauf nur eine andere Art, die
Testumgebung zu vermessen. Das Ergebnis gehört mit Umgebungsbeschreibung
festgehalten, sonst ist es mit dem nächsten nicht vergleichbar — dafür gibt es
`k6-run-context.txt` und `jmeter-run-context.txt`.

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
| p95-Grenze (k6 `thresholds`) | **800 ms** | lässt den k6-Lauf **scheitern** |
| Grenze je Anfrage (JMeter-Assertion) | **800 ms** | lässt den JMeter-Lauf **scheitern** |
| Fehlerrate | < 1 % | lässt beide Läufe scheitern |
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

JMeter kann kein Perzentil in einer Assertion prüfen, nur die Dauer der
einzelnen Anfrage. Es setzt die 800 ms deshalb **je Anfrage** durch — strenger
als ein p95, und damit die ehrlichste verfügbare Entsprechung. Die Fehlergrenze
von 1 % wird nach dem Lauf aus der `.jtl` ausgewertet, weil JMeter selbst auch
dann mit Code 0 endet, wenn jede Assertion fehlgeschlagen ist.

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
- **Keine Messung mit echten Mediendateien.** Ausgeliefert werden sie von
  Moodles Dateiapi bzw. direkt vom Anbieter — das ist nicht das Verhalten dieses
  Plugins.
