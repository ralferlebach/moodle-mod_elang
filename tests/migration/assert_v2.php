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
 * Check that the version 1 activity survived the upgrade to 2.0 intact.
 *
 * Run **after** the plugin has been replaced by 2.0 and the migration has run.
 * Reads the ids the seed script printed, so it checks the activity that was
 * actually seeded rather than whatever happens to be in the database.
 *
 * Every check states what it expects and why it matters. A migration test that
 * only counts rows would pass while moving them into the wrong places.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

// How many checks have failed so far.
$failures = 0;

/**
 * Assert, and keep going so one run reports everything rather than the first thing.
 *
 * @param bool $condition What must hold
 * @param string $what The claim being checked
 * @param string $detail What was found instead
 * @return void
 */
function check(bool $condition, string $what, string $detail = ''): void {
    global $failures;

    if ($condition) {
        echo "  ok    $what\n";
        return;
    }

    echo "  FAIL  $what\n";
    if ($detail !== '') {
        echo "        $detail\n";
    }
    $failures++;
}

$elangid = (int) getenv('MIGV1_ELANGID');
$cmid = (int) getenv('MIGV1_CMID');
$contextid = (int) getenv('MIGV1_CONTEXTID');
if ($elangid === 0 || $cmid === 0) {
    cli_error('MIGV1_ELANGID and MIGV1_CMID must be set — source the seed script output.');
}

echo "Pruefe die Migration der Aktivitaet $elangid (cmid $cmid)\n\n";

// The activity itself.
$elang = $DB->get_record('elang', ['id' => $elangid]);
check($elang !== false, 'Die Aktivität existiert noch');
if ($elang === false) {
    exit(1);
}

check($elang->name === 'Dictée de test', 'Der Name ist unverändert', 'name=' . $elang->name);
check((string) $elang->language === 'fr', 'Die Inhaltssprache ist erhalten', 'language=' . $elang->language);
check(
    $DB->record_exists('course_modules', ['id' => $cmid, 'instance' => $elangid]),
    'Das Kursmodul zeigt weiterhin auf die Aktivität'
);

// A published version was created.
$version = $DB->get_record('elang_version', ['id' => (int) $elang->currentversionid]);
check($version !== false, 'Eine Version ist angelegt und verknüpft');
if ($version === false) {
    exit(1);
}
check($version->status === 'published', 'Die Version ist veröffentlicht', 'status=' . $version->status);

// The media file has to have moved from V1's file area to the version's, or the
// exercise would open with no medium at all — the one failure a learner notices
// immediately.
check(
    (string) $version->mediakind === 'file',
    'Das Medium ist als Datei übernommen',
    'mediakind=' . var_export($version->mediakind, true)
);

$fs = get_file_storage();
$mediafiles = $fs->get_area_files($contextid, 'mod_elang', 'media', (int) $version->id, 'filename', false);
check(count($mediafiles) === 1, 'Genau eine Mediendatei liegt am neuen Ort', 'gefunden: ' . count($mediafiles));
$mediafile = reset($mediafiles);
check(
    $mediafile !== false && $mediafile->get_filename() === 'lesson.mp4',
    'Der Dateiname ist erhalten',
    $mediafile ? $mediafile->get_filename() : '(keine)'
);

// Cues and gaps.
$cues = $DB->get_records('elang_cue', ['versionid' => $version->id], 'sortorder ASC');
check(count($cues) === 3, 'Alle drei Cues sind migriert', 'gefunden: ' . count($cues));

$firstcue = reset($cues);
check(
    $firstcue !== false && $firstcue->transcript === 'Le chat dort sur le canapé.',
    'Der Transkripttext ist vollständig zusammengesetzt',
    $firstcue ? $firstcue->transcript : '(kein Cue)'
);
check(
    $firstcue !== false && (int) $firstcue->starttime === 0 && (int) $firstcue->endtime === 3000,
    'Die Cue-Zeiten sind übernommen',
    $firstcue ? "{$firstcue->starttime}–{$firstcue->endtime}" : ''
);

$gapcount = $DB->count_records_sql(
    'SELECT COUNT(1) FROM {elang_gap} g JOIN {elang_cue} c ON c.id = g.cueid WHERE c.versionid = ?',
    [$version->id]
);
check($gapcount === 5, 'Alle fünf Lücken sind migriert', 'gefunden: ' . $gapcount);

