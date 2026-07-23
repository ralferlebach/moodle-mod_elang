## Session-Ende – mod_elang 2.0 · Session 001

**Datum:** 23. Juli 2026
**Thema:** Projektaufsetzung — Infrastruktur, Anforderungen, Blueprint

---

### Was wurde erledigt?

- [x] Auswertung der technischen Gesamtbewertung zu `mod_elang` 1.x und Sichtung
      des V1-Quellstands (`src/server`, `db/install.xml`, `pix/`, `version.php`)
- [x] Übernahme der Infrastrukturmuster aus dem Plugin-Stub
      (`local_instantcoursecompletion`): CI-Workflows, Makefile, phpcs, gitattributes,
      Dokumentations- und Prompt-Struktur
- [x] Plugin-Skelett `mod/elang/`: `version.php`, `lib.php`, `mod_form.php`,
      `view.php`, `index.php` (nebst Event `course_module_instance_list_viewed`
      für Moodle 4.5), `db/install.xml`, `db/access.php`, `db/install.php`,
      `db/upgrade.php`, Sprachdateien en/de, Null-Privacy-Provider
- [x] `pix/monologo.svg`, `pix/icon.svg` und `pix/monologo.png`
      (einfarbig `#212529`, 24×24)
- [x] `classes/event/course_module_viewed.php` sowie Aufruf von
      `completion_info::set_module_viewed()` in `view.php`
- [x] Korrektur: `FEATURE_BACKUP_MOODLE2`, `FEATURE_COMPLETION_HAS_RULES` und
      `FEATURE_GRADE_HAS_GRADE` werden im Skelett bewusst mit `false`
      beantwortet. Andernfalls sucht der Kern `backup_elang_activity_task`,
      `\mod_elang\completion\custom_completion` und `elang_grade_item_update()`;
      das Anlegen einer Aktivität und jede Kurssicherung brächen ab.
- [x] `elang_supports(FEATURE_MOD_PURPOSE)` → `MOD_PURPOSE_ASSESSMENT`,
      `elang_is_branded()` → `false`
- [x] CI-Matrix stichprobenartig über die Spanne 4.5 – 5.3 gelegt
      (4.5/8.1, 4.5/8.3, 5.0/8.2, 5.2/8.3, 5.2/8.4, 5.3-dev/8.4); 5.3-Jobs
      nicht blockierend
- [x] PHPUnit-Basistests, Testdatengenerator, Behat-Feature
- [x] `docs/materials/`: Lasten-/Pflichtenheft und Blueprint, Kompaktfassung,
      Machbarkeitsprüfung der Zusatzanforderungen, Migrationskonzept,
      Lizenz-/Herkunftsdokument, Ideen-Backlog
- [x] `docs/prompt-templates/`: sessionstart, sessionende, Planungs-Prompt

---

### Entscheidungen getroffen

