# Wann das Alt-Schema aus Version 1 verschwindet

Das Plugin trägt noch Spuren seines Vorgängers: die V1-Tabellen und die Spalte
`elang.options`. Dieses Dokument sagt, wann sie weg dürfen, wer das entscheidet
und was bis dahin passiert — damit „Legacy" nicht zu „für immer" wird.

## Was noch da ist

| Was | Warum es noch existiert |
|---|---|
| V1-Tabellen (`elang_stats`, `elang_users`, …) | Quelle der Migration; solange sie gebraucht wird, kann nichts gelöscht werden |
| `elang.options` | Das rohe JSON-Optionsfeld aus V1. `v1_options_mapper` liest es aus; die Rohfassung bleibt als Rückfallebene, solange die Migration nicht abgenommen ist |
| `elang.migrationapproveduserid`, `migrationapprovedtime` | Die Abnahme selbst. Sie bleiben **dauerhaft** — auch nach dem Abbau |

Die Abnahmespalten sind bewusst keine Wegwerfdaten. Sie sind der Nachweis, dass
jemand die migrierten Inhalte angesehen und freigegeben hat, und dieser Nachweis
überlebt den Abbau der Quelle, aus der migriert wurde.

## Die Bedingung

`v1_decommissioner::blockers()` ist die einzige Autorität. Sie gibt eine leere
Liste zurück — und erst dann darf abgebaut werden — wenn **alle** zutreffen:

1. **Keine unmigrierte V1-Aktivität** ist übrig.
2. **Keine migrierte Aktivität ohne Abnahme** ist übrig; jede hat
   `migrationapproveduserid` gesetzt.
3. Für `elang.options` zusätzlich: mindestens **eine** Aktivität wurde je
   abgenommen.

Bedingung 3 sieht überflüssig aus, ist es aber nicht. `options` existiert auf
**jeder** frischen Installation, auch auf einer, die nie eine V1 gesehen hat.
Ohne diese Prüfung würde eine Site ohne V1-Tabellen die Spalte einfach
verlieren — „nichts zu migrieren" und „alles migriert" sind nicht dasselbe.

## Wer entscheidet

Der Abbau ist **nie** automatisch. Kein Upgrade-Schritt, keine geplante Aufgabe
und kein Web-Aufruf löst ihn aus. Er läuft ausschließlich über

```bash
php mod/elang/cli/decommission_v1.php
```

von einer Person mit Serverzugang, die vorher die Blocker gesehen hat. Ein
Datenverlust, den ein Cron ohne Zutun auslöst, ist keine Migration, sondern ein
Unfall.

## Der Ablauf

1. **Migrieren** — `mod_elang\task\migrate_v1_activities_task` oder
   `cli/migrate_v1.php`.
2. **Prüfen** — `v1_verifier` vergleicht Migriertes mit der Quelle;
   `admin_migrate_v1.php` zeigt das Ergebnis.
3. **Abnehmen** — je Aktivität, über die Administrationsseite. Setzt
   `migrationapproveduserid` und `migrationapprovedtime`.
4. **Abbauen** — `cli/decommission_v1.php`. Zeigt zuerst die Blocker; ohne
   Blocker fragt es nach und löscht dann die V1-Tabellen und `elang.options`.

Zwischen 3 und 4 liegt bewusst kein Zeitlimit. Solange die V1-Tabellen stehen,
ist die Migration umkehrbar; danach nicht mehr. Das ist die Sicherung, für die
sie noch da sind.

## Ab wann der Code selbst verschwinden kann

Die Migrationsklassen (`classes/local/migration/`) und die beiden CLI-Skripte
bleiben, solange irgendeine unterstützte Site noch von V1 kommen könnte. Sie
sind inert: `v1_detector::v1_tables_present()` beantwortet das in einer Abfrage,
und ohne V1-Tabellen tut keine von ihnen etwas.

**Der Ausstieg:** Mit der ersten Hauptversion nach 2.0, deren
Mindest-Moodle-Version über der letzten liegt, die V1 je unterstützt hat
(Moodle 3.4), entfällt jeder Migrationspfad von V1 — dann können
`classes/local/migration/`, `cli/migrate_v1.php`, `cli/decommission_v1.php`,
`admin_migrate_v1.php` und die zugehörigen Sprachstrings entfernt werden.

Die Abnahmespalten bleiben auch dann. Zu entfernende Dateien gehören in
`db/removed_files.txt`, sonst überleben sie ein ZIP-Update — dafür gibt es den
`stale-files`-CI-Job.

## Was heute geprüft ist

`tests/local/migration/v1_decommissioner_test.php` deckt ab, dass nichts
abgebaut wird, solange ein Blocker besteht, dass `options` ohne je erfolgte
Abnahme stehen bleibt, und dass der Abbau nach vollständiger Abnahme greift.
