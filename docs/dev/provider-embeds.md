# Videos von Dritten: was passiert, und was die Alternativen sind

Entscheidung zu RR-07. Umgesetzt ist **Variante B**: der Anbieter wird erst nach
Zustimmung kontaktiert.

## Das Problem

Bei `mediakind = provider` bettet der Player einen YouTube- oder Vimeo-`<iframe>`
ein. Sobald dieser Rahmen im Dokument steht, verbindet sich der Browser der
lernenden Person mit dem Anbieter. Der erhält dabei:

- die IP-Adresse,
- User-Agent und Geräteangaben,
- alle Cookies, die er auf diesem Browser bereits gesetzt hat.

Das geschieht **beim Aufbau der Seite** — bevor jemand auf Abspielen drückt und
ohne dass die lernende Person etwas tut. Es ist keine Verarbeitung dieses
Plugins, weshalb sie zu Recht nicht im Privacy-Provider steht. Ausgelöst wird
sie trotzdem von der Aktivität.

## Was umgesetzt ist

Statt des Rahmens erscheint eine Hinweisfläche mit dem Anbieternamen, der
Erklärung und einer Schaltfläche. Das `<iframe>` wird **erst beim Klick
erzeugt** — vorher wird seine `src` nie gesetzt, es geht also tatsächlich nichts
hinaus.

Drei Entscheidungen dahinter:

**Site-Einstellung, nicht Aktivitätseinstellung**
(`mod_elang/providerconsent`). Ob ein Anbieter vor der Zustimmung kontaktiert
werden darf, beantwortet die Einrichtung einmal. Pro Übung gefragt, würde daraus
eine didaktische Wahl derjenigen, die die Aktivität zufällig anlegt.

**Zustimmung gilt für die Browsersitzung**, nicht dauerhaft. Ein Reload fragt
nicht erneut; eine gespeicherte Präferenz überdauert die Sitzung, in der sie
gegeben wurde, und hört damit auf, etwas zu sein, dessen Erteilung man bewusst
wahrnimmt.

**Im Zweifel gesperrt.** Geprüft wird `get_config(...) !== '0'`, nicht ein
Boolean-Cast. `get_config()` liefert `false`, solange der Admin-Standard nie
geschrieben wurde — ein Cast hätte „noch nicht entschieden" in „nicht nötig"
verwandelt. Das ist die eine Antwort, die eine Datenschutzkontrolle nicht
versehentlich geben darf. Ein Behat-Lauf hat genau das aufgedeckt.

## Warum der Stream nicht über Moodle geleitet wird

Naheliegende Idee: der Moodle-Server holt das Video und liefert es aus, dann
sieht der Anbieter nur eine IP-Adresse. Für **YouTube** ist das keine gangbare
Option, aus drei Gründen in dieser Reihenfolge:

1. **Die Nutzungsbedingungen untersagen es.** Zugriff auf die Videodaten ist nur
   „through the embeddable player" gestattet. Ein Plugin im offiziellen
   Moodle-Verzeichnis darf Einrichtungen nicht zu einem Vertragsbruch anleiten.
2. **Es wäre nicht haltbar.** Die Segmente liegen hinter signierten,
   IP-gebundenen URLs auf `googlevideo.com`, die sich regelmäßig ändern. Jede
   Umsetzung wäre ein Wettlauf gegen die nächste Änderung.
3. **Es würde die Lernplattform zum CDN machen.** 200 Lernende, die gleichzeitig
   ein Video sehen, sind Bandbreite, die ein Moodle-Server nicht vorhält.

Ein Feature, dessen Betrieb rechtlich angreifbar, technisch instabil und
ressourcenseitig unpassend ist, gehört nicht gebaut — auch nicht als Option.

## Was stattdessen funktioniert

**Datei hochladen**, wo die Lizenz es erlaubt. Kein Dritter beteiligt, volle
Kontrolle über die Wiedergabe. Der Medientyp „Datei" kann das seit jeher, und nur
er erlaubt Untertitel auf dem Bild und das Anhalten an Cue-Grenzen.

**Einen institutionellen Medienserver verwenden** — Opencast, Panopto, Kaltura
oder vergleichbar. Viele Hochschulen betreiben ohnehin einen. Der Medientyp
„direkte URL" nimmt eine solche Adresse **ohne jede Änderung am Plugin** entgegen:

- die IP-Adressen bleiben in der eigenen Infrastruktur,
- die Einwilligungsabfrage entfällt, weil kein Dritter beteiligt ist,
- Untertitelposition und Pausemodus funktionieren vollständig, weil ein direktes
  Medienelement seine Wiedergabezeit meldet — anders als ein Anbieterrahmen.

Der zweite Punkt ist die eigentliche Antwort auf „kann man das über Moodle
routen": man muss nicht routen, wenn die Quelle ohnehin im Haus steht. Der
Hilfetext des Feldes „Adresse der Quelle" weist darauf hin.

## Für Betreibende

| Situation | Empfehlung |
|---|---|
| Eigener Medienserver vorhanden | dessen direkte Datei-URL verwenden; Anbieter gar nicht erst nutzen |
| Material liegt frei lizenziert vor | herunterladen und als Datei hochladen |
| Einwilligung wird zentral eingeholt (Cookie-Banner o. Ä.) | `mod_elang/providerconsent` abschalten |
| Nichts davon trifft zu | Voreinstellung belassen — es wird gefragt |
