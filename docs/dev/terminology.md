# Verbindliche Terminologie

Dieses Dokument legt fest, wie die zentralen Begriffe von mod_elang heißen — auf
Englisch als Referenzsprache und auf Deutsch. Für alle weiteren Sprachen
beschreibt es die Prinzipien, nicht die Wörter: die gehören der
AMOS-Übersetzungsgemeinschaft.

Es steht hier, weil die Terminologie bisher nur implizit in den Sprachdateien
definiert war. Vier Review-Runden haben dieselben Begriffe wiederholt
korrigieren müssen, und ohne festgehaltene Entscheidung passiert das wieder,
sobald jemand Neues übersetzt.

## Die Begriffe

| Englisch (sichtbar) | Deutsch | Nicht verwenden |
|---|---|---|
| subtitle | Untertitel | cue, Cue, Block, Segment |
| transcript | Transkript | Abschrift, Mitschrift |
| gap | Lücke | Leerstelle, Auslassung |
| hint | Hinweis | Hilfe, Tipp |
| full transcript with answers | Musterlösung | Lösungstranskript |
| worksheet | Arbeitsblatt | — |
| attempt | Versuch | Durchgang |
| participant | Teilnehmer/in | Lernende, Person, Nutzer/in |
| embedded player | eingebetteter Player | Rahmen, Frame, iframe |
| video dictation | Video-Diktat | Sprachübung, Hör-Garten |

Die vier Ergebniszustände heißen durchgehend gleich — in der Oberfläche, im
Bericht und in den Datenschutzbeschreibungen:

| Englisch | Deutsch |
|---|---|
| exact | exakt |
| recognised | erkannt |
| incorrect | falsch |
| empty | leer |

## cue gegen subtitle

`cue` ist der korrekte Terminus der WebVTT-Spezifikation und bleibt dort, wo
tatsächlich das Datenobjekt gemeint ist:

- in Bezeichnern (`editor_addcue`, `cuekey`, `validate_cuewhere`),
- in der Datenbank,
- in `error_*`, `validate_*`, `verify_*` und `import_*` — also in Meldungen, die
  sich an Administration und Fehlersuche richten.

In allem, was eine lehrende Person beim Erstellen einer Übung sieht, heißt es
**subtitle** beziehungsweise **Untertitel**. Die W3C-Spezifikation selbst nennt
den typischen Fall eines cue „one subtitle", und für eine Autorenoberfläche ist
das die verständlichere Bezeichnung.

Ein Bezeichner wird deshalb **nicht** umbenannt, wenn sich seine Beschriftung
ändert. `editor_addcue` heißt weiterhin so und trägt den Text „Add subtitle".
Das sieht auf den ersten Blick inkonsistent aus, ist aber richtig: eine
Umbenennung von String-IDs verwirft in AMOS jede bestehende Übersetzung.

## Prinzipien für weitere Sprachen

**Ein Begriff, ein Wort.** Wenn eine Sprache für „Untertitel" zwei mögliche
Wörter hat, wird eines gewählt und überall verwendet — auch dort, wo das andere
im Einzelfall schöner klänge.

**Der Produktname wird nicht übersetzt, sondern entschieden.** „Video-Diktat"
ist keine Übersetzung von „Video dictation", sondern die deutsche Benennung
derselben Sache. Andere Sprachen setzen den Namen ebenfalls nicht mechanisch aus
„Video" und „Diktat" zusammen, sondern wählen, was in dieser Sprache eine solche
Übung heißt. Wo das ohne muttersprachliche Entscheidung geschah, steht es im
Kopf der jeweiligen Sprachdatei.

**Rollen werden nicht benannt, Berechtigungen schon.** „Nur für Lehrende" ist
falsch, weil eine Site diese Rolle vielleicht nicht hat; „nur für berechtigte
Lehrende" beschreibt, was tatsächlich zählt — eine Capability.

**Zählangaben brauchen keine Kongruenz.** Statt „{$a} Untertitel" wird
„Untertitel: {$a}" geschrieben. Im Deutschen ist das eine Stilfrage, im
Polnischen, Tschechischen, Ukrainischen, Sorbischen, Finnischen, Ungarischen
und Türkischen ist es der Unterschied zwischen richtig und falsch: dort hängt
die Form des Substantivs von der Zahl ab, und Sorbisch hat zusätzlich einen
Dual. Die Regel gilt deshalb für alle Sprachen, damit der englische Quellstring
überall dieselbe Form hat.

**Keine mechanische Ableitung zwischen verwandten Sprachen.** `zh_tw` wurde
nicht aus `zh_cn` konvertiert, `dsb` nicht aus `hsb`, `ca` nicht aus `es`. Der
teuerste Fehler dieses Projekts entstand genau so: eine Ersetzungsregel machte
im brasilianischen Paket aus „Pontuação média" — dem Durchschnitt — das
sinnlose „Pontuação mídia". Wortersetzung ist nur dort zulässig, wo die
Bedeutung eindeutig ist, und muss auf Wortgrenzen laufen.

**Regionalpakete überschreiben nur, was sich unterscheidet.** `es_mx` und
`pt_br` erben von `es` und `pt`. Was dort gleich liest, bleibt im Elternpaket,
damit es nicht zweimal gepflegt werden muss.

## Was der Test absichert

`tests/lang_strings_test.php` prüft, was sich prüfen lässt: Schlüsselgleichheit,
Platzhalterparität, keine doppelten IDs, und dass PHP aus jeder Sprachdatei
dieselben Strings lädt, die sie zu deklarieren scheint.

Terminologie prüft er nicht. Dafür ist dieses Dokument da, und danach die
muttersprachliche Abnahme.
