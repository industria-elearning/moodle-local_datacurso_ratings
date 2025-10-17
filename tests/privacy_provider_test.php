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

namespace local_datacurso_ratings;

use core_privacy\local\metadata\collection;

/**
 * Privacy provider tests for local_datacurso_ratings plugin.
 *
 * @package    local_datacurso_ratings
 * @category   test
 * @covers     \local_datacurso_ratings\privacy\provider
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class privacy_provider_test extends \advanced_testcase {
    /**
     * Test that metadata is correctly defined by the provider.
     *
     * @covers ::get_metadata
     * @return void
     */
    public function test_get_metadata(): void {
        $this->resetAfterTest(true);

        $collection = new collection('local_datacurso_ratings');
        $collection = \local_datacurso_ratings\privacy\provider::get_metadata($collection);

        $items = $collection->get_collection();

        // Ensure the metadata includes both expected tables.
        $this->assertArrayHasKey('local_datacurso_ratings', $items);
        $this->assertArrayHasKey('local_datacurso_ratings_feedback', $items);

        // Ensure each metadata description is a non-empty string.
        $this->assertNotEmpty($items['local_datacurso_ratings']->get_summary());
        $this->assertNotEmpty($items['local_datacurso_ratings_feedback']->get_summary());
    }

    /**
     * Test that the privacy provider implements the required interfaces.
     *
     * @covers ::provider
     * @return void
     */
    public function test_provider_implements_interfaces(): void {
        $provider = new \local_datacurso_ratings\privacy\provider();

        $this->assertInstanceOf(\core_privacy\local\metadata\provider::class, $provider);
        $this->assertInstanceOf(\core_privacy\local\request\plugin\provider::class, $provider);
    }
}
