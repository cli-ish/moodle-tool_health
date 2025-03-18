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
 * Unit tests for tool_health.
 *
 * @package    tool_health
 * @copyright  2013 Marko Vidberg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_health;

/**
 * Health lib test.
 *
 * @package    tool_health
 * @copyright  2013 Marko Vidberg
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class healthlib_test extends \advanced_testcase {

    /**
     * Set up before class.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();
        global $CFG;
        require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/health/locallib.php');
    }

    /**
     * Data provider for test_tool_health_category_find_loops.
     *
     * @return array[]
     */
    public static function provider_loop_categories(): array {
        return [
            0 => [ // One item loop including root.
                [
                    '1' => (object) ['id' => 1, 'parent' => 1],
                ], [
                    '1' => (object) ['id' => 1, 'parent' => 1],
                ],
            ],
            1 => [ // One item loop not including root.
                [
                    '1' => (object) ['id' => 1, 'parent' => 0],
                    '2' => (object) ['id' => 2, 'parent' => 2],
                ], [
                    '2' => (object) ['id' => 2, 'parent' => 2],
                ],
            ],
            2 => [ // Two item loop including root.
                [
                    '1' => (object) ['id' => 1, 'parent' => 2],
                    '2' => (object) ['id' => 2, 'parent' => 1],
                ], [
                    '2' => (object) ['id' => 2, 'parent' => 1],
                    '1' => (object) ['id' => 1, 'parent' => 2],
                ],
            ],
            3 => [ // Two item loop not including root.
                [
                    '1' => (object) ['id' => 1, 'parent' => 0],
                    '2' => (object) ['id' => 2, 'parent' => 3],
                    '3' => (object) ['id' => 3, 'parent' => 2],
                ], [
                    '3' => (object) ['id' => 3, 'parent' => 2],
                    '2' => (object) ['id' => 2, 'parent' => 3],
                ],
            ],
            4 => [ // Three item loop including root.
                [
                    '1' => (object) ['id' => 1, 'parent' => 2],
                    '2' => (object) ['id' => 2, 'parent' => 3],
                    '3' => (object) ['id' => 3, 'parent' => 1],
                ], [
                    '3' => (object) ['id' => 3, 'parent' => 1],
                    '1' => (object) ['id' => 1, 'parent' => 2],
                    '2' => (object) ['id' => 2, 'parent' => 3],
                ],
            ],
            5 => [ // Three item loop not including root.
                [
                    '1' => (object) ['id' => 1, 'parent' => 0],
                    '2' => (object) ['id' => 2, 'parent' => 3],
                    '3' => (object) ['id' => 3, 'parent' => 4],
                    '4' => (object) ['id' => 4, 'parent' => 2],
                ], [
                    '4' => (object) ['id' => 4, 'parent' => 2],
                    '2' => (object) ['id' => 2, 'parent' => 3],
                    '3' => (object) ['id' => 3, 'parent' => 4],
                ],
            ],
            6 => [ // Multi-loop.
                [
                    '1' => (object) ['id' => 1, 'parent' => 2],
                    '2' => (object) ['id' => 2, 'parent' => 1],
                    '3' => (object) ['id' => 3, 'parent' => 4],
                    '4' => (object) ['id' => 4, 'parent' => 5],
                    '5' => (object) ['id' => 5, 'parent' => 3],
                    '6' => (object) ['id' => 6, 'parent' => 6],
                    '7' => (object) ['id' => 7, 'parent' => 1],
                    '8' => (object) ['id' => 8, 'parent' => 7],
                ], [
                    '1' => (object) ['id' => 1, 'parent' => 2],
                    '2' => (object) ['id' => 2, 'parent' => 1],
                    '8' => (object) ['id' => 8, 'parent' => 7],
                    '7' => (object) ['id' => 7, 'parent' => 1],
                    '6' => (object) ['id' => 6, 'parent' => 6],
                    '5' => (object) ['id' => 5, 'parent' => 3],
                    '3' => (object) ['id' => 3, 'parent' => 4],
                    '4' => (object) ['id' => 4, 'parent' => 5],
                ],
            ],
            7 => [ // Double-loop.
                [
                    '1' => (object) ['id' => 1, 'parent' => 2],
                    '2' => (object) ['id' => 2, 'parent' => 1],
                    '3' => (object) ['id' => 3, 'parent' => 2],
                    '4' => (object) ['id' => 4, 'parent' => 2],
                ], [
                    '4' => (object) ['id' => 4, 'parent' => 2],
                    '3' => (object) ['id' => 3, 'parent' => 2],
                    '2' => (object) ['id' => 2, 'parent' => 1],
                    '1' => (object) ['id' => 1, 'parent' => 2],
                ],
            ],
        ];
    }

    /**
     * Data provider for test_tool_health_category_find_missing_parents.
     *
     * @return array[]
     */
    public static function provider_missing_parent_categories(): array {
        return [
            // Test for two items, both with direct ancestor (parent) missing.
            0 => [
                [
                    '1' => (object) ['id' => 1, 'parent' => 0],
                    '2' => (object) ['id' => 2, 'parent' => 3],
                    '4' => (object) ['id' => 4, 'parent' => 5],
                    '6' => (object) ['id' => 6, 'parent' => 2],
                ],
                [
                    '4' => (object) ['id' => 4, 'parent' => 5],
                    '2' => (object) ['id' => 2, 'parent' => 3],
                ],
            ],
        ];
    }

    /**
     * Test finding loops between two items referring to each other.
     *
     * @param array $categories
     * @param array $expected
     * @dataProvider provider_loop_categories
     * @covers ::tool_health_category_find_loops
     * @return void
     */
    public function test_tool_health_category_find_loops(array $categories, array $expected): void {
        $loops = tool_health_category_find_loops($categories);
        $this->assertEquals($expected, $loops);
    }

    /**
     * Test finding missing parent categories.
     *
     * @param array $categories
     * @param array $expected
     * @dataProvider provider_missing_parent_categories
     * @covers ::tool_health_category_find_missing_parents
     * @return void
     */
    public function test_tool_health_category_find_missing_parents(array $categories, array $expected): void {
        $missingparent = tool_health_category_find_missing_parents($categories);
        $this->assertEquals($expected, $missingparent);
    }

    /**
     * Test listing missing parent categories.
     *
     * @covers ::tool_health_category_list_missing_parents
     * @return void
     * @throws \coding_exception
     */
    public function test_tool_health_category_list_missing_parents(): void {
        $missingparent = [(object) ['id' => 2, 'parent' => 3, 'name' => 'test'],
            (object) ['id' => 4, 'parent' => 5, 'name' => 'test2']];
        $result = tool_health_category_list_missing_parents($missingparent);
        // Todo: check if this will work (language based).
        $this->assertMatchesRegularExpression('/Category 2: test/', $result);
        $this->assertMatchesRegularExpression('/Category 4: test2/', $result);
    }

    /**
     * Test listing loop categories.
     *
     * @covers ::tool_health_category_list_loops
     * @return void
     * @throws \coding_exception
     */
    public function test_tool_health_category_list_loops(): void {
        $loops = [(object) ['id' => 2, 'parent' => 3, 'name' => 'test']];
        $result = tool_health_category_list_loops($loops);
        // Todo: check if this will work (language based).
        $this->assertMatchesRegularExpression('/Category 2: test/', $result);
    }
}
