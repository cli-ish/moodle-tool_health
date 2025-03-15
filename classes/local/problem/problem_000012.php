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

use dml_exception;

/**
 * 000012 tests if random questions are consistent.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000012 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'Random questions data consistency';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     */
    public function exists(): bool {
        global $DB;
        return $DB->record_exists_select('question', "qtype = 'random' AND parent <> id", []);
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return base::SEVERITY_ANNOYANCE;
    }


    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        return '<p>For random questions, question.parent should equal question.id. ' .
            'There are some questions in your database for which this is not true. ' .
            'One way that this could have happened is for random questions restored from backup before ' .
            '<a href="https://tracker.moodle.org/browse/MDL-5482" target="_blank">MDL-5482</a> was fixed.</p>';
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        return '<p>Upgrade to Moodle 1.9.1 or later, or manually execute the SQL</p>' .
            '<pre>UPDATE ' . $CFG->prefix . 'question SET parent = id WHERE qtype = \'random\' and parent &lt;> id;</pre>';
    }
}
