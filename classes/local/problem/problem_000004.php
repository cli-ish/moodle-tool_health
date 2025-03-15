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
 * 000004 tests if the cron is run on a regular basis.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000004 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'cron.php is not set up to run automatically';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     * @throws dml_exception
     */
    public function exists(): bool {
        global $CFG;
        $lastcron = get_config('tool_task', 'lastcronstart');
        $expectedfrequency = $CFG->expectedcronfrequency ?? MINSECS;
        $delta = time() - $lastcron;
        return $expectedfrequency && ($delta > $expectedfrequency + MINSECS);
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return SEVERITY_SIGNIFICANT;
    }


    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        return 'The cron.php mainenance script has not been run in the expected interval, the interval can be ' .
            'defined over $CFG->expectedcronfrequency. This probably means that your ' .
            'server is not configured to automatically run this script in regular time intervals. If this is the ' .
            'case, then Moodle will mostly work as it should but some operations (notably sending email to users) ' .
            'will not be carried out at all.';
    }

    /**
     * Generate solution text.
     *
     * @return string
     */
    public function solution(): string {
        return 'For detailed instructions on how to enable cron, see ' .
            '<a href="https://docs.moodle.org/en/Cron" target="_blank">this section</a> of the installation manual.';
    }
}
