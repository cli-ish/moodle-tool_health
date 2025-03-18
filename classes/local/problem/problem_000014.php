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
 * 000014 tests if any none multi answer/random questions have a question as parent.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000014 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     * @throws coding_exception
     */
    public function title(): string {
        return get_string('problem_000014_title', 'tool_health');
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     */
    public function exists(): bool {
        global $DB;
        return $DB->record_exists_sql("
                SELECT * FROM {question} q
                    JOIN {question} parent_q ON parent_q.id = q.parent
                WHERE parent_q.qtype NOT IN ('random', 'multianswer')");
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
     * @throws coding_exception
     */
    public function description(): string {
        return get_string('problem_000014_description', 'tool_health');
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @throws coding_exception
     */
    public function solution(): string {
        return get_string('problem_000014_solution', 'tool_health');
    }

    /**
     * Returns a list of urls which could be helpful.
     * where the key is the title for the link.
     *
     * @return string[]
     * @throws coding_exception
     */
    public function links(): array {
        return [
            get_string('problem_000014_link_cron', 'tool_health') => 'https://moodle.org/mod/forum/view.php?f=121',
        ];
    }
}
