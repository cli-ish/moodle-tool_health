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
 * 000009 tests if the sql database is conected without a password.
 *
 * @package     tool_health
 * @copyright   2025 Vincent Schneider (cli-ish)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class problem_000009 extends base {
    /**
     * Generate title for this problem.
     *
     * @return string
     */
    public function title(): string {
        return 'SQL: using account without password';
    }

    /**
     * Check if the problem exists.
     *
     * @return bool
     */
    public function exists(): bool {
        global $CFG;
        return empty($CFG->dbpass);
    }

    /**
     * Get severity of the problem.
     *
     * @return string
     */
    public function severity(): string {
        return SEVERITY_CRITICAL;
    }


    /**
     * Get problem description.
     *
     * @return string
     */
    public function description(): string {
        global $CFG;
        return 'The user account your are connecting to the database server with is set up without a password. ' .
            'This is a very big security risk and is only somewhat lessened if your database is configured to not ' .
            'accept connections from any hosts other than the server Moodle is running on. Unless you use a strong ' .
            'password to connect to the database, you risk unauthorized access to and manipulation of your data.' .
            ($CFG->dbuser != 'root' ? '' : (' <strong>This is especially alarming because such access to the ' .
                'database would be as the superuser (root)!</strong>'));
    }

    /**
     * Generate solution text.
     *
     * @return string
     * @uses $CFG
     */
    public function solution(): string {
        global $CFG;
        return 'You should change the password of the user <strong>' . $CFG->dbuser . '</strong> both in your ' .
            'database and in your Moodle <strong>config.php</strong> immediately!' .
            ($CFG->dbuser != 'root' ? '' : (' It would also be a good idea to change the user account from root' .
                ' to something else, because this would lessen the impact in the event that your ' .
                'database is compromised anyway.'));
    }
}
