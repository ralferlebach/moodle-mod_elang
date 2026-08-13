# Bundled React runtime for the authoring editor

The authoring editor (`js/src`, entry `js/src/mount.tsx`) is written in
TypeScript/React and compiled by the development-only esbuild toolchain
(`build.mjs`, `make react`) into the prebuilt bundle in this directory:

    js/vendor/react/editor.bundle.js

The bundle contains React, ReactDOM and Scheduler. See
`../../../thirdpartylibs.xml` for the authoritative third-party declaration and
`readme_moodle.txt` in this directory for the provenance and the reproducible
build steps.

## How it is loaded

The bundle is built as an **IIFE**, not as an AMD module: it is loaded by
`edit.php` with `$PAGE->requires->js()` and exposes `window.mod_elang_editor`.
The small AMD module `mod_elang/editor` (`amd/src/editor.js`) prefetches the
language strings and then calls into it.

It deliberately does **not** live under `amd/build/`: moodle-plugin-ci wipes that
directory before re-running Grunt and would flag a bundle Grunt cannot
regenerate, since it has no `amd/src` counterpart. Keeping it here also gives
`thirdpartylibs.xml` a stable `<location>` that always exists in a checkout,
which Moodle's Grunt pipeline and moodle-plugin-ci `stat()`.

## Licence

React, ReactDOM and Scheduler are distributed under the MIT licence — see
`LICENSE-react.txt` in this directory (https://react.dev,
https://github.com/facebook/react).
