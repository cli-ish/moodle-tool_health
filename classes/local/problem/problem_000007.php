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

/**
 * 000007 tests the file_upload php variable.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000007 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     * @throws coding_exception
     */
    public function title(): string {
        return get_string('problem_000007_title', 'tool_health');
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        return !ini_get_bool('file_uploads');
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
     * @throws coding_exception
     */
    public function description(): string {
        return get_string('problem_000007_description', 'tool_health');
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @throws coding_exception
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        $result = get_string('problem_000007_solution', 'tool_health');
        $result .= '<ol><li>' . get_string('problem_000007_solution_step_one', 'tool_health') . '</li>';
        $result .= '<li>' . get_string('problem_000007_solution_step_two', 'tool_health', $CFG->dirroot) . '</li></ol>';
        return $result;
    }
}
