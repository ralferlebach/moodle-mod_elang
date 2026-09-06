# Code-Review mod_elang 2.0.0-RC1

Durchgang durch die erweiterte Review-Checkliste (39 Abschnitte, 685
Prüfpunkte). Geprüfter Stand: `2.0.0-RC1`, Build `2026090600` — die beiden
Befunde dieses Durchgangs sind darin bereits behoben.

**Umgebung:** Moodle 4.5.13+, PHP 8.3.6, PostgreSQL 16.15, moodle-cs 3.7.0.

## Wie dieses Dokument zu lesen ist

Jeder Abschnitt nennt, **wie** geprüft wurde. Wo eine Prüfung ausgeführt wurde,
steht ihr Ergebnis; wo sie eine Beurteilung ist, steht sie als Beurteilung; wo
sie nicht durchführbar war, steht das ebenfalls. Ein Häkchen ohne Beleg wäre
hier wertlos — die Checkliste hat 685 davon, und sie alle abzuhaken wäre in
zwanzig Minuten möglich und würde nichts aussagen.

Was gefunden wurde, steht unten unter **Befunde**. Was nicht gefunden wurde,
steht als „kein Befund" mit der Prüfung daneben.

---

## 1–3 Struktur, Coding Standards, Kommentarhygiene

| Prüfung | Ergebnis |
|---|---|
| `phpcs --standard=moodle --severity=1` | 0 Errors, 0 Warnings (Exit 0) |
| `moodlecheck` | 0 Befunde |
| ESLint, Stylelint, Rollup (Grunt) | 0, mit `--max-lint-warnings=0` |
| `tsc --noEmit` | sauber |
| Mustache-Prüfung | 0 Fehler |
| `actionlint` | Exit 0 |
| GPL-Boilerplate in allen PHP-Dateien | vollständig (Skript über den Baum) |
| `MOODLE_INTERNAL` | in `lib.php`, `db/upgrade.php`, `db/install.php` bewusst nicht — diese Dateien deklarieren nur, moderne Moodle-Regel; phpcs bestätigt |
| TODO/FIXME/XXX/HACK im Produktivcode | keine |
| Roadmap-Kommentare im Code | keine (in `docs/dev/roadmap.md` verlagert, beta.16) |

Alle Prüfungen laufen über `tools/verify.sh` **per Exit-Code**. Das ist selbst
ein Befund aus dieser Reihe: die Prüfungen wurden zuvor mit `tail -3` gelesen,
und ein Lauf mit Befunden endet mit derselben Zeitzeile wie ein sauberer.

## 4 Entry-Point-Security

Jeder Einstiegspunkt einzeln geprüft:

| Datei | Absicherung |
|---|---|
| `view.php`, `edit.php`, `media.php`, `transcript.php` | `require_login($course, false, $cm)` + `require_capability` |
| `report.php` | dazu `sesskey`-Prüfung vor destruktiven Aktionen |
| `index.php` | `require_course_login` — korrekt, es listet nur |
| `admin_migrate_v1.php` | `admin_externalpage_setup()`, das `moodle/site:config` erzwingt |

Kein `$_GET`/`$_POST`/`$_REQUEST` im Produktivcode; durchgängig
`required_param`/`optional_param`. **Kein Befund.**

## 5 pluginfile und Dateizugriffe

Ein einziger Callback, `mod_elang_pluginfile`, der Kontext, Capability **und**
`version_manager::user_can_access_version_file()` prüft.

Hier lag der schwerste Befund der gesamten Arbeit (beta.12): es gab **zwei**
Callbacks, und Moodle bevorzugt den nach der Komponente benannten — das war der
mit der schwächeren Prüfung. Entwurfsmedien waren über eine geratene
Versions-ID erreichbar. Ein Test hält jetzt fest, dass es bei genau einem
bleibt.

## 6 Capability-Modell

Zehn Capabilities, dokumentiert in `docs/dev/capabilities.md`. Ein
Vertragstest erzwingt, dass README und `db/access.php` **genau dieselben**
nennen — eingeführt, nachdem drei Rollenangaben auseinandergelaufen waren.

