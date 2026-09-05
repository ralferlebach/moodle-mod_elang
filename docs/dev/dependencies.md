# Abhängigkeiten: Stand, Prüfung, Entscheidungen

Nachweis zu RR-11. Geprüft auf dem Stand `2.0.0-beta.30`, jeweils nach
`npm ci` aus dem eingecheckten Lockfile — nicht `npm install`, damit gemessen
wird, was ausgeliefert wird, und nicht, was heute zufällig aktuell ist.

## Ergebnis

| Ort | Befund |
|---|---|
| `package.json` (Editor) | `npm audit`: **0 vulnerabilities** |
| dieselbe, nur Laufzeit (`--omit=dev`) | **0 vulnerabilities** |
| `tests/playwright/package.json` | **0 vulnerabilities** |

Zwei Befunde zu `esbuild` sind nacheinander geschlossen worden:

- `^0.23` fiel unter GHSA-67mh-4wv8-2f99 (Dev-Server nimmt Anfragen beliebiger
  Seiten entgegen). Angehoben auf `^0.25` in `2.0.0-beta.7`.
- `< 0.28.1` fällt unter GHSA-gv7w-rqvm-qjhr (fehlende Integritätsprüfung der
  heruntergeladenen Binärdatei). Angehoben auf `^0.28` in `2.0.0-beta.30`,
  installiert ist 0.28.2.

Beide betrafen ausschließlich das Bauwerkzeug, nie die ausgelieferte Laufzeit.
Bemerkenswert: **`npm audit` meldete den zweiten nicht** — weder vor noch nach
dem Anheben. Ein Audit ist eine Abfrage gegen eine Datenbank zu einem Zeitpunkt,
kein Beweis der Abwesenheit. Deshalb steht hier, was geprüft **und** was von wem
gemeldet wurde, statt nur „0 vulnerabilities".

Nach dem Anheben baut `js/vendor/react/editor.bundle.js` weiterhin
reproduzierbar: zwei aufeinanderfolgende Läufe liefern denselben Hash
(`868e209c…`), und es entsteht keine Sourcemap.

## Was tatsächlich ausgeliefert wird

Nur **React und ReactDOM**, und auch die nicht als Dateien: sie sind in
`js/vendor/react/editor.bundle.js` einkompiliert. Alles andere — esbuild,
TypeScript, Jest, ts-jest, jsdom, Playwright, die Typdefinitionen — ist
`devDependencies` und existiert in einer Installation überhaupt nicht.

`thirdpartylibs.xml` deklariert React 18.3.1, ReactDOM 18.3.1 und Scheduler;
`node_modules/react/package.json` meldet 18.3.1. Synchron.

## React bleibt auf 18

Eine bewusste Entscheidung, kein Versäumnis.

Moodle bringt ab **5.2** eine eigene React-Laufzeit mit. Das Plugin liefert
sein Bundle aus, weil es ab **4.5** unterstützt wird, wo es keine gibt. Auf 19
zu wechseln hieße, ein zweites React neben das des Kerns zu stellen, sobald
5.2 die Mindestversion wird — zwei Laufzeiten in einer Seite, mit den bekannten
Folgen für Hooks und Kontext.

Der Ausstieg ist an eine Bedingung geknüpft, nicht an ein Datum: sobald die
Mindestversion dieses Plugins auf 5.2 steht (nach dem Support-Ende von 5.1,
voraussichtlich Oktober 2026), entfällt das eigene Bundle **ganz**, und die
Frage nach React 19 stellt sich nicht mehr — dann gilt, was der Kern mitbringt.
Bis dahin folgt 18.3.x den Patchständen.

Notiert in `docs/dev/roadmap.md` unter „React über Moodle Core beziehen"; die
Ladestelle ist in `edit.php` kommentiert.

## esbuild und Jest

- **esbuild ^0.28** — aktueller Hauptstand, Bundle baut reproduzierbar
  (byte-identisch über wiederholte Läufe, geprüft in jedem Inkrement).
- **Jest 29** — Jest 30 verlangt Node ≥ 18 und ändert das Standardverhalten von
  `testEnvironment`. Kein Nutzen für diese Suite, den Jest 29 nicht schon
  liefert; ein Wechsel würde 71 Tests anfassen, um dasselbe Ergebnis zu
  erhalten. Bleibt bei 29, bis ein konkreter Grund auftritt.
- **TypeScript ^5.9** — folgt den Minor-Ständen; `tsc --noEmit` ist ein
  blockierendes CI-Gate, ein Bruch fiele sofort auf.

## Wie der Nachweis wiederholt wird

```bash
cd mod/elang           && npm ci && npm audit && npm audit --omit=dev
cd tests/playwright    && npm ci && npm audit
```

`npm ci` schlägt fehl, wenn `package.json` und `package-lock.json`
auseinanderlaufen — das ist der eigentliche Wert des Kommandos hier, nicht die
Installationsgeschwindigkeit.