$firstgap = $DB->get_record_sql(
    'SELECT g.* FROM {elang_gap} g JOIN {elang_cue} c ON c.id = g.cueid
      WHERE c.versionid = ? ORDER BY c.sortorder ASC, g.sortorder ASC',
    [$version->id],
    IGNORE_MULTIPLE
);
check(
    $firstgap !== false && $firstgap->solution === 'chat',
    'Die Lösung der ersten Lücke ist erhalten',
    $firstgap ? $firstgap->solution : ''
);
// The gap word stays inside the transcript at its own offsets; the player masks
// it at display time. Getting this wrong shows the answer to the learner.
check(
    $firstgap !== false
        && \core_text::substr((string) $firstcue->transcript, (int) $firstgap->charstart, (int) $firstgap->charlength)
            === 'chat',
    'charstart/charlength zeigen auf das Lösungswort im Transkript',
    $firstgap ? "start={$firstgap->charstart} len={$firstgap->charlength}" : ''
);

// V1 marked one gap as help-allowed and gave one a reference link.
$hintcount = $DB->count_records_sql(
    'SELECT COUNT(1) FROM {elang_gaphint} h
       JOIN {elang_gap} g ON g.id = h.gapid
       JOIN {elang_cue} c ON c.id = g.cueid
      WHERE c.versionid = ?',
    [$version->id]
);
check($hintcount === 1, 'Der eine V1-Hinweis ist übernommen', 'gefunden: ' . $hintcount);

$linked = $DB->get_field_sql(
    'SELECT g.linkurl FROM {elang_gap} g JOIN {elang_cue} c ON c.id = g.cueid
      WHERE c.versionid = ? AND g.linkurl <> \'\'',
    [$version->id],
    IGNORE_MULTIPLE
);
check(
    $linked === 'https://example.org/chien',
    'Der Nachschlage-Link einer Lücke ist erhalten',
    var_export($linked, true)
);

// Settings mapped from V1 options.
// V1 applied usecasesensitive/usetransliteration/jaroDistance to the whole
// activity. The fixture set case-insensitive with transliteration, which must
// not become exact matching — that would mark correct answers wrong.
$algorithms = $DB->get_fieldset_sql(
    'SELECT DISTINCT g.gradingalgorithm FROM {elang_gap} g
       JOIN {elang_cue} c ON c.id = g.cueid WHERE c.versionid = ?',
    [$version->id]
);
check(count($algorithms) === 1, 'Alle Lücken haben denselben Algorithmus', implode(', ', $algorithms));
check(
    !in_array('exact', $algorithms, true),
    'Der tolerante Vergleich aus V1 ist nicht zu exaktem Vergleich geworden',
    implode(', ', $algorithms)
);

// Learner work.
$attempts = $DB->get_records('elang_attempt', ['elangid' => $elangid]);
check(count($attempts) === 4, 'Jede der vier lernenden Personen hat einen Versuch', 'gefunden: ' . count($attempts));

foreach (['EXACT', 'ACCENTS', 'PARTIAL', 'UNTOUCHED'] as $label) {
    $userid = (int) getenv('MIGV1_USER_' . $label);
    if ($userid === 0) {
        continue;
    }
    check(
        $DB->record_exists('elang_attempt', ['elangid' => $elangid, 'userid' => $userid]),
        "Der Versuch von '$label' existiert"
    );
}

$exactuser = (int) getenv('MIGV1_USER_EXACT');
$exactattempt = $DB->get_record('elang_attempt', ['elangid' => $elangid, 'userid' => $exactuser]);
if ($exactattempt !== false) {
    check(
        (int) $exactattempt->answeredgaps === 5,
        'Wer alles beantwortet hat, hat fünf beantwortete Lücken',
        'answeredgaps=' . $exactattempt->answeredgaps
    );
    check(
        (int) $exactattempt->correctgaps === 5,
        'Alle fünf Antworten sind als richtig übernommen',
        'correctgaps=' . $exactattempt->correctgaps
    );
}