Drei Stellen, an denen eine Capability nicht die ganze Antwort ist, sind
dokumentiert und getestet: `attempt` (dazu Eigentumsprüfung), `useregex` (nur
Manager) und die beiden Exporte (dazu Aktivitätseinstellungen).

## 7 External API, AJAX, Mobile

15 Funktionen. `tests/external/security_contract_test.php` iteriert über
**`db/services.php`** statt über eine gepflegte Liste und prüft je Funktion:
Klasse existiert, drei Pflichtmethoden, Capability deklariert, Typ gesetzt.
Dazu Verhaltenstests: fremder Versuch, Autorenfunktion als Lernende, Lücke aus
einer anderen Übung.

Mobile-Service: nur lernendenseitige Funktionen, durch Test abgesichert.

## 8 Privacy und externe Datenübertragung

18 Tests. Metadaten werden aus **`db/install.xml`** abgeleitet: jede Tabelle mit
einer personenbezogenen Spalte muss deklariert sein.

**In diesem Durchgang ergänzt:** `add_external_location_link('videoprovider')`.
Ein YouTube- oder Vimeo-Embed überträgt IP-Adresse und Geräteangaben an den
Anbieter. Das Plugin sendet nichts selbst — deshalb war es leicht zu übersehen —,
aber die Aktivität löst die Übertragung aus, und eine Auskunft sollte das sagen.
Ebenfalls ergänzt: `referrerpolicy="strict-origin"` am Iframe, damit der
Anbieter die Site erfährt, nicht den Kurs, die Aktivität oder den Versuch.

Die Einwilligungsschranke selbst (beta.28) ist Site-Einstellung, im Zweifel
gesperrt, und gilt je Browsersitzung.

## 9–10 Datenmodell und Upgradepfade

8 Tabellen, Fremdschlüssel und Indizes gesetzt. Upgrade doppelt abgedeckt:

- `tests/upgrade_test.php` baut eine V1-förmige Datenbank und lässt
  `xmldb_elang_upgrade()` darüberlaufen;
- `.github/workflows/migration-v1.yml` installiert **das echte Plugin 1.3.5**,
  füllt es aus einer Untertiteldatei mit V1s eigener Lückensyntax, aktualisiert
  und prüft 34 Eigenschaften.

Der zweite existiert, weil der erste das V1-Schema **selbst definiert** und
damit nur bestätigen kann, womit er geschrieben wurde.

## 11 Backup, Restore, Duplicate, Reset

Backup deckt alle 8 Tabellen ab; Restore getestet.

**Befund RC-01, in diesem Durchgang behoben:** `elang_reset_userdata()` und die
beiden zugehörigen Formularfunktionen fehlten vollständig. Ein Kurs-Reset ließ
jeden Versuch stehen. Wer einen Kurs für den nächsten Jahrgang wiederverwendet,
hätte der neuen Gruppe eine Übung übergeben, die bereits die Antworten der
vorigen enthält — sichtbar im Bericht, gewertet im Notenbuch, und von Personen,
die nicht mehr im Kurs sind.

Implementiert samt Notenbuch-Reset. Fünf Tests: löscht Versuche und Antworten,
**behält die Übung** (Versionen, Cues, Lücken sind Lehrmaterial), tut ohne
Häkchen nichts, ist standardmäßig aus, und greift nicht in andere Kurse.

## 12–13 Datenintegrität, Transaktionen, Tasks

Alle 10 Schreibpfade laufen über `transaction_trait::in_transaction()`.
Per-Versuch-Sperren heißen einheitlich `attempt_write_<id>` — auch das war ein
Befund: `delete_attempt()` nahm eine eigene Sperre, sodass ein Löschen neben
einer gerade bewerteten Antwort laufen konnte.

Der Migrationstask ist ein Adhoc-Task, absichtlich ohne `db/tasks.php`: er wird
auf Anforderung eingereiht, nicht nach Zeitplan.

## 14 Forms und Settings

Zwei Site-Einstellungen (`allowedlanguages`, `providerconsent`), beide
dokumentiert. Aktivitätseinstellungen vollständig in der README beschrieben —
das war eine Doku-Lücke, die in beta.30 geschlossen wurde.

