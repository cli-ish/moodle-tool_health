<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace tool_health\local\problem;

use coding_exception;
use dml_exception;

/**
 * Check course categories tree structure for problems.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @copyright   2013 Marko Vidberg
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000018 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     * @throws coding_exception
     */
    public function title(): string {
        return get_string('problem_000018_title', 'tool_health');
    }

    /**
     * Search for problems in the course categories.
     *
     * @return array List of categories that contain missing parents or loops.
     * @throws dml_exception
     * @uses $DB
     */
    private function find_problems(): array {
        global $DB;
        static $answer = null;

        if (is_null($answer)) {
            $categories = $DB->get_records('course_categories', [], 'id');

            // Look for missing parents.
            $missingparent = tool_health_category_find_missing_parents($categories);

            // Look for loops.
            $loops = tool_health_category_find_loops($categories);

            $answer = [$missingparent, $loops];
        }

        return $answer;
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     */
    public function exists(): bool {
        [$missingparent, $loops] = $this->find_problems();
        return !empty($missingparent) || !empty($loops);
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return base::SEVERITY_SIGNIFICANT;
    }


    /**
     * Get problem description.
     *
     * @return string
     * @throws dml_exception
     * @throws coding_exception
     */
    public function description(): string {
        [$missingparent, $loops] = $this->find_problems();

        $description = get_string('problem_000018_description', 'tool_health');

        $description .= tool_health_category_list_missing_parents($missingparent);
        $description .= tool_health_category_list_loops($loops);

        return $description;
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @throws dml_exception
     * @throws coding_exception
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        [$missingparent, $loops] = $this->find_problems();

        $solution = get_string('problem_000018_solution', 'tool_health');

        if (!empty($missingparent)) {
            $solution .= "<pre>UPDATE " . $CFG->prefix . "course_categories\n" .
                "        SET parent = 0, depth = 1, path = CONCAT('/', id)\n" .
                "        WHERE id IN (" . implode(',', array_keys($missingparent)) . ");</pre>\n";
        }

        if (!empty($loops)) {
            $solution .= "<pre>UPDATE " . $CFG->prefix . "course_categories\n" .
                "        SET parent = 0, depth = 1, path = CONCAT('/', id)\n" .
                "        WHERE id IN (" . implode(',', array_keys($loops)) . ");</pre>\n";
        }

        return $solution;
    }
}
