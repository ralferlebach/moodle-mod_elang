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
 * Tests for the media provider registry and its reference normalisation.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\media\provider_registry
 */
final class provider_registry_test extends \basic_testcase {
    /**
     * The registry offers exactly the curated providers.
     *
     * @return void
     */
    public function test_curated_provider_list(): void {
        $this->assertSame(['youtube', 'vimeo'], provider_registry::providers());
        $this->assertTrue(provider_registry::is_known('youtube'));
        $this->assertFalse(provider_registry::is_known('myvideosite'));
    }

    /**
     * Data provider: references that normalise to a canonical id.
     *
     * @return array[] provider, input reference, expected id
     */
    public static function valid_reference_provider(): array {
        return [
            'youtube bare id' => ['youtube', 'dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'youtube watch url' => ['youtube', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'youtube watch with params' => [
                'youtube',
                'https://www.youtube.com/watch?app=desktop&v=dQw4w9WgXcQ&t=42s',
                'dQw4w9WgXcQ',
            ],
            'youtube short link' => ['youtube', 'https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'youtube short link with tracking' => ['youtube', 'https://youtu.be/dQw4w9WgXcQ?si=AbCdEf', 'dQw4w9WgXcQ'],
            'youtube short link without scheme' => ['youtube', 'youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'youtube shorts' => ['youtube', 'https://www.youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'youtube embed' => ['youtube', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'youtube nocookie embed' => ['youtube', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'youtube live' => ['youtube', 'https://www.youtube.com/live/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'youtube mobile' => ['youtube', 'https://m.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'youtube music' => ['youtube', 'https://music.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'vimeo bare id' => ['vimeo', '76979871', '76979871'],
            'vimeo url' => ['vimeo', 'https://vimeo.com/76979871', '76979871'],
            'vimeo url without scheme' => ['vimeo', 'vimeo.com/76979871', '76979871'],
            'vimeo url with fragment' => ['vimeo', 'https://vimeo.com/76979871#t=10s', '76979871'],
            'vimeo channel url' => ['vimeo', 'https://vimeo.com/channels/staffpicks/76979871', '76979871'],
            'vimeo player url' => ['vimeo', 'https://player.vimeo.com/video/76979871', '76979871'],
        ];
    }

    /**
     * Recognised shapes normalise to the canonical video id.
     *
     * @dataProvider valid_reference_provider
     * @param string $provider The provider key
     * @param string $reference The teacher-supplied reference
     * @param string $expected The canonical id
     * @return void
     */
    public function test_recognised_references_normalise(string $provider, string $reference, string $expected): void {
        $this->assertSame($expected, provider_registry::normalise_reference($provider, $reference));
    }

    /**
     * Data provider: references that must be rejected.
     *
     * @return array[] provider, input reference
     */
    public static function invalid_reference_provider(): array {
        return [
            'empty' => ['youtube', ''],
            'whitespace' => ['youtube', '   '],
            'youtube wrong id length' => ['youtube', 'abc'],
            'youtube foreign host' => ['youtube', 'https://example.com/watch?v=dQw4w9WgXcQ'],
            'youtube channel url' => ['youtube', 'https://www.youtube.com/@somechannel'],
            'vimeo non-numeric' => ['vimeo', 'https://vimeo.com/somepage'],
            'vimeo foreign host' => ['vimeo', 'https://example.com/76979871'],
            'unknown provider' => ['myvideosite', 'dQw4w9WgXcQ'],
        ];
    }

    /**
     * Unrecognised shapes are rejected rather than guessed about.
     *
     * @dataProvider invalid_reference_provider
     * @param string $provider The provider key
     * @param string $reference The teacher-supplied reference
     * @return void
     */
    public function test_unrecognised_references_are_rejected(string $provider, string $reference): void {
        $this->assertNull(provider_registry::normalise_reference($provider, $reference));
    }

    /**
     * A pasted address is resolved to the provider it belongs to.
     *
     * @dataProvider detected_urls_provider
     * @param string $reference The address a teacher would paste
     * @param string $provider The provider it belongs to
     * @param string $expected The canonical video id
     * @return void No return value.
     */
    public function test_addresses_are_resolved_to_their_provider(
        string $reference,
        string $provider,
        string $expected
    ): void {
        $detected = provider_registry::detect($reference);

        $this->assertNotNull($detected);
        $this->assertSame($provider, $detected['provider']);
        $this->assertSame($expected, $detected['reference']);
    }

    /**
     * Addresses that belong to a known provider.
     *
     * @return array The reference, its provider and the canonical id
     */
    public static function detected_urls_provider(): array {
        return [
            'youtube watch url' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ'],
            'youtube with extra parameters' => [
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=42s',
                'youtube',
                'dQw4w9WgXcQ',
            ],
            'youtube short link' => ['https://youtu.be/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ'],
            'youtube nocookie embed' => [
                'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
                'youtube',
                'dQw4w9WgXcQ',
            ],
            'vimeo url' => ['https://vimeo.com/123456789', 'vimeo', '123456789'],
        ];
    }

    /**
     * Anything that is not a provider address is left alone, so the media page
     * stores it as a direct media URL rather than inventing an embed.
     *
     * @dataProvider undetected_urls_provider
     * @param string $reference The address a teacher would paste
     * @return void No return value.
     */
    public function test_other_addresses_belong_to_no_provider(string $reference): void {
        $this->assertNull(provider_registry::detect($reference));
    }

    /**
     * Addresses that belong to no known provider.
     *
     * @return array The reference
     */
    public static function undetected_urls_provider(): array {
        return [
            'direct media url' => ['https://example.org/lesson/clip.mp4'],
            'a site that is not a provider' => ['https://example.org/watch?v=dQw4w9WgXcQ'],
            'empty' => [''],
            'whitespace' => ['   '],
            // A bare id normalises under every provider, so it names none of
            // them and must not be silently claimed by the first in the list.
            'bare id' => ['dQw4w9WgXcQ'],
        ];
    }
}
