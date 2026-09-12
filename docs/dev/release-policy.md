# Was ein Release enthält

**`.gitattributes` ist die maßgebliche Quelle dafür.** Dieses Dokument erklärt
sie; wo beide auseinandergehen, gilt die Datei.

Ein Release ist das **installierbare Plugin**, nicht das Repository: was eine
Site braucht, um mod_elang zu betreiben, und nichts, was nur in einem Checkout
Sinn ergibt.

## Was nicht mitgeliefert wird

| Ausgeschlossen | Grund |
|---|---|
| `docs/` | Entwicklungsdokumentation; gehört ins Repository, nicht auf jede Site |
| `tools/` | Werkzeuge für Entwicklung und CI |
| `makefile` | dasselbe |
| `.github/` | Pipelines |
| `lang/` außer `lang/en/` | Übersetzungen sind Sache von AMOS, sobald das Plugin im Verzeichnis ist — eine Kopie im Archiv liefe unbemerkt auseinander |
| `.gitattributes`, `.gitignore`, `.phpcsignore` | bedeutungslos außerhalb eines Checkouts |

`tests/` bleibt drin: `moodle-plugin-ci` erwartet die Tests im Plugin, die
Prüfung durch das Plugin-Verzeichnis führt sie aus, und eine Administration kann
`vendor/bin/phpunit -c mod/elang` aufrufen, um zu sehen, ob das Plugin auf ihrer
Moodle-Version läuft.

## Was daraus folgt

Die Dokumentation darf **keine** Pfade versprechen, die im ausgelieferten
Zustand fehlen. Konkret:

- `make`-Kommandos sind Entwicklungskommandos und stehen nur in `docs/`, das
  ohnehin nicht ausgeliefert wird — sie richten sich an Leute mit einem
  Checkout.
- `tools/cleanup_stale.sh` liegt **nicht** auf der Zielsite. Wer eine
  Installation von einem älteren Stand aufräumen muss, holt es aus dem
  Repository. `db/removed_files.txt` liegt bei, damit die Liste der zu
  entfernenden Pfade auf der Site verfügbar ist, auch wenn das Werkzeug selbst
  es nicht ist.
- README und die Strings sind die einzige Dokumentation, die mitgeht. Was dort
  steht, muss ohne `docs/` verständlich sein.

## Ein Format, nicht zwei

Erzeugt wird das Archiv mit `git archive`, nicht mit einer eigenen
Ausschlussliste. Damit enthält unser ZIP genau dasselbe wie das Archiv, das
GitHub für einen Tag erzeugt — zwei Archive mit unterschiedlichem Inhalt wären
die Sorte Unterschied, die bei jedem Fehlerbericht zuerst geklärt werden müsste.

## Wie ein Release entsteht

1. Der Stand ist ein grüner CI-Lauf. Welche Prüfungen das einschließt und welche
   **nicht**, steht in `docs/dev/ci-gates.md`.
2. Playwright/Axe, k6, JMeter **und der Migrationslauf 1.3.5 → 2.0** laufen
   zusätzlich und bewusst. Keines der
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
   | Migration 1.3.5 → 2.0 | Moodle-Branch, Ergebnis der 32 Prüfungen |
   | Dependency-Audit | Kommandos und Befunde, oder „keine" |
   | A11y-Smoke | welche Assistenztechnik, welcher Ablauf, Datum |

   Eine Freigabe-Notiz ohne diese Zeilen sagt „es lief", nicht „es wurde
   geprüft".

## Beim Einspielen

`php admin/cli/purge_caches.php` nach jedem Update. Der Versionssprung erledigt
das normalerweise selbst; bei neu hinzugekommenen Klassendateien ist es der
Unterschied zwischen „läuft" und einem Fatal Error, weil Moodles Klassenkarte
die neue Datei sonst nicht kennt.
