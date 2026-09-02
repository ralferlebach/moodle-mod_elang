# Rechte in mod_elang

Wer was darf, und wo es durchgesetzt wird. Die Tabelle ist aus
`db/access.php` abgeleitet; bei Abweichung gilt der Code.

| Recht | Art | Risiko | Standardrollen |
|---|---|---|---|
| `mod/elang:addinstance` | write | XSS | editingteacher, manager |
| `mod/elang:view` | read | – | student, teacher, editingteacher, manager |
| `mod/elang:attempt` | write | – | **nur student** |
| `mod/elang:manage` | write | XSS | editingteacher, manager |
| `mod/elang:useregex` | write | XSS | **nur manager** |
| `mod/elang:viewreports` | read | personenbezogen | teacher, editingteacher, manager |
| `mod/elang:exportreports` | read | personenbezogen | teacher, editingteacher, manager |
| `mod/elang:deleteattempts` | write | Datenverlust | editingteacher, manager |
| `mod/elang:exporttranscript` | read | – | student, teacher, editingteacher, manager |
| `mod/elang:exportsolution` | read | – | teacher, editingteacher, manager |

## Was dabei leicht überrascht

**`attempt` hält nur `student`.** Eine Lehrkraft kann die Übung ansehen, aber
keinen Versuch starten — der Player lädt für sie nicht. Das ist Absicht: ein
Versuch erzeugt Bewertungsdaten. Wer als Lehrkraft prüfen will, wie sich die
Übung anfühlt, nutzt die Vorschau im Editor oder ein Testkonto. Ein
Playwright-Test ist genau darüber gestolpert, bevor der Zusammenhang klar war.

**`useregex` hält nur `manager`.** Ein regulärer Ausdruck als akzeptierte
Antwortvariante wird gegen Lernendeneingaben ausgewertet; ein unglücklicher
Ausdruck kann eine Anfrage sehr lange beschäftigen. Deshalb liegt das Recht
höher als das übrige Autorenrecht und wird in `authoring_helper` **je Variante**
geprüft, nicht einmal pro Speichervorgang.

**`exporttranscript` hält auch `student`** — Lernende dürfen ihr Arbeitsblatt
mitnehmen. Ob eine konkrete Aktivität es ihnen anbietet, ist eine
Aktivitätseinstellung (`allowtranscriptdownload`), keine Rechtefrage. Ohne diese
zweite Ebene hätte jede Aktivität ihr Arbeitsblatt an jede lernende Person
ausgegeben.

**`exportsolution` ist die Musterlösung.** Für Lernende regelt
`solutionavailability` (`never` / `aftersubmission` / `always`), ob sie sie ohne
dieses Recht bekommen. `elang_can_export_solution()` in `lib.php` ist die
einzige Stelle, an der das entschieden wird.

## Wo die Prüfung stattfindet

| Ort | Was er prüft |
|---|---|
| `lib.php::elang_extend_settings_navigation()` | welche Reiter erscheinen |
| `view.php`, `edit.php`, `media.php`, `report.php`, `transcript.php` | `require_capability()` je Seite |
| `classes/external/attempt_helper` | Kontext, `attempt`, **und** dass der Versuch der aufrufenden Person gehört |
| `classes/external/authoring_helper` | Kontext, `manage`, **und** dass die Version zur Aktivität gehört |
| `version_manager::user_can_access_version_file()` | ob diese Person diese Version als Datei laden darf |

Sichtbarkeit ist **nie** die Absicherung. Ein Reiter, der nicht erscheint, sagt
nichts darüber, ob die zugehörige URL geschützt ist — jede Seite und jede
External Function prüft selbst. `tests/external/security_contract_test.php`
läuft über `db/services.php` und hält das für alle Endpunkte fest.

## Zwei Lehren aus dieser Codebasis

**Eine Capability allein ist selten die ganze Antwort.** `mod/elang:attempt`
hält jede lernende Person im Kurs; ohne die zusätzliche Eigentumsprüfung könnte
jede von ihnen den Versuch jeder anderen lesen und beschreiben. Dasselbe gilt
für `manage` und fremde Versionen.

**Zwei Callbacks sind eine Auswahl, keine Redundanz.** In `lib.php` standen
einmal zwei Datei-Callbacks; Moodle bevorzugt den nach der Komponente
benannten, und das war der mit der schwächeren Prüfung. Draft-Medien waren
darüber erreichbar. Ein Test hält jetzt fest, dass es bei genau einem bleibt.
