#!/bin/bash
# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

#
# Verify that every committed AMD build artefact matches its source.
#
# moodle-plugin-ci runs the full Grunt task set with --max-lint-warnings=0 and
# then fails the build for any file under amd/build/ that Grunt would have
# written differently — "File is stale and needs to be rebuilt". This script
# runs Grunt the same way, so a lint warning fails here rather than in CI.
# Checking the minified JavaScript alone is not
# enough: a source map embeds the original source in `sourcesContent`, so a
# change to nothing but a comment leaves the .min.js byte-identical while the
# .map differs. That exact case has slipped through twice.
#
# Usage, from a Moodle tree with this plugin installed at mod/elang and Moodle's
# own npm dependencies present:
#
#   bash mod/elang/tools/check_amd_builds.sh
#
# Exits non-zero and names every file that would change.
#

set -u

plugindir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
moodleroot="$(cd "$plugindir/../.." && pwd)"
grunt="$moodleroot/node_modules/.bin/grunt"

if [[ ! -x "$grunt" ]]; then
    echo "Grunt nicht gefunden unter $grunt."
    echo "Im Moodle-Root 'npm ci' ausfuehren."
    exit 2
fi

before="$(mktemp -d)"
trap 'rm -rf "$before"' EXIT
cp -a "$plugindir/amd/build/." "$before/"

# --max-lint-warnings=0 is what moodle-plugin-ci passes, and without it a plain
# `grunt` run reports lint warnings and still exits 0. Two findings have reached
# CI that way. The value must be attached with "=": `--max-lint-warnings 0`
# makes Grunt read the 0 as a task name and fail with "Task \"0\" not found".
echo "Grunt laeuft in $plugindir ..."
if ! (cd "$plugindir" && "$grunt" --max-lint-warnings=0); then
    echo "Grunt selbst ist fehlgeschlagen - siehe Ausgabe oben."
    exit 1
fi

rc=0
for built in "$plugindir"/amd/build/*; do
    name="$(basename "$built")"
    if ! cmp -s "$built" "$before/$name"; then
        echo "Veraltet: amd/build/$name"
        rc=1
    fi
done

# A file Grunt no longer produces would also fail the CI check.
for kept in "$before"/*; do
    name="$(basename "$kept")"
    if [[ ! -e "$plugindir/amd/build/$name" ]]; then
        echo "Nicht mehr erzeugt: amd/build/$name"
        rc=1
    fi
done

if [[ $rc -ne 0 ]]; then
    echo
    echo "Die neu gebauten Artefakte in amd/build/ in den Arbeitsbaum zurueckkopieren"
    echo "und mit ausliefern - inklusive der .map-Dateien."
    exit 1
fi

echo "Alle Artefakte in amd/build/ entsprechen ihren Quellen."
