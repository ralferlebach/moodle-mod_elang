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
 * Per-cue control for generating gaps from a rule (2.1 rule-based gaps).
 *
 * The author picks a rule — a vocabulary word list, or every nth word — and
 * generates the matching gaps server-side. Generation is a two-step action: the
 * control first fetches and reports how many gaps the rule would create, and
 * only replaces the cue's gaps once the author confirms, so a rule never
 * silently discards hand-placed gaps.
 *
 * @module     mod_elang/components/RuleGapControl
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useState} from 'react';
import {Cue, Gap, GapRule, RuleGapSpan, Translator} from '../types';
import {newKey} from '../keys';

interface Props {
    cue: Cue;
    t: Translator;
    onGenerate: (transcript: string, rule: GapRule) => Promise<RuleGapSpan[]>;
    onApply: (gaps: Gap[]) => void;
    onStatus: (message: string) => void;
}

/**
 * Turn generated spans into full gap records.
 *
 * @param spans The generated spans.
 * @returns Gap records ready to store.
 */
export function spansToGaps(spans: RuleGapSpan[]): Gap[] {
    return spans.map((span, index) => ({
        gapkey: newKey('g'),
        sortorder: index + 1,
        charstart: span.charstart,
        charlength: span.charlength,
        solution: span.solution,
        gradingalgorithm: 'exact',
        maxlength: 0,
        linkurl: '',
        answers: [],
        hints: [],
    }));
}

/**
 * Render the rule-gap control.
 *
 * @param props The component props.
 * @returns The control element.
 */
export function RuleGapControl({cue, t, onGenerate, onApply, onStatus}: Props): JSX.Element {
    const [ruletype, setRuletype] = useState<GapRule['type']>('words');
    const [words, setWords] = useState('');
    const [interval, setInterval] = useState(2);
    const [busy, setBusy] = useState(false);
    const [pending, setPending] = useState<Gap[] | null>(null);

    const buildRule = (): GapRule => {
        if (ruletype === 'everynth') {
            return {type: 'everynth', n: Math.max(1, interval)};
        }
        return {
            type: 'words',
            words: words.split(/[\s,]+/).filter((word) => word !== ''),
        };
    };

    const handleGenerate = async(): Promise<void> => {
        setBusy(true);
        setPending(null);
        try {
            const spans = await onGenerate(cue.transcript, buildRule());
            const gaps = spansToGaps(spans);
            setPending(gaps);
            onStatus(t('editor:rulefound').replace('%count%', String(gaps.length)));
        } catch (error) {
            onStatus(t('editor:ruleerror'));
        } finally {
            setBusy(false);
        }
    };

    const handleApply = (): void => {
        if (pending === null) {
            return;
        }
        onApply(pending);
        onStatus(t('editor:ruleapplied').replace('%count%', String(pending.length)));
        setPending(null);
    };

    return (
        <div className="mod_elang-editor-rulegaps" data-region="rulegaps">
            <label className="mr-2">
                {t('editor:ruletype')}
                <select
                    className="form-control form-control-sm d-inline-block w-auto ml-1"
                    value={ruletype}
                    onChange={(event) => {
                        setRuletype(event.target.value as GapRule['type']);
                        setPending(null);
                    }}
                >
                    <option value="words">{t('editor:rulewords')}</option>
                    <option value="everynth">{t('editor:ruleeverynth')}</option>
                </select>
            </label>

            {ruletype === 'words' ? (
                <input
                    type="text"
                    className="form-control form-control-sm d-inline-block w-auto"
                    aria-label={t('editor:rulewordlist')}
                    placeholder={t('editor:rulewordlist')}
                    value={words}
                    onChange={(event) => {
                        setWords(event.target.value);
                        setPending(null);
                    }}
                />
            ) : (
                <input
                    type="number"
                    min={1}
                    className="form-control form-control-sm d-inline-block w-auto"
                    aria-label={t('editor:ruleinterval')}
                    value={interval}
                    onChange={(event) => {
                        setInterval(Math.max(1, parseInt(event.target.value, 10) || 1));
                        setPending(null);
                    }}
                />
            )}

            <button
                type="button"
                className="btn btn-outline-secondary btn-sm ml-2"
                disabled={busy}
                onClick={() => void handleGenerate()}
            >
                {t('editor:rulegenerate')}
            </button>

            {pending !== null && (
                <button
                    type="button"
                    className="btn btn-secondary btn-sm ml-2"
                    data-action="applyrule"
                    onClick={handleApply}
                >
                    {t('editor:ruleapply').replace('%count%', String(pending.length))}
                </button>
            )}
        </div>
    );
}