$accentuser = (int) getenv('MIGV1_USER_ACCENTS');
$accentattempt = $DB->get_record('elang_attempt', ['elangid' => $elangid, 'userid' => $accentuser]);
if ($accentattempt !== false) {
    // Here "canape" stands for "canapé" and "CHIEN" for "chien". V1 accepted
    // migration graded them as wrong, learners would find their score silently
    // reduced by an upgrade they did not ask for.
    check(
        (int) $accentattempt->correctgaps === 5,
        'Antworten ohne Akzent und in Großbuchstaben bleiben akzeptiert',
        'correctgaps=' . $accentattempt->correctgaps
    );
}

$partialuser = (int) getenv('MIGV1_USER_PARTIAL');
$partialattempt = $DB->get_record('elang_attempt', ['elangid' => $elangid, 'userid' => $partialuser]);
if ($partialattempt !== false) {
    check(
        (int) $partialattempt->answeredgaps < 5,
        'Eine unvollständige Bearbeitung bleibt unvollständig',
        'answeredgaps=' . $partialattempt->answeredgaps
    );
    check(
        (int) $partialattempt->hintedgaps === 1,
        'Die eine mit Hinweis beantwortete Lücke ist als solche erhalten',
        'hintedgaps=' . $partialattempt->hintedgaps
    );
}

$untoucheduser = (int) getenv('MIGV1_USER_UNTOUCHED');
$untouchedattempt = $DB->get_record('elang_attempt', ['elangid' => $elangid, 'userid' => $untoucheduser]);
if ($untouchedattempt !== false) {
    check(
        (int) $untouchedattempt->answeredgaps === 0,
        'Wer nichts beantwortet hat, behält einen leeren Versuch',
        'answeredgaps=' . $untouchedattempt->answeredgaps
    );
}

// Individual responses, not just the totals: the counters could be right while
// the text a learner typed was lost.
$responsetext = $DB->get_field_sql(
    'SELECT r.responsetext FROM {elang_response} r
       JOIN {elang_attempt} a ON a.id = r.attemptid
       JOIN {elang_gap} g ON g.id = r.gapid
      WHERE a.userid = ? AND g.solution = ?',
    [$partialuser, 'canapé'],
    IGNORE_MULTIPLE
);
check(
    $responsetext === 'sofa',
    'Der genaue Wortlaut einer falschen Antwort ist erhalten',
    var_export($responsetext, true)
);

// The gap set matches the markup in the subtitle fixture.
//
// The fixture's cues are read out of lesson.vtt using V1's own rule, so this
// closes the loop: the solutions that arrived in V2 are the words that were
// bracketed in the file a teacher uploaded, and the one with help is the one
// written in square brackets rather than braces.
$solutions = $DB->get_fieldset_sql(
    'SELECT g.solution FROM {elang_gap} g JOIN {elang_cue} c ON c.id = g.cueid
      WHERE c.versionid = ? ORDER BY c.sortorder ASC, g.sortorder ASC',
    [$version->id]
);
check(
    $solutions === ['chat', 'canapé', 'chien', 'oiseaux', 'matin'],
    'Die Lösungen sind genau die im Untertitelfile markierten Wörter',
    implode(', ', $solutions)
);

$helpsolution = $DB->get_field_sql(
    'SELECT g.solution FROM {elang_gaphint} h
       JOIN {elang_gap} g ON g.id = h.gapid
       JOIN {elang_cue} c ON c.id = g.cueid
      WHERE c.versionid = ?',
    [$version->id],
    IGNORE_MULTIPLE
);
check(
    $helpsolution === 'chat',
    'Der Hinweis hängt an der Lücke, die in der Datei in eckigen Klammern stand',
    var_export($helpsolution, true)
);

// The migration is recorded as done.
check(
    $DB->record_exists_select('elang', 'id = ? AND migrationapproveduserid IS NULL', [$elangid]),
    'Die Migration ist noch nicht abgenommen (Abnahme ist ein bewusster Schritt)'
);

echo "\n";
if ($failures > 0) {
    cli_error("$failures Pruefung(en) fehlgeschlagen.", 1);
}

cli_writeln(
    'Alle Pruefungen bestanden: Aktivitaet, Medium, Cues, Luecken, Einstellungen'
        . ' und Nutzerdaten sind vollstaendig migriert.'
);
