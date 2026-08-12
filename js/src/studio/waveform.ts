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
 * Turns decoded audio samples into a small set of peak buckets for the timeline
 * waveform.
 *
 * Decoding the medium (an AudioBuffer via the Web Audio API) belongs to the
 * component; the arithmetic of reducing tens of thousands of samples to one peak
 * per pixel column, and of turning those peaks into an SVG polyline, is pure and
 * lives here so it can be unit tested without a browser audio stack.
 *
 * @module     mod_elang/studio/waveform
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Reduce a sample array to a fixed number of peak buckets, each the maximum
 * absolute amplitude over its slice, normalised to [0, 1].
 *
 * @param samples The decoded mono samples, each roughly in [-1, 1].
 * @param buckets The number of peak buckets to produce (one per pixel column).
 * @returns The peak per bucket, in order.
 */
export function extractPeaks(samples: ArrayLike<number>, buckets: number): number[] {
    if (buckets <= 0 || samples.length === 0) {
        return [];
    }

    const peaks: number[] = [];
    const size = samples.length / buckets;
    for (let b = 0; b < buckets; b++) {
        const from = Math.floor(b * size);
        const to = Math.min(samples.length, Math.floor((b + 1) * size));
        let peak = 0;
        for (let i = from; i < to; i++) {
            const value = Math.abs(samples[i]);
            if (value > peak) {
                peak = value;
            }
        }
        peaks.push(peak > 1 ? 1 : peak);
    }
    return peaks;
}

/**
 * Build the SVG polyline points for a mirrored waveform: peaks reflected around
 * a horizontal centre line, so the shape reads like a classic audio waveform.
 *
 * @param peaks The peak per bucket in [0, 1].
 * @param width The target pixel width.
 * @param height The target pixel height.
 * @returns A "x,y x,y ..." points string for an SVG polyline.
 */
export function peaksToPolyline(peaks: number[], width: number, height: number): string {
    if (peaks.length === 0 || width <= 0 || height <= 0) {
        return '';
    }

    const mid = height / 2;
    const step = width / peaks.length;
    const top: string[] = [];
    const bottom: string[] = [];
    peaks.forEach((peak, index) => {
        const x = index * step;
        const amplitude = (peak * height) / 2;
        top.push(x.toFixed(2) + ',' + (mid - amplitude).toFixed(2));
        bottom.push(x.toFixed(2) + ',' + (mid + amplitude).toFixed(2));
    });
    // Trace the top edge left to right, then the bottom edge back, forming a
    // closed mirrored band.
    return top.concat(bottom.reverse()).join(' ');
}