## 15 Import- und Parser-Hardening

Serverseitig: 2 MiB, 4000 Cues, 5000 Zeichen je Zeile, UTF-8-Pflicht, und
Inhalt ohne einen einzigen Cue wird abgelehnt statt leer zurückgegeben.
Clientseitig: Größe, Endung und MIME **vor** `FileReader.readAsText()`.

Die Grenzen stehen im Parser, nicht im Endpunkt — durch ihn muss jeder Weg.

## 16–17 Output und i18n

Zwei `{{{ }}}` in Templates, beide gerenderte Moodle-Komponenten
(`action_menu`, Formular-HTML), kein Nutzertext. 422 Strings je Sprache, exakte
Parität. Zwei Stellen bilden IDs zur Laufzeit; beide aus geschlossenen
Schlüsselmengen, alle Ziele existieren (Prüfskript).

Flache String-IDs seit beta.14 — davor war das Plugin **nicht
veröffentlichungsfähig**, weil AMOS nur `[a-z0-9_]` akzeptiert.

## 18 Accessibility

`docs/dev/accessibility.md` trennt automatisch Geprüftes von dem, was ein
Mensch mit Hilfstechnologie bedienen muss. 7 Axe-Scans, Tastaturfluss ohne
Maus, 200 % und 400 % Reflow, RTL-Grundtest.

**Offen und als offen ausgewiesen:** die vier manuellen Screenreader-Prüfungen.

## 19–20 UX und Reports

Destruktive Aktionen bestätigen über `Notification.saveCancelPromise()`.

Der Personenfilter im Bericht war ein **Namensleck**: er listete alle Personen
mit Versuch, ohne den Gruppen-Scope. Behoben, indem er dieselbe Abfrage nutzt
wie Liste, Zähler, Kennzahlen und Export.

## 21 Performance

Gemessen statt vermutet:

| Pfad | Ergebnis |
|---|---|
| Bericht, 20 000 Versuche | Liste 10,9 ms, Sortierung nach Name 43,3 ms, Index-Scan |
| Antwortpfad, 50/200/400 Lücken | 2,6 / 2,9 / 3,1 ms je Einreichung, **konstant 15 Queries** |
| Entwurf speichern, 50/200/400 Cues | 0,5 ms je Cue bei 400, **konstant 4 Queries je Cue** |
| Player, 400 Cues | `restore` 791 → 419 ms nach Beseitigung eines O(N²)-Zugriffs |
| k6 (letzte Messung) | p95 409,9 ms, Grenze 800, 79,3 % unter dem 300-ms-Ziel |

Kein Index hinzugefügt: ein Index verlangsamt jedes Schreiben und muss migriert
werden. Ohne belegten Nutzen wäre das Aufwand gegen ein Gefühl.

## 22 Caching

Keine `db/caches.php`, kein `cache::make`. **Bewusst:** die teuerste Leseabfrage
liegt bei ~400 ms unter Last und der Inhalt ändert sich beim Veröffentlichen.
Ein Cache wäre eine zweite Wahrheitsquelle mit Invalidierungsproblem für einen
Gewinn, den keine Messung verlangt.

## 23 Externe Bibliotheken

`npm audit` in drei Bereichen: 0 Befunde. React 18.3.1 und ReactDOM
einkompiliert, `thirdpartylibs.xml` synchron. esbuild auf `^0.28` (0.28.2),
außerhalb GHSA-gv7w-rqvm-qjhr.

Bemerkenswert und dokumentiert: **`npm audit` meldete diesen Befund nicht.** Ein
Audit ist eine Abfrage gegen eine Datenbank zu einem Zeitpunkt, kein Beweis der
Abwesenheit.

## 24–27 Architektur, Legacy, Algorithmen, Verträge

Domänenlogik unter `classes/local/`, Webservices unter `classes/external/`,
Ausgabe unter `classes/output/`. `version_manager` ist mit 1077 Zeilen die
größte Klasse — vertretbar, weil sie einen zusammenhängenden Lebenszyklus
abbildet, aber der erste Kandidat, falls weitere Verantwortung dazukäme.

