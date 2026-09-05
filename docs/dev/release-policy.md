# Was ein Release enthält

Entscheidung zu RR-13: **Variante A — vollständiges Entwickler- und
Quellartefakt.** Es gibt genau ein Auslieferungsformat, und es ist der komplette
Repository-Inhalt.

## Warum nicht das schlanke Produktions-ZIP

Variante B hätte `docs/`, `tests/`, `makefile` und `tools/` aus dem
Auslieferungspaket genommen. Das klingt sauber und wäre hier falsch:

**Moodle installiert ein Plugin, indem es ein ZIP entpackt.** Was nicht im ZIP
ist, existiert auf der Zielsite nicht — auch nicht `tools/cleanup_stale.sh`, das
genau dort gebraucht wird, wo eine Installation von einem älteren Stand kommt.
Ein Aufräumwerkzeug, das nur im Repository liegt, hilft niemandem, der ein ZIP
eingespielt hat.

**Die Tests sind Teil des Vertrags.** `moodle-plugin-ci` erwartet sie im
Plugin, die Prüfung durch das Moodle-Plugin-Verzeichnis führt sie aus, und eine
Administration, die wissen will, ob das Plugin auf ihrer Moodle-Version läuft,
kann `vendor/bin/phpunit -c mod/elang` aufrufen. Ohne Tests ist das nicht
möglich.

**Zwei Formate wären zwei Wahrheiten.** Sobald ein „Produktions-ZIP" und ein
„Quell-ZIP" nebeneinander existieren, ist die nächste Frage bei jedem Fehler,
welches von beiden die Person eingespielt hat. Diese Sitzung hat mehrfach
gezeigt, wie teuer eine Datei ist, die in einer Variante steckt und in der
anderen nicht.

Was ein Release **nicht** enthält, ist alles, was ohnehin erzeugt wird:
`node_modules/`, `.git/`, `tests/playwright/test-results/`,
`tests/playwright/playwright-report/`. Und keine Sourcemap des React-Bundles —
siehe RR-02.

## Was daraus folgt

| Zusage | Wo sie eingehalten wird |
|---|---|
| `make`-Kommandos in der Dokumentation sind ausführbar | `makefile` liegt im ZIP |
| `tools/cleanup_stale.sh` ist aufrufbar | `tools/` liegt im ZIP |
| `db/removed_files.txt` verweist auf einen realen Pfad | derselbe Grund |
| Tests sind lokal ausführbar | `tests/` liegt im ZIP |
| Doku-Verweise zeigen auf mitgelieferte Dateien | `docs/` liegt im ZIP |

Die README darf deshalb `make`-Kommandos und Dateipfade nennen: sie sind
vorhanden. Das war die eigentliche Sorge hinter RR-13 — nicht die Größe des
Pakets, sondern die Frage, ob die Dokumentation etwas verspricht, das im
ausgelieferten Zustand fehlt.

## Wie ein Release entsteht

1. Der Stand ist ein grüner CI-Lauf. Welche Prüfungen das einschließt und welche
   **nicht**, steht in `docs/dev/ci-gates.md`.
2. Playwright/Axe, k6 **und JMeter** laufen zusätzlich und bewusst. Keines der
   drei ist blockierend, keines wird von einem grünen Pipeline-Lauf belegt, und
   alle drei müssen denselben SHA betreffen — sonst vergleichen sie nichts.
3. Das ZIP wird aus **genau diesem** Commit erzeugt, ohne die oben genannten
   erzeugten Verzeichnisse.
4. `tools/check_amd_builds.sh` läuft vorher: die eingecheckten Build-Artefakte
   müssen zu ihren Quellen passen. Sie werden auf dem Weg ins Release nicht neu
   gebaut, also ist das die letzte Gelegenheit, es zu bemerken.
5. Die Freigabe-Notiz hält fest, worauf sie beruht:

   | Nachweis | Festzuhalten |
   |---|---|
   | SHA | der eine Commit, auf den sich alles bezieht |
   | Moodle-CI | Matrix (4.5 / 5.0 / 5.1 / 5.2 × PHP × DB), Laufnummer |
   | PHPUnit, Behat, Jest | Anzahl Tests, Ergebnis |
   | Bundle | Reproduzierbarkeit bestätigt |
   | Playwright/Axe | Anzahl Tests, Ergebnis |
   | k6 | Szenario, p95, Anteil unter dem Ziel, Fehlerrate |
   | JMeter | Szenario, Threads/Loops, Grenze, Fehlerrate |
   | Dependency-Audit | Kommandos und Befunde, oder „keine" |
   | A11y-Smoke | welche Assistenztechnik, welcher Ablauf, Datum |

   Eine Freigabe-Notiz ohne diese Zeilen sagt „es lief", nicht „es wurde
   geprüft".

## Beim Einspielen

`php admin/cli/purge_caches.php` nach jedem Update. Der Versionssprung erledigt
das normalerweise selbst; bei neu hinzugekommenen Klassendateien ist es der
Unterschied zwischen „läuft" und einem Fatal Error, weil Moodles Klassenkarte
die neue Datei sonst nicht kennt.
