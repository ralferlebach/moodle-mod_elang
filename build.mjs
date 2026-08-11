// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Development-only build script. Bundles the React/TypeScript authoring editor
// with esbuild into a single self-contained script that exposes a global,
// window.mod_elang_editor, with a mount(element, config) method.
//
// The bundle is written to js/vendor/react/editor.bundle.js, deliberately NOT
// to amd/build/. Two Moodle tooling facts drive this:
//   1. moodle-plugin-ci's `grunt` check WIPES amd/build/ and re-runs Grunt,
//      then flags any file Grunt did not regenerate ("no longer generated and
//      likely should be deleted"). React cannot be built by Moodle's Grunt
//      (rollup/babel) pipeline, so a prebuilt bundle in amd/build/ always
//      fails that check.
//   2. Moodle's Grunt and moodle-plugin-ci stat every <location> declared in
//      thirdpartylibs.xml; a build artefact that is absent from a checkout
//      (e.g. when amd/build/ is gitignored) aborts the JS lint job.
// Placing the bundle under js/vendor/react/ (a plain committed directory that
// Grunt never touches) and declaring that directory in thirdpartylibs.xml
// avoids both. edit.php loads the bundle as a regular page script via
// $PAGE->requires->js(); amd/src/editor.js then reads the exposed global.
import {build} from 'esbuild';
import {writeFileSync} from 'fs';

const result = await build({
    entryPoints: ['js/src/mount.tsx'],
    bundle: true,
    format: 'iife',
    globalName: 'mod_elang_editor',
    target: ['es2018'],
    minify: true,
    sourcemap: true,
    write: false,
    outfile: 'js/vendor/react/editor.bundle.js',
    jsx: 'automatic',
    define: {'process.env.NODE_ENV': '"production"'},
    banner: {js: '/*! mod_elang editor bundle (GPLv3+). Bundled third-party components: React 18.3.1 (MIT), ReactDOM 18.3.1 (MIT), Scheduler 0.23.2 (MIT); upstream https://react.dev. Built by build.mjs. */'},
    // esbuild assigns the module namespace object to the global; expose the
    // default export's mount as window.mod_elang_editor for the page loader.
    footer: {js: 'window.mod_elang_editor = (mod_elang_editor && mod_elang_editor.default) ? mod_elang_editor.default : mod_elang_editor;'},
    logLevel: 'info',
});

for (const file of result.outputFiles) {
    writeFileSync(file.path, file.text);
}
console.log('Built js/vendor/react/editor.bundle.js (+ .map)');