| Thema | Entscheidung | Begründung |
|---|---|---|
| Vorgehen | Kompatible Neuentwicklung statt Modernisierung | Datenmodell, Frontend, API, Reporting, Datenschutz und Tests müssten ohnehin vollständig ersetzt werden |
| Komponentenname | bleibt `mod_elang` | Upgradepfad statt Parallelinstallation; Namensfrage im Plugins-Verzeichnis separat zu klären |
| Zielplattform | **Moodle 4.5 LTS bis 5.3 LTS** | Verbreitung: 4.5 ist die meistgenutzte LTS-Version; ein Plugin ab 5.2 erreicht die Bestandsinstallationen von `mod_elang` nicht. Releaseziel bleibt 5.3 (Code Freeze 24.08., Release 05.10.) |
| PHP | **8.1 bis 8.4**, Code auf PHP-8.1-Sprachstand | ergibt sich zwingend aus der Spanne: 4.5 verlangt mindestens 8.1, 5.2/5.3 mindestens 8.3 und unterstützen 8.4 |
| Kursintegration | `index.php` **und** `courseformat\overview` ausliefern | die Aktivitätenübersicht existiert erst ab 5.0; auf 4.5 bleibt die Instanzliste der Weg |
| Versionsunterschiede | Fähigkeitsprüfung statt Versionsvergleich | hält den Fachcode frei von Plattformwissen (L-Q12) |
| 5.3 in der CI | über `main`, nicht blockierend | bewegliches Ziel darf die Pipeline nicht rot färben |
| Aktivitätszweck | `MOD_PURPOSE_ASSESSMENT` | Aktivität bewertet, führt Versuche und schreibt ins Gradebook — wie quiz und assign |
| Privacy im Skelett | `null_provider` | Skelett speichert keine personenbezogenen Daten; Ablösung ist Freigabevoraussetzung für Phase 2 |
| Datenexport | Dataformat API des Kerns | CSV/XLSX/ODS/JSON ohne formatspezifischen Eigencode |
| Dokumentexport | DOCX und ODT nativ über `ZipArchive` | keine Fremdbibliothek, kein Konverter-Zwang, gut testbar |
| Standbilder | browserseitige Erfassung zur Autorenzeit | keine Serverabhängigkeit; `ffmpeg` nur als optionale Ausbaustufe |
| KI-Untertitel | zurückgestellt | Moodle-KI-Subsystem kennt keine Transkriptionsaktion; Aktionen sind Kernbestandteil |
| KI-Videogenerierung | nicht empfohlen | keine Kernaktion, hohe Kosten, geringer didaktischer Ertrag |
| Umfang 2.1 | fünf Vorhaben **verbindlich zugesagt** (Kap. 19) | bestes Verhältnis von didaktischem Nutzen zu Aufwand; 4,2–6,1 PW |
| Stabile Schlüssel | `cuekey` und `gapkey` bereits in 2.0 | ohne sie wäre die für 2.1 zugesagte Neubewertung nachträglich nur per Datenmigration umsetzbar |
| Maintainerschaft | Übergabe angestrebt | Kontakt mit Christophe Demko ist aufgenommen; Rückfallweg bleibt bis zur schriftlichen Bestätigung bestehen |

---

### Entwurfsentscheidungen geändert / zurückgestellt

Gegenüber der technischen Gesamtbewertung wurden drei Punkte präzisiert:

1. **Zielplattform.** Die Bewertung nannte Moodle 5.3 als alleiniges Ziel. Die
   Vorgabe lautet nun: lauffähig **ab Moodle 4.5**, Releaseziel weiterhin 5.3.
   Das kostet Sprachstand (PHP 8.1 statt 8.3) und erzwingt eine zweigleisige
   Kursintegration, erreicht dafür aber die Bestandsinstallationen.
2. **Privacy-Provider.** Statt sofort einen vollständigen Provider zu schreiben,
   der noch nichts zu beschreiben hätte, steht ein `null_provider` mit
   dokumentierter Ablösepflicht.
3. **Umfang 2.1.** Die fünf Backlog-Vorhaben mit dem besten Nutzen-Aufwand-
   Verhältnis wurden aus dem Backlog in den verbindlichen Umfang von 2.1 gehoben
   (Blueprint Kap. 19). Eines davon — die nachträgliche Anerkennung von
   Antwortvarianten — hat eine **Rückwirkung auf das 2.0-Datenmodell** und wurde
   deshalb dort als Vorleistung verankert.

Nicht mehr offen: die Maintainerfrage. Der Kontakt zu Christophe Demko ist
aufgenommen; bis zur schriftlichen Bestätigung bleibt der Rückfallweg dokumentiert,
und das Migrationskonzept ist so gebaut, dass es beide Wege trägt.

---

### Offene Punkte für die nächste Session

- [ ] Erster vollständiger CI-Lauf über die Matrix 4.5 – 5.3
- [ ] Referenzübungen und erwartete Bewertungen aus V1 festschreiben
      (Grundlage für `answer_evaluator`)
- [ ] Produktionsnahe V1-Datenmenge für Migrationstests beschaffen
- [ ] Schriftliche Bestätigung der Maintainer-Übergabe abwarten
      (`Lizenz_und_Herkunft.md`, Kap. 3)
- [ ] Phase 2 beginnen: `db/install.xml` um das versionierte Datenmodell erweitern —
      einschließlich `cuekey` und `gapkey` als Vorleistung für 2.1-2
- [ ] `classes/courseformat/overview.php` ergänzen (wirkt ab Moodle 5.0)

---

### Testlauf-Ergebnis

```
PHPUnit: nicht ausgeführt (keine Moodle-Instanz in der Arbeitsumgebung)
PHPCS:   nicht ausgeführt
PHPDoc:  nicht ausgeführt
Behat:   nicht ausgeführt
```

Der erste vollständige CI-Lauf steht noch aus. Bis dahin gilt das Skelett als
**ungeprüft**.