Legacy: Ausstieg in `docs/dev/v1-legacy-exit.md` an eine **Bedingung** geknüpft,
nicht an ein Datum, und `v1_decommissioner::blockers()` ist die einzige
Autorität.

## 28 Teststrategie

```
PHPUnit    474 Tests, 1518 Assertions        Behat  45 Szenarien / 466 Steps
Jest       75 Tests                          Playwright 26 Tests
Migration  34 Prüfungen gegen echtes V1      k6 + JMeter, beide gepflegt
```

## 29 CI/CD und Supportmatrix

`supported = [405, 502]`. Blockierend: `lint-php` (2), `lint-js` (1), `phpunit`
(6), `behat` (3), `stale-files` (1). Moodle 5.1 ist seit beta.27 in allen drei —
es lag zuvor **innerhalb** der zugesagten Spanne, ohne je getestet worden zu
sein.

Nicht blockierend und in `docs/dev/ci-gates.md` als solche benannt: Playwright,
k6, JMeter, Migrationslauf, Moodle-`main`.

## 30 Testdaten und Fixtures

Ein Befund aus diesem Durchgang, von Ralf gefunden: die Migrations-Fixture
bestand aus einer Untertiteldatei **ohne** Lücken und daneben handgeschriebenen
Cues. Die Datei war Dekoration. Jetzt trägt sie V1s echte Lückensyntax, und die
Cues werden **daraus** abgeleitet.

## 31–32 Logging und externe Services

Kein `error_log`, `var_dump`, `print_r` oder `console.log` im Produktivcode.
Keine ausgehenden HTTP-Aufrufe außer den Anbieter-Embeds, die jetzt in der
Privacy-API stehen.

## 33 mod-spezifisch

`elang_supports` deklariert 9 Features. Grade-Integration mit `reset`-Pfad,
Completion, Groups, Groupings, Backup. Mit RC-01 ist die Modul-API vollständig.

## 34–35 Release-Dokumentation und Reifegrade

Neun Dokumente unter `docs/dev/`. Gegen die RC-Kriterien (35.4) offen:

1. Der manuelle Screenreader-Smoke.
2. Alle nicht blockierenden Läufe **auf einem einzigen SHA**. Zuletzt stammten
   sie von verschiedenen Ständen.

---

# Befunde

## [P1] RC-01 — Kurs-Reset entfernte keine Lernendendaten — **behoben**

`elang_reset_userdata()` existierte nicht. Ein Kurs-Reset ließ jeden Versuch
stehen; der nächste Jahrgang hätte die Antworten des vorigen vorgefunden.

**Behoben** samt Notenbuch-Reset, standardmäßig abgeschaltet, mit fünf Tests.

## [P2] RC-02 — Anbieterübertragung nicht in der Privacy-API — **behoben**

Ein Anbieter-Embed überträgt IP-Adresse und Geräteangaben. Kein Datenbankfeld,
deshalb bisher nirgends deklariert.

**Behoben:** `add_external_location_link('videoprovider')` mit Erklärung, dazu
`referrerpolicy="strict-origin"`.

## [P2] RC-03 — Manuelle Barrierefreiheitsabnahme offen

Vier Prüfungen mit echten Hilfstechnologien. Kein Test kann sie ersetzen; sie
stehen als offene Tabelle in `docs/dev/accessibility.md`.

**Nicht durch mich schließbar.**

## [P3] RC-04 — Freigabeevidenz auf verschiedenen Ständen

Playwright, k6, JMeter und der Migrationslauf stammten zuletzt aus
unterschiedlichen Commits. `docs/dev/release-policy.md` verlangt einen
gemeinsamen SHA.

**Nicht durch mich schließbar.**

---

# Empfohlene Freigabeentscheidung

**Freigabe als Release Candidate: ja.** Es ist kein P0 offen; die zwei Befunde
dieses Durchgangs sind behoben und durch Tests abgesichert.

**Freigabe als Stable: noch nicht** — RC-03 und RC-04 sind offen, und beide
liegen außerhalb dessen, was ich prüfen kann. Sie sind keine Codearbeit,
sondern Nachweise, die eine Person erbringen muss.
