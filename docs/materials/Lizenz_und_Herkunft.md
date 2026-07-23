# Lizenz, Herkunft und Namensfragen

**Stand:** 23. Juli 2026 · Risikopunkte 3 und 4 aus
`Lastenheft_Pflichtenheft_Blueprint.md`, Kap. 21.

> Dieses Dokument beschreibt den technischen und organisatorischen Sachstand und
> die daraus abgeleiteten Arbeitsregeln. Es ist **keine Rechtsberatung**. Vor der
> Veröffentlichung sollte eine juristisch zuständige Stelle der eigenen
> Einrichtung die Punkte 1 und 3 bestätigen.

---

## 1. Zwei Lizenzen

| | Version 1 | Version 2 |
| --- | --- | --- |
| Urheber | Université de La Rochelle u. a. | Ralf Erlebach |
| Lizenz | CeCILL-B | GNU GPL v3 or later |
| Zeitraum | 2013–2018 | ab 2026 |

Moodle-Plugins müssen GPL-v3-kompatibel lizenziert sein; die neue Codebasis ist
deshalb GPL v3+. CeCILL-B ist eine permissive Lizenz französischen Rechts mit
einer ausgeprägten **Zitier- und Nennungspflicht**.

**Praktische Folge:** Nicht die Lizenzverträglichkeit ist das Hauptrisiko, sondern
die Nachweisbarkeit — wer welche Zeile woher hat.

---

## 2. Arbeitsregeln für die Entwicklung

1. **Standardweg: Neuimplementierung aus der Spezifikation.** Das fachliche
   Verhalten von V1 (Parserregeln, Normalisierung, Toleranzmaß, Hilfestufen) wird
   als *Verhaltensbeschreibung mit Referenzfällen* festgehalten und daraus neu
   implementiert. Referenzfälle sind Testdaten, kein Code.
2. **Kein Copy-Paste ohne Kennzeichnung.** Wird ausnahmsweise eine Passage
   übernommen, erhält die Datei einen zusätzlichen Herkunftsvermerk mit
   Ursprungsdatei, Ursprungslizenz und ursprünglichem Copyright, und der Vorgang
   wird in diesem Dokument protokolliert (Abschnitt 5).
3. **Keine Übernahme von Fremdbibliotheken aus V1.** Enyo, Bootstrap 3, die
   mitgelieferte jQuery-Version und die Bower-Konfiguration entfallen ersatzlos.
   `thirdpartylibs.xml` wird nur angelegt, wenn tatsächlich Fremdcode ausgeliefert
   wird — derzeit ist das nicht der Fall.
4. **Sprachdateien und Icons** werden neu erstellt, nicht übernommen.
5. **Dokumentation und Screenshots** aus V1 werden nicht übernommen.

---

## 3. Der Komponentenname `mod_elang`

Die Beibehaltung des Namens ist fachlich richtig — sie ist die Voraussetzung für
einen Upgradepfad statt einer Parallelinstallation. Sie wirft aber zwei Fragen auf,
die **vor** einer Veröffentlichung geklärt sein müssen:

1. **Eintrag im Moodle-Plugins-Verzeichnis.** Der Eintrag `mod_elang` liegt bei
   den ursprünglichen Autor:innen.

   **Stand 23.07.2026:** Mit **Christophe Demko**, dem Maintainer von
   `mod_elang` 1.x, ist in der Sache der Fortführung der Maintainerschaft bereits
   Kontakt aufgenommen worden. Das ist der angestrebte und wahrscheinliche Weg.

   Bis eine schriftliche Bestätigung vorliegt, bleibt der Rückfallweg aus
   Abschnitt 3.3 formal bestehen. Zu klären sind im Zuge der Übergabe:
   Übernahme oder Co-Maintainerschaft des Verzeichniseintrags, Umgang mit dem
   bestehenden GitHub-Repository (Fortführung, Fork oder neues Repository) sowie
   Nennung der ursprünglichen Autorenschaft in Release Notes und `README.md`.
2. **Erwartungshaltung der Bestandsnutzer:innen.** Ein Release unter derselben
   Komponente mit vollständig neuem Datenmodell und neuer Oberfläche muss
   unmissverständlich als Hauptversionswechsel kommuniziert werden: Release Notes,
   Upgrade-Hinweis, Migrationsanleitung, ausdrücklicher Hinweis auf die
   Sicherungsempfehlung vor dem Upgrade.

### 3.3 Rückfallvariante

Sollte die Übergabe wider Erwarten nicht zustande kommen, ist die Rückfallvariante
ein neuer Komponentenname mit einem Migrationswerkzeug aus `mod_elang` — technisch
aufwendiger, aber ohne Namenskonflikt. Die Entscheidung sollte **vor** Phase 2
fallen, weil sie das Migrationskonzept berührt.

Solange die Frage offen ist, wird das Migrationskonzept so gebaut, dass es beide
Wege trägt: Die Migration liest die Legacy-Tabellen über deren Namen und ist nicht
davon abhängig, dass Quelle und Ziel dieselbe Komponente sind.

---

## 4. Nennungen

Unabhängig vom rechtlichen Ergebnis: Die ursprüngliche Arbeit wird in `README.md`
und in den Release Notes benannt. Version 2.0 ist eine Neuentwicklung, aber sie
steht erkennbar in der Tradition des Originals — das gehört ausgewiesen.

---

## 5. Protokoll übernommener Passagen

| Datum | Zieldatei | Ursprung | Umfang | Vermerk gesetzt |
| --- | --- | --- | --- | --- |
| — | — | — | — | — |

*(Leer. Stand 2.0.0-alpha.1 enthält keine aus Version 1 übernommene Zeile.)*
