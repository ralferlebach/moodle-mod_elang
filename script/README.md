# `elangscript` subplugins

This directory is where `elangscript` subplugins live, one subdirectory per
subplugin — for example `script/hangul/` for a Korean handler or
`script/hanzi/` for a Chinese one. It is declared as the subplugin type's
location in `db/subplugins.json` and is intentionally empty in the core
plugin: no non-Latin script is bundled by default (see
`docs/materials/Lastenheft_Pflichtenheft_Blueprint.md`, chapter 10.2).

This file exists only so the directory itself is present on disk and in
version control (Git does not track empty directories). Without it, Moodle
logs `Invalid subtype directory 'mod/elang/script' detected` on every plugin
scan, because a subplugin type declared in `db/subplugins.json` is expected
to resolve to a real directory even when it currently contains zero
subplugins.

## What a subplugin here must provide

A directory `script/<name>/` with:

- a standard Moodle plugin skeleton (`version.php` with
  `$plugin->component = 'elangscript_<name>'`, etc.);
- a class `\elangscript_<name>\handler` at `classes/handler.php` implementing
  `\mod_elang\local\grading\script_handler`
  (`get_supported_codes()`, `normalise_for_exact()`,
  `normalise_for_word_recognised()`).

`\mod_elang\local\grading\script_handler_manager` discovers installed
subplugins automatically; nothing else needs to be registered.
