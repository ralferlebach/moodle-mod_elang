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

/**
 * German language strings for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['completiondetail:completionfinishattempt'] = 'Einen Versuch abschließen';
$string['completionfinishattempt'] = 'Die Person muss einen Versuch abschließen';
$string['elang:addinstance'] = 'Neue Sprachübung anlegen';
$string['elang:attempt'] = 'Sprachübung bearbeiten';
$string['elang:deleteattempts'] = 'Versuche von Lernenden löschen';
$string['elang:exportreports'] = 'Berichte mit personenbezogenen Daten exportieren';
$string['elang:exporttranscript'] = 'Transkript als Dokument exportieren';
$string['elang:manage'] = 'Übungsinhalte erstellen und bearbeiten';
$string['elang:useregex'] = 'Reguläre Ausdrücke in akzeptierten Antworten verwenden';
$string['elang:view'] = 'Sprachübung ansehen';
$string['elang:viewreports'] = 'Berichte zu Lernenden ansehen';
$string['error:attemptnotinprogress'] = 'Dieser Versuch läuft nicht mehr.';
$string['error:gapnotinattemptversion'] = 'Diese Lücke gehört nicht zur Übungsversion dieses Versuchs.';
$string['error:noaccesstoattempt'] = 'Sie haben keinen Zugriff auf diesen Versuch.';
$string['error:nomorehints'] = 'Für diese Lücke sind keine weiteren Hilfen verfügbar.';
$string['error:nopublishedversion'] = 'Für diese Übung ist noch kein Inhalt veröffentlicht.';
$string['modulename'] = 'Sprachübung';
$string['modulename_help'] = 'Die Aktivität Sprachübung lässt Lernende Lücken in zeitcodierten Untertiteln ausfüllen, während sie ein Video ansehen oder anhören.

Lehrende importieren eine WebVTT- oder SubRip-Datei, markieren Wörter oder Wendungen als Lücken und legen fest, wie streng Antworten verglichen werden. Lernende arbeiten das Transkript Segment für Segment durch, können abgestufte Hilfen anfordern und erhalten unmittelbare Rückmeldung.';
$string['modulenameplural'] = 'Sprachübungen';
$string['noinstances'] = 'In diesem Kurs gibt es keine Sprachübungen.';
$string['pluginadministration'] = 'Sprachübung-Administration';
$string['pluginname'] = 'Sprachübung';
$string['privacy:metadata:elang_attempt'] = 'Für jeden Versuch an einer Übung speichert die Aktivität, wer ihn unternommen hat, wann und wie er verlief.';
$string['privacy:metadata:elang_attempt:attemptnumber'] = 'Die laufende Nummer dieses Versuchs für die Person und die Aktivität.';
$string['privacy:metadata:elang_attempt:score'] = 'Die in diesem Versuch erreichte Punktzahl.';
$string['privacy:metadata:elang_attempt:state'] = 'Ob der Versuch läuft, abgeschlossen oder abgebrochen ist.';
$string['privacy:metadata:elang_attempt:timefinish'] = 'Der Zeitpunkt, zu dem der Versuch abgeschlossen wurde.';
$string['privacy:metadata:elang_attempt:timestart'] = 'Der Zeitpunkt, zu dem der Versuch begonnen wurde.';
$string['privacy:metadata:elang_attempt:userid'] = 'Die ID der Person, die den Versuch unternommen hat.';
$string['privacy:metadata:elang_response'] = 'Für jede Lücke, die eine lernende Person innerhalb eines Versuchs beantwortet, speichert die Aktivität den Antworttext und dessen Bewertung.';
$string['privacy:metadata:elang_response:accepted'] = 'Ob die Antwort für diese Lücke als richtig akzeptiert wurde.';
$string['privacy:metadata:elang_response:responsetext'] = 'Der von der lernenden Person für diese Lücke eingegebene Text.';
$string['privacy:metadata:elang_response:resultstate'] = 'Die vom Evaluator ermittelte Einstufung dieser Antwort (exakt, Wort erkannt, falsch oder leer).';
$string['privacy:metadata:elang_response:timecreated'] = 'Der Zeitpunkt, zu dem diese Antwort erstmals abgegeben wurde.';
$string['privacy:metadata:elang_response:tries'] = 'Wie oft die lernende Person eine Antwort auf diese Lücke abgegeben hat.';
$string['skeletonnotice'] = 'Diese Aktivität ist ein Infrastruktur-Skelett für Version 2.0. Player, Transkript und Bearbeitungsbereich sind noch nicht umgesetzt.';
$string['subplugintype_elangscript'] = 'Schrift-Handler';
$string['subplugintype_elangscript_plural'] = 'Schrift-Handler';
