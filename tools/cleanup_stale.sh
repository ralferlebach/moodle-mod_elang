#!/usr/bin/env bash
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

# One-time cleanup: remove the stale React bundle artefacts that used to live in
# amd/build/ before alpha.66 moved the bundle to js/vendor/react/. They cannot
# be removed by extracting a release ZIP (a ZIP only adds/updates files), and
# while present they break the CI JS lint job, because moodle-plugin-ci wipes
# amd/build/ and re-runs Grunt, then flags any file Grunt did not regenerate.
#
# Run once from the plugin root, then commit:
#   bash tools/cleanup_stale.sh
#   git commit -m "Remove stale editor_lazy build artefacts (moved to js/vendor/react)"

set -euo pipefail

cd "$(dirname "$0")/.."

stale=(
    "amd/build/editor_lazy.min.js"
    "amd/build/editor_lazy.min.js.map"
)

removed=0
for file in "${stale[@]}"; do
    if git ls-files --error-unmatch "$file" >/dev/null 2>&1; then
        git rm --quiet "$file"
        echo "Removed from git: $file"
        removed=$((removed + 1))
    elif [ -e "$file" ]; then
        rm -f "$file"
        echo "Removed untracked file: $file"
        removed=$((removed + 1))
    fi
done

if [ "$removed" -eq 0 ]; then
    echo "Nothing to remove: amd/build/ is already clean."
else
    echo "Done. Now commit the removal."
fi
