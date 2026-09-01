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
 * A cue boundary, entered as a timestamp rather than as milliseconds.
 *
 * The field keeps its own draft of what is being typed. Reformatting on every
 * keystroke would fight the author — typing "1:0" would jump to "01:00.000"
 * before the second digit of the minute arrived — so the value is only parsed
 * and handed upwards when the field is left or Enter is pressed. An
 * unparseable entry is rejected there and the last good value comes back,
 * because silently keeping a half-typed time is how a cue ends up somewhere
 * nobody chose.
 *
 * @module     mod_elang/components/TimeField
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {useEffect, useState} from 'react';
import {formatTime, parseTime} from '../studio/time';

interface Props {
    value: number;
    label: string;
    invalidmessage: string;
    onChange: (ms: number) => void;
}

/**
 * Render a timestamp field.
 *
 * @param props The component props.
 * @returns The field element.
 */
export function TimeField({value, label, invalidmessage, onChange}: Props): JSX.Element {
    const [draft, setDraft] = useState(formatTime(value));
    const [invalid, setInvalid] = useState(false);

    // The value also changes from elsewhere — dragging a cue handle in the
    // timeline, capturing from playback — and the field has to follow those
    // without the author retyping anything.
    useEffect(() => {
        setDraft(formatTime(value));
        setInvalid(false);
    }, [value]);

    const commit = (): void => {
        const ms = parseTime(draft);
        if (ms === null) {
            setInvalid(true);
            setDraft(formatTime(value));
            return;
        }
        setInvalid(false);
        if (ms !== value) {
            onChange(ms);
        }
    };

    return (
        <span className="mod_elang-timefield">
            <input
                type="text"
                inputMode="numeric"
                className={'form-control d-inline-block' + (invalid ? ' is-invalid' : '')}
                style={{width: '9rem'}}
                aria-label={label}
                aria-invalid={invalid ? 'true' : undefined}
                data-region="timefield"
                value={draft}
                onChange={(event) => setDraft(event.target.value)}
                onBlur={commit}
                onKeyDown={(event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        commit();
                    }
                }}
            />
            {invalid && (
                <span className="invalid-feedback d-block" role="alert">{invalidmessage}</span>
            )}
        </span>
    );
}
