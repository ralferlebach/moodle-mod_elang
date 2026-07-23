<?php
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

namespace mod_elang\local\grading;

/**
 * Locates the script_handler responsible for a given language code.
 *
 * Installed elangscript_* subplugins are discovered through core_component and
 * asked which language/script codes they cover (script_handler::get_supported_codes()).
 * Each subplugin must provide a class \elangscript_<name>\handler implementing
 * script_handler at classes/handler.php within the subplugin.
 *
 * If no installed subplugin claims a code, the request falls back to
 * latin_script_handler, so the plugin remains fully functional for
 * Latin-script languages without any subplugin installed.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class script_handler_manager {
    /**
     * Map of lower-case language/script code to the handler that covers it.
     *
     * @var array<string, script_handler>
     */
    private array $handlersbycode;

    /**
     * The handler used when no installed subplugin claims a code.
     *
     * @var script_handler
     */
    private script_handler $defaulthandler;

    /**
     * Construct the manager.
     *
     * @param script_handler[]|null $handlers Explicit list of handlers to
     *        index, bypassing subplugin discovery. Intended for unit tests;
     *        production code should omit this so installed elangscript
     *        subplugins are discovered normally.
     * @param script_handler|null $defaulthandler Handler used as fallback;
     *        defaults to a new latin_script_handler
     */
    public function __construct(?array $handlers = null, ?script_handler $defaulthandler = null) {
        $this->defaulthandler = $defaulthandler ?? new latin_script_handler();
        $this->handlersbycode = [];

        foreach ($handlers ?? $this->discover_subplugin_handlers() as $handler) {
            foreach ($handler->get_supported_codes() as $code) {
                $this->handlersbycode[strtolower($code)] = $handler;
            }
        }
    }

    /**
     * Return the handler responsible for the given language/script code.
     *
     * Tries an exact match first, then the primary subtag (the part before
     * the first hyphen, e.g. 'zh' for 'zh-Hans'), then falls back to the
     * default Latin-script handler.
     *
     * @param string $language Language/script code, for example 'de', 'ko', 'zh-Hans'
     * @return script_handler The handler to use for this language
     */
    public function get_handler_for_language(string $language): script_handler {
        $code = strtolower(trim($language));

        if ($code === '') {
            return $this->defaulthandler;
        }

        if (isset($this->handlersbycode[$code])) {
            return $this->handlersbycode[$code];
        }

        $primarysubtag = strtolower(strtok($code, '-'));
        if (isset($this->handlersbycode[$primarysubtag])) {
            return $this->handlersbycode[$primarysubtag];
        }

        return $this->defaulthandler;
    }

    /**
     * Instantiate the handler class of every installed elangscript subplugin.
     *
     * @return script_handler[] Handlers of all installed and valid subplugins
     */
    private function discover_subplugin_handlers(): array {
        $handlers = [];

        foreach (\core_component::get_plugin_list('elangscript') as $name => $unused) {
            $classname = '\\elangscript_' . $name . '\\handler';
            if (!class_exists($classname)) {
                continue;
            }

            $handler = new $classname();
            if ($handler instanceof script_handler) {
                $handlers[] = $handler;
            }
        }

        return $handlers;
    }
}
