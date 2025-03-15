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

/**
 * 000003 tests if the dataroot exist and is writeable.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000003 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return '$CFG->dataroot does not exist or does not have write permissions';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        global $CFG;
        if (!is_dir($CFG->dataroot) || !is_writable($CFG->dataroot)) {
            return true;
        }
        return false;
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
     */
    public function description(): string {
        global $CFG;
        return 'Your <strong>config.php</strong> says that your "data root" directory is <strong>' . $CFG->dataroot .
            '</strong>. However, this directory either does not exist or cannot be written to by Moodle. This means ' .
            'that a variety of problems will be present, such as users not being able to log in and not being able ' .
            'to upload any files. It is imperative that you address this problem for Moodle to work correctly.';
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        return 'First of all, make sure that the directory <strong>' . $CFG->dataroot .
            '</strong> exists. If the directory does exist, then you must make sure that Moodle is able to write ' .
            'to it. Contact your web server administrator and request that he gives write permissions for that ' .
            'directory to the user that the web server process is running as.';
    }
}
