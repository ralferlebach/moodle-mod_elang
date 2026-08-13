Description of the React import into mod_elang
==============================================

This directory holds a prebuilt JavaScript bundle containing the React runtime
used by the mod_elang authoring editor (the "Subtitle Studio").

Libraries and versions
----------------------
* React        18.3.1  (MIT)  https://react.dev  https://github.com/facebook/react
* ReactDOM     18.3.1  (MIT)  https://github.com/facebook/react
* Scheduler     0.23.2 (MIT)  https://github.com/facebook/react (ReactDOM runtime dependency)

The exact versions are pinned in the plugin's package.json and locked in
package-lock.json, which is the authoritative record of what was installed.

Downloaded from
---------------
The libraries are not vendored as upstream source files. They are installed from
the npm registry and compiled into a single bundle:

  https://registry.npmjs.org/react/-/react-18.3.1.tgz
  https://registry.npmjs.org/react-dom/-/react-dom-18.3.1.tgz
  https://registry.npmjs.org/scheduler/-/scheduler-0.23.2.tgz

Reproducible build steps
------------------------
From the plugin root (mod/elang), with Node.js installed:

  npm ci                # install the exact versions from package-lock.json
  node build.mjs        # production build (or: make react)

  Input:  js/src/mount.tsx  (with the rest of js/src)
  Output: js/vendor/react/editor.bundle.js

The production build is minified and, deliberately, carries no source map: a map
roughly triples the shipped payload and exposes the unminified sources. For a
development build with a source map, run:

  node build.mjs --dev  (or: ELANG_BUILD_DEV=1 node build.mjs)

The build is byte-reproducible: building twice from the same lockfile produces an
identical editor.bundle.js.

Local changes
-------------
None. The libraries are bundled unmodified; only the plugin's own sources under
js/src are compiled alongside them. The bundle is emitted as an IIFE exposing
window.mod_elang_editor (see README.md in this directory).
