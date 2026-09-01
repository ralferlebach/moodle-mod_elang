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

namespace mod_elang\local\media;

/**
 * Curated registry of the external media providers the player can embed.
 *
 * The single source of truth for which providers exist and for turning a
 * teacher-supplied reference — a bare video id or any of the common URL
 * shapes (youtu.be links, share links with tracking parameters, shorts,
 * player URLs, ...) — into the canonical video id the player's embed
 * builders expect. Only OAuth-free, publicly embeddable providers belong
 * here; providers that require OAuth or a login are out of scope for this
 * registry and belong in a separate subplugin.
 *
 * Extending the list means: add the key here, an embed builder in
 * amd/src/player.js, and a provider:<key> language string.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_registry {
    /** @var string[] The provider keys the player knows how to embed. */
    private const PROVIDERS = ['youtube', 'vimeo'];

    /**
     * Return the curated provider keys.
     *
     * @return string[] The provider keys, in display order
     */
    public static function providers(): array {
        return self::PROVIDERS;
    }

    /**
     * Whether the given provider key is known.
     *
     * @param string $provider The provider key
     * @return bool True when the registry knows the provider
     */
    public static function is_known(string $provider): bool {
        return in_array($provider, self::PROVIDERS, true);
    }

    /**
     * Work out which provider a pasted URL belongs to, if any.
     *
     * The media page asks for one "other source" field rather than a provider
     * selector plus a reference field: a teacher pastes the URL they have,
     * from the address bar of whatever site they were on. Deciding which
     * provider that is belongs here, next to the patterns that already know
     * every shape those URLs take.
     *
     * @param string $reference A URL, or a bare video id
     * @return array|null ['provider' => key, 'reference' => canonical id], or null when no provider matches
     */
    public static function detect(string $reference): ?array {
        $reference = trim($reference);
        if ($reference === '') {
            return null;
        }

        foreach (self::PROVIDERS as $provider) {
            // A bare id normalises under every provider, so it cannot identify
            // one. Only a reference that actually names the provider counts.
            if (stripos($reference, $provider) === false && !self::mentions_short_domain($provider, $reference)) {
                continue;
            }

            $normalised = self::normalise_reference($provider, $reference);
            if ($normalised !== null) {
                return ['provider' => $provider, 'reference' => $normalised];
            }
        }

        return null;
    }

    /**
     * Whether a reference uses one of a provider's short domains.
     *
     * youtu.be does not contain the string "youtube", so matching on the
     * provider key alone would miss every short link.
     *
     * @param string $provider A known provider key
     * @param string $reference The teacher-supplied reference
     * @return bool True when a short domain of this provider appears
     */
    private static function mentions_short_domain(string $provider, string $reference): bool {
        $shortdomains = [
            'youtube' => ['youtu.be'],
            'vimeo' => [],
        ];

        foreach ($shortdomains[$provider] ?? [] as $domain) {
            if (stripos($reference, $domain) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalise a teacher-supplied reference to the canonical video id, or
     * null when neither a bare id nor a recognised URL shape matches.
     *
     * @param string $provider A known provider key
     * @param string $reference A bare video id or a URL in any common shape
     * @return string|null The canonical video id, or null if unparseable
     */
    public static function normalise_reference(string $provider, string $reference): ?string {
        $reference = trim($reference);
        if ($reference === '') {
            return null;
        }

        switch ($provider) {
            case 'youtube':
                return self::normalise_youtube($reference);
            case 'vimeo':
                return self::normalise_vimeo($reference);
            default:
                return null;
        }
    }

    /**
     * Normalise a YouTube reference. Accepted shapes: the bare 11-character
     * id; watch URLs with a v parameter (youtube.com, m.youtube.com,
     * music.youtube.com, with any extra parameters); youtu.be short links;
     * and /shorts/, /embed/, /live/ and /v/ paths — all with or without a
     * scheme or www prefix.
     *
     * @param string $reference The reference to normalise
     * @return string|null The 11-character video id, or null
     */
    private static function normalise_youtube(string $reference): ?string {
        $id = '[A-Za-z0-9_-]{11}';

        if (preg_match('/^' . $id . '$/', $reference)) {
            return $reference;
        }

        // Watch URLs: the id is the v query parameter, wherever it appears.
        if (preg_match('~^(?:https?://)?(?:www\.|m\.|music\.)?youtube\.com/watch\?[^#]*\bv=(' . $id . ')~', $reference, $m)) {
            return $m[1];
        }

        // Short links and path-based shapes; trailing query/fragment ignored.
        if (preg_match('~^(?:https?://)?(?:www\.)?youtu\.be/(' . $id . ')(?:[?#&]|$)~', $reference, $m)) {
            return $m[1];
        }
        $pathpattern = '~^(?:https?://)?(?:www\.|m\.)?youtube(?:-nocookie)?\.com/'
            . '(?:shorts|embed|live|v)/(' . $id . ')(?:[?#&/]|$)~';
        if (preg_match($pathpattern, $reference, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Normalise a Vimeo reference. Accepted shapes: the bare numeric id;
     * vimeo.com/<id> (also with a channel or group path before the id);
     * and player.vimeo.com/video/<id> — with or without a scheme or www
     * prefix, trailing query/fragment ignored.
     *
     * @param string $reference The reference to normalise
     * @return string|null The numeric video id, or null
     */
    private static function normalise_vimeo(string $reference): ?string {
        if (preg_match('/^\d+$/', $reference)) {
            return $reference;
        }

        if (preg_match('~^(?:https?://)?(?:www\.)?vimeo\.com/(?:[a-z]+/[^/]+/)?(\d+)(?:[?#/]|$)~i', $reference, $m)) {
            return $m[1];
        }
        if (preg_match('~^(?:https?://)?player\.vimeo\.com/video/(\d+)(?:[?#/]|$)~', $reference, $m)) {
            return $m[1];
        }

        return null;
    }
}
