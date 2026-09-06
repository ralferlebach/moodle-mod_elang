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
# Run the static checks and report by exit code.
#
# This exists because of a specific mistake. The checks were being run by hand
# and read by eye, with the output piped through `tail`. A clean phpcs run ends
# with a timing line — and so does a run with twenty findings, because the
# findings are printed above it. Reading the tail therefore looked identical in
# both cases, and a real error reached CI while the local run "looked clean".
#
# Nothing here is read by eye. Every check reports through its exit code, and
# the full output of a failing one is shown.
#
# Usage, from a Moodle tree with this plugin at mod/elang:
#
#   bash mod/elang/tools/verify.sh
#

set -u

plugindir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
moodleroot="$(cd "$plugindir/../.." && pwd)"
failed=()

# Run one check, keeping its output only to show it when it fails.
run() {
    local name="$1"
    shift
    local output
    if output="$("$@" 2>&1)"; then
        echo "  ok    $name"
    else
        echo "  FAIL  $name"
        echo "$output" | sed 's/^/        /'
        failed+=("$name")
    fi
}

echo "Statische Pruefungen fuer $plugindir"

# PHPCS_BIN lets a checkout that keeps moodle-cs outside the Moodle tree — as
# moodle-plugin-ci does — point at it, rather than the check being skipped and
# the skip being mistaken for a pass.
phpcs_bin="${PHPCS_BIN:-}"
if [[ -z "$phpcs_bin" ]]; then
    for candidate in \
        "$moodleroot/vendor/bin/phpcs" \
        "$moodleroot/../ci/vendor/bin/phpcs" \
        "$HOME/cs/vendor/bin/phpcs"; do
        if [[ -x "$candidate" ]]; then
            phpcs_bin="$candidate"
            break
        fi
    done
fi
if [[ -z "$phpcs_bin" ]] && command -v phpcs >/dev/null; then
    phpcs_bin="$(command -v phpcs)"
fi

if [[ -n "$phpcs_bin" ]]; then
    run "phpcs (moodle)" "$phpcs_bin" --standard=moodle --severity=1 "$plugindir"
else
    echo "  FAIL  phpcs — nicht gefunden; PHPCS_BIN setzen"
    failed+=("phpcs")
fi

if [[ -f "$moodleroot/local/moodlecheck/cli/moodlecheck.php" ]]; then
    # moodlecheck exits 0 even when it reports problems, so its own output has
    # to be inspected — the one place where a grep is the check.
    if php "$moodleroot/local/moodlecheck/cli/moodlecheck.php" --path=mod/elang 2>&1 | grep -q "<e>"; then
        echo "  FAIL  moodlecheck"
        php "$moodleroot/local/moodlecheck/cli/moodlecheck.php" --path=mod/elang 2>&1 | grep -B2 "<e>" | sed 's/^/        /'
        failed+=("moodlecheck")
    else
        echo "  ok    moodlecheck"
    fi
fi

if [[ -f "$plugindir/tools/mustache_check.php" ]]; then
    run "mustache" php "$plugindir/tools/mustache_check.php"
fi

if [[ -f "$plugindir/package.json" ]] && [[ -d "$plugindir/node_modules" ]]; then
    run "tsc --noEmit" npx --prefix "$plugindir" tsc --noEmit --project "$plugindir/tsconfig.json"
fi

if command -v actionlint >/dev/null && [[ -d "$plugindir/.github/workflows" ]]; then
    run "actionlint" bash -c "cd '$plugindir' && actionlint -shellcheck= -pyflakes="
fi

echo
if [[ ${#failed[@]} -gt 0 ]]; then
    echo "Fehlgeschlagen: ${failed[*]}"
    exit 1
fi

echo "Alle statischen Pruefungen bestanden."
