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
 * 000016 tests if question categories have the same context as their parent.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000016 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'Question categories should belong to the same context as their parent';
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
            SELECT parent_qc.id AS parent, child_qc.id AS child, child_qc.contextid
            FROM {question_categories} child_qc
                JOIN {question_categories} parent_qc ON child_qc.parent = parent_qc.id
            WHERE child_qc.contextid <> parent_qc.contextid");
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return SEVERITY_ANNOYANCE;
    }


    /**
     * Get problem description.
     *
     * @return string
     * @throws dml_exception
     */
    public function description(): string {
        global $DB;
        $problemcategories = $DB->get_records_sql("
            SELECT
                parent_qc.id AS parentid, parent_qc.name AS parentname, parent_qc.contextid AS parentcon,
                child_qc.id AS childid, child_qc.name AS childname, child_qc.contextid AS childcon
            FROM {question_categories} child_qc
                JOIN {question_categories} parent_qc ON child_qc.parent = parent_qc.id
            WHERE child_qc.contextid <> parent_qc.contextid");
        $table = '<table><thead><tr><th colspan="3">Child category</th><th colspan="3">Parent category</th></tr><tr>' .
            '<th>Id</th><th>Name</th><th>Context id</th>' .
            '<th>Id</th><th>Name</th><th>Context id</th>' .
            "</tr></thead><tbody>\n";
        foreach ($problemcategories as $cat) {
            $table .= "<tr><td>$cat->childid</td><td>" . s($cat->childname) .
                "</td><td>$cat->childcon</td><td>$cat->parentid</td><td>" . s($cat->parentname) .
                "</td><td>$cat->parentcon</td></tr>\n";
        }
        $table .= '</tbody></table>';
        return '<p>When one question category is the parent of another, then they ' .
            'should both belong to the same context. This is not true for the following categories:</p>' .
            $table;
    }

    /**
     * Generate solution text.
     *
     * @return string
     */
    public function solution(): string {
        return '<p>An automated solution is difficult. It depends whether the ' .
            'parent or child category is in the wrong pace. People in the ' .
            '<a href="https://moodle.org/mod/forum/view.php?f=121" target="_blank">Quiz forum</a> may be able to help.</p>';
    }
}
